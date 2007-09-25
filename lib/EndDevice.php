<?php

/*
 * EndDevice Class
 *
 * This class represents a row in the systems table in the database.
 * In the current version, the host is identified by its mac address.
 * 
 * 
 *
 * This is the core of the logging / debugging and regression testing.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Seiler Thomas (contributer)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */


class EndDevice extends Common {
	private $mac;
	private $db_row = array();
	

	/* The constructor takes the mac address of the system and creates 
	 * and instance representing that particular system.
	 * Access is read-only.
	 */
	public function __construct($object) {
	  	
		parent::__construct();
		if (($object instanceof VMPSRequest) || ($object instanceof VMPSResult))
                {
		   /* Normalise mac address format by removing spaces, dashes, dots
		    * and collons, and by converting to lower case.
		    */
		   $mac=$object->mac;
  	  	   $mac = strtolower(preg_replace('/-|\.|\s|\:/', '', $mac));
		   /* sanity check - Is this a valid MAC address ??? */
  	  	   if (!preg_match("/^[0-9a-f]{12}$/",$mac)) DENY();
  	  	   if ($mac === '000000000000') DENY();
	
	  	   /* Rewrite mac address according to Cisco convention, XXXX.XXXX.XXXX */ 
	  	   $this->mac="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";	  	
	  	   
		   /* query system table */
		   $sql_query="select s.id as sid, s.mac as mac, s.name as hostname, s.description, s.status, u.id as uid,"
			   ."u.username, s.r_ip as ip, s.expiry, v.id as vid, v.default_name as vlan_name from systems s"
			   ." left join users u on s.uid=u.id left join vlan v on s.vlan=v.id where s.mac='{$this->mac}' limit 1";
		   //Found system in database, fill up the properties
		   if ($temp=mysql_fetch_one($sql_query))
		   {
                      $this->db_row=$temp;
		      $this->db_row['in_db']=true;
                      if (($object instanceof VMPSRequest) && ($this->vid == 0))
	                 DENY();
		   }
		   else 
		   {
		      //System unknown
		      $this->db_row['status']=0;
		      $this->db_row['mac']=$this->mac;
		      $this->db_row['in_db']=false;
		      $this->logger->logit("Unknown device {$this->mac}");
		   }
		   $this->db_row['port_id']=0;
		   $this->db_row['office_id']=1;
		   $this->db_row['lastvlan_id']=1;
		}
	}



/* Policy Checks ------------------------------------------------------------ */

	/* isExpired
	 * Check that the system is not yet expired
	 * Expiry date like 0000-00-00 00:00:00 is treated as never expire
	 */
	public function isExpired() {
		/* check if expiry checks are enabled */
		if ($this->conf->check_for_expired) 
		{
			/* get systems expiry date*/
			$expiry = $this->expiry;
			
			if ($expiry)
			{
				/* 0000-00-00 00:00:00 means no expiry */
				if(strcmp(trim($expiry),"0000-00-00 00:00:00")==0) 
				{
					return false;
				}
		
            			$timestamp=date('Y-m-d H:i:s');
            			$time=time_diff($timestamp,$expiry);
            			if ($time<0) 
            			{
            				return true;
           			}
           		}
		} 
		else 
		{
			/* default is not to expire */
			$this->logger->logit("check_for_expired option is not enabled");
        		return false;
		}
	}

	/* system->isVM()    Is this system a Virtual Machine ?
	 * It is, if the ventor string associated to the first 3 bytes of the mac
	 * contains "vmware" or "parallels"
	 */
	public function isVM() {
		/* check if VM checks are enabled */
		if ($this->conf->vm_lan_like_host) {
			if (stristr($this->getVendor(),"vmware")) return true;    // the original
			if (stristr($this->getVendor(),"parallels")) return true; // Mac VMWare-alike
			// Todo: Check with Sean if its okay to think that all Microsoft OUIs are
			// VirtualPCs ?
			//if (stristr(this->getVendor(),"microsoft")) return true; // VirtualPC
		}
		else
		{
			$this->logger->logit("vm_lan_like_host option is not enabled");
			return false; 
		}
	}

	/* Is this device 'killed'? */
	public function isKilled() {
		if ($this->status==7)
			return true;
		else
			return false;
	}

	/* Is this device 'active'? */
	public function isActive() {
		if ($this->status==1)
			return true;
		else
			return false;
	}
	
	 /* Is this device 'unknown'? */
        public function isUnknown() {
                if ($this->status==0)
                        return true;
                else
                        return false;
        }

	public function isUnmanaged() {
		if ($this->status==3)
			return true;
		else
			return false;
	}
	
	
/* Information about system ------------------------------------------------- */
	
	/* Return the vendor name associated to first 3 bytes of the mac address 
	 */
	public function getVendor() {
		/* Todo: Implement a vendor Cache in an arry 
		 * to save an sql statement per request ;-) 
		 */
                 $mac=preg_replace('/\./','',$this->mac);
                 $prefix="$mac[0]$mac[1]$mac[2]$mac[3]$mac[4]$mac[5]";
                 $query="select vendor from ethernet where mac like '%$prefix%';";
                 $vendor=v_sql_1_select($query);
		 $vendor=rtrim($vendor,',');
		 return trim($vendor);
	}
	
	/* Return the Name of the systems default VLAN */
	public function getVlanName() {
		return $this->vlan_name;	
	}

	public function getVlanID() {
		return $this->vid;
	}

	/* Universal Accessor Method
	 * We are redirecting all unresolved method calls to this handler, 
	 * so that we can emulate arbitraty accessor methods.
	 * With this trick, the user can add new fields to the system tables
	 * and will be able to access them in the policy as
	 * $system->getDBFieldName() without haveing to change this class
	 */
	public function __call($methodName, $parameters) {
		/* If methodname starts with get */
		if (substr($methodName,0,3) == "get") {
			$dbfieldname = substr($methodName,3);
			foreach(array_keys($this->db_row) as $key) {
				if (strtolower($key) == strtolower($dbfieldname)) {
					return $this->db_row[$key];
				}
			}
		}
		/*If the db field does not exists, Log Error */
		$this->logger->logit("Field $methodName doesn't exist");
		/*Then DENY as default action */
		DENY();
	}
	
	# Get the value of only one var if it exists
	public function __get($key)                                                  //Get the value of one var
   	{
		if (array_key_exists($key,$this->db_row))
		   return $this->db_row[$key];
   	}

	# Set the value of only one var if it exists
        protected function __set($key,$value)                                                  //Set the value of one var
        {
                if (array_key_exists($key,$this->db_row))
                   $this->db_row[$key]=$value;
                else
                   DENY();
        }


	#Return all properties assigned to this system. This method is here only for debugging purposes, please delete it after
	public function getAllProps()
	{
		return $this->db_row;
	}

	#This method indicates if a system is in the db. This flag was set in the constructor.
	public function inDB()
	{
		return $this->in_db;
	}

	#Update EndDevice information in the DB
	public function update()
	{
	   if ($this->inDB())
	   {
	      if ($this->isExpired() && $this->conf->disable_expired_devices)
	         $query="UPDATE systems SET LastSeen=NOW(), status=7, LastPort={$this->port_id}, LastVlan='{$this->lastvlan_id}' where id='{$this->sid}';";
	      else
	         $query="UPDATE systems SET LastSeen=NOW(), LastPort={$this->port_id}, LastVlan='{$this->lastvlan_id}' where id='{$this->sid}';";
	      $res=mysql_query($query);
	      if ($res)
	      {
	         return true;
	      }
	      else
	      {
	         return false;
	      }
	   }
	}

	protected function getEndDeviceID()
	{
	   return $this->sid;
	}

	#Insert an EndDevice if it is not in the DB
	public function insertIfUnknown()
	{
	   if (!$this->inDB() && $this->port_id)
	   {
	      #Enterprise only
	      if ($this->conf->lastseen_sms)
	      {
	          $retval='';
                  $sms_details=syscall($this->conf->sms_mac." ".$this->mac, $retval);

                  # Enable PC and set to SMS VLAN
                  if(preg_match("/Host=(\S+) NtAccount=(\S+) OS=(.+)$/",$sms_details, $matches))
                  {
                     $sms_name=$matches[1];
                     $txx_name= $conf->default_user_unknown;         # default
                     $txx_name   =$matches[2];
                     $sms_os     =$matches[3];
                     $sms_details="";
                     $this->logger->logit("SMS PC: name=$sms_name, NTaccount=$txx_name, $sms_os, $sms_details");
                     $os3_id=v_sql_1_select("select id from sys_os3 where value='$sms_os'");

                     insert_user($txx_name);
                     #logit("Inserted new user $txx_name");
                     direx_sync_user($txx_name);
                     #logit("Symced new user $txx_name");

                     $uid=v_sql_1_select("select id from users where username like '$txx_name'");
                     if (!$uid)
                        $uid=0;
                     if ($conf->lastseen_sms_vlan)
                        $vlan_id=$this->conf->lastseen_sms_vlan;
                     $query="INSERT INTO systems "
                     . "SET LastSeen=NOW(), status=1, class=2,"      # active, GWP
                     .      "description='$txx_name', "   # nt account
                     .      "uid='$uid', "
                     .      "name='$sms_name', "
                     .      "comment='$sms_details', "
                     .      "vlan='$vlan_id', "
                     .      "os3='$os3_id', "
                     .      "os4='$sms_os', "
                     .      "lastport='{$this->port_id}', "
                     .      "office='{$this->office_id}', "
                     .      "mac='{$this->mac}' ";
                     $res = mysql_query($query);
                     if ($res)
		     { 
                        // Document the user's details in the alert
                        $query="SELECT CONCAT(Givenname,' ',Surname,' ',Department,' ',Mobile) from users where username='$txx_name'";
                        $res = mysql_query($query);
                        if ($res) 
	                {
                           list($sms_details)=mysql_fetch_array($res);
                           $subject="NAC alert in {$this->patch_info}, $sms_details, port {$this->port_info}\n";
                           $mesg="New {$this->conf->sms_device} {$this->mac}({$this->getVendor()}) $sms_name, $txx_name, $sms_details, switch {$this->switch_info}\n";
	                   $this->logger->logit($subject);
	                   $this->logger->logit($mesg);
	                   snmp_restart_port_id($this->port_id);
		           return true;
	                }
	                else
	                {
	                   $this->logger->logit(mysql_error(),LOG_ERROR);
                           return false;
	                }
		     }
	             else
		     {
		        $this->logger->logit(mysql_error(),LOG_ERROR);
                        return false;
	 	     }
                  }

	      }
	      else
	      #Normal case
	      {
	         $query="insert into systems set lastseen=NOW(), status='{$this->conf->set_status_for_unknowns}', name='unknown', vlan='{$this->conf->set_vlan_for_unknowns}',lastport='{$this->port_id}', office='{$this->office_id}', description='".$this->conf->default_user_unknown."', uid='1', mac='{$this->mac}';";
	         $res=mysql_query($query);
	         if ($res)
	         {
	            $this->db_row['in_db']=true;
	            $subject="NAC alert {$this->patch_info} port {$this->port_info}\n";
		    $mesg="New unknown {$this->mac}({$this->getVendor()}), switch {$this->switch_info} Patch: {$this->patch_info}\n";
	            $this->logger->logit($subject);
	            $this->logger->logit($mesg);
	            return true;
	         }
	         else
	         {
	            $this->logger->logit(mysql_error(),LOG_ERROR);
	            return false;
	         }
	      }
	   }
	}

	#Linking between Port and EndDevice.
	public function onPortID($port=0)
	{
	   if (is_numeric($port) && ($port>=0))
	   {
	      $this->db_row['port_id']=$port;
	      return true;
	   }
	   else
	   {
	      return false;
	   }   
	}

	#Linking between Port and EndDevice.
	public function inOfficeID($office=1)
	{
	   if (is_numeric($office) && ($office>0))
	   {
	      $this->db_row['office_id']=$office;
	      return true;
	   }
	   else
	   {
	      return false;
	   }
	}

	#Linking between Port and EndDevice.
	public function onVlanID($vlan=1)
	{
	   if (is_numeric($vlan) && ($vlan>=1))
	   {
	      $this->db_row['lastvlan_id']=$vlan;
	      return true;
	   }
	   else
	   {
	      return false;
	   }
	}

	public function setPortInfo($var)
	{
	   if ($var)
           {
	      $this->db_row['port_info']=$var;
	      return true;
	   }
	   else
	   {
	      return false;
	   }
	}

	public function setSwitchInfo($var)
	{
	   if ($var)
	   {
	      $this->db_row['switch_info']=$var;
	      return true;
	   }
	   else
	   {
	      return false;
	   }
	}

	public function setPatchInfo($var)
	{
	   if ($var)
           {
              $this->db_row['patch_info']=$var;
              return true;
           }
           else
           {
              return false;
           }

	}
}

?>
