<?php

/*
 * System Class
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
	  	
	  	   // Todo: Update Query with SQL joins to user table and vlan table
	  	
	  	   #$sql_query="SELECT * FROM systems WHERE mac='" . $this->mac."';";
		   $sql_query="select s.id as sid, s.mac as mac, s.name as hostname, s.description, s.status, u.id as uid,"
			   ."u.username, s.r_ip as ip, s.expiry, v.id as vid, v.default_name as vlan_name from systems s"
			   ." inner join users u on s.uid=u.id inner join vlan v on s.vlan=v.id where s.mac='{$this->mac}'";
		   //Todo fill the db_row array
		   if ($temp=mysql_fetch_one($sql_query))
		   {
                      $this->db_row=$temp;
		      $this->db_row['in_db']=true;
		   }
		   else 
		   {
		      $this->db_row['status']=0;
		      $this->db_row['mac']=$this->mac;
		      $this->db_row['in_db']=false;
		      #Log unknown device   
		   }
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
			#Log: this option is not enabled
			/* default is not to expire */
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
			#Log: this option is not enabled
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
		// Todo: Log
		
		/*Then DENY as default action */
		DENY();
	}
	
	# Get the value of only one var if it exists
	protected function __get($key)                                                  //Get the value of one var
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


	#Return all properties asigned to this system. This method is here only for debugging purposes, please delete it after
	public function getAllProps()
	{
		return $this->db_row;
	}

	public function inDB()
	{
		return $this->in_db;
	}
}

?>
