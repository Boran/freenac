<?php
/**
 * Port.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      FreeNAC Core Team
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

/**
 * The Port class describes the interfaces to information on Ports and Switches
 * This class extends the {@link Common} class.
 */
class Port extends Common
{
   private $props=array();
   
   /** 
   * The constructor takes the parameters needed to generate a Port object
   * Access is read-only.
   * @param object $object      A copy of the Request
   */
   function __construct($object)
   {
      parent::__construct();	
      if (($object instanceof VMPSRequest) || ($object instanceof VMPSResult))
      {
         # Get needed parameters from object
         $switchip=$object->switch;
         $portname=$object->port;
         $domain=$object->vtp;
         $lastvlan=$object->lastvlan;
         # Invalid parameters?
         if ((strlen($switchip) < 8) || (strlen($portname) <1)) {
            return undef;
         }
        
         #In case we have a DENY as result from vmpsd_external, so no vlan would come in the object. If so, set vlan to 
         # '--NONE--' which should deny access 
	 if (($object instanceof VMPSResult) && (!$lastvlan))
            $lastvlan="--NONE--";
         // Returns an array containing all variables defined in the config table
         // TBD: query is a first draft, there is probably too much in there.
         /*$query=<<<EOF
SELECT DISTINCT port.id, switch, switch.ip as switchip, switch.name as SwitchName, 
  default_vlan, last_vlan, v1.default_name as LastVlanName, 
  port.name,  restart_now, port.comment, last_activity, 
  auth_profile.method as VlanAuth,
  CONCAT(switch.name, ' ', port.name) as switchport  
  FROM port 
  INNER JOIN switch     ON port.switch = switch.id 
  LEFT  JOIN patchcable ON patchcable.port = port.id 
  LEFT  JOIN location   ON patchcable.office = location.id   
  LEFT  JOIN auth_profile ON auth_profile.id = port.auth_profile
  LEFT  JOIN vlan v1    ON port.last_vlan = v1.id
EOF;
         $query .=" WHERE port.name='$portname' and switch.ip='$switchip' LIMIT 1";*/
         $query="select sw.id as switch_id, sw.ip as switch_ip, sw.name as switch_name, sw.comment as switch_comment, p.default_vlan, p.last_vlan, p.id as port_id, p.name as port_name, p.default_vlan, l.id as office_id, l.name as office,b.name as building from switch sw left join port p on sw.id=p.switch and p.name='$portname' left join location l on sw.location=l.id left join building b on l.building_id=b.id where sw.ip='$switchip' limit 1;";
	 if ($temp=mysql_fetch_one($query))
         {
            #Information found in DB.
            $this->props=$temp;
            $this->props['exception_vlan']=v_sql_1_select("select vs.vlan_id from vlanswitch vs inner join vlan v on vs.vid=v.id"
                                ." inner join switch s on s.id=vs.swid where s.ip='$switchip'");
            #Initialize control flags
	    if ($this->switch_ip)
               $this->props['switch_in_db']=true;
            else
               $this->props['switch_in_db']=false;

            if ($this->port_name)
               $this->props['port_in_db']=true;
            else
               $this->props['port_in_db']=false;
            
            #Just in case we didn't get a port_name from the DB
	    if (!$this->port_name)
               $this->port_name=$portname;
         }
         else
         {
            #No information found in DB, so get data from the request
            $this->props['switch_ip']=$switchip;
            $this->props['port_name']=$portname;
            $this->props['switch_name']=gethostbyaddr($switchip);
            
            #Initialize control flags
	    $this->props['switch_in_db']=false;
            $this->props['port_in_db']=false;
	 }

         #Should we lookup patch information?
         if ($this->conf->lastseen_patch_lookup && $this->port_in_db && $this->office_id)
         {
            $query="SELECT GROUP_CONCAT(Surname) as Surname from users WHERE PhysicalDeliveryOfficeName='" . $this->office . "'";
            $users=v_sql_1_select($query);
            $this->props['users_in_office']=$users;
            $query="select CONCAT(outlet,', {$this->office}, ',comment) from patchcable where port='" . $this->port_id . "'";
            $patch_details=v_sql_1_select($query);
            $this->props['patch_details']=$patch_details;
         }

         #If the object is a syslog message, lookup the vlan_id for that vlan and set it to last_vlan
         if ($object instanceof VMPSResult)
         {
            $temp_vlan=v_sql_1_select("select id from vlan where default_name='$lastvlan';");
            if ($temp_vlan>0)
               $this->props['last_vlan']=$temp_vlan;
            else
               $this->props['last_vlan']=0;
         }
      }
   }

   /**
   * Get the value of one property if it exists
   * @param mixed $key		Property to lookup
   * @return mixed		The value of the wanted property, or false if such a property doesn't exist
   */
   protected function __get($key)							
   {
      if (array_key_exists($key,$this->props))
      {
         return $this->props[$key];
      }
      else
      {
         $this->logger->logit("Property $key not found",LOG_WARNING);
         return false;
      }      
   }

   /**
   * Set the value of one property if it exists
   * @param mixed $key		Property to lookup
   * @param mixed $value	Value to set the desired property to
   * @return boolean		True if successful, false otherwise
   */
   private function __set($key,$value)
   {
      if (array_key_exists($key,$this->props))
      {
         $this->props[$key]=$value;
         return true;
      }
      else
      {
         $this->logger->logit("Property $key not found",LOG_WARNING);
         return false;
      }
   }

   /**
   * Return all properties assigned to this system. This method is here only for debugging purposes, please delete it after
   * @return array      All properties present in this object
   */
   public function getAllProps()						
   {
      return $this->props;
   }

   /**
   * Get the default vlan assigned to a Port
   * @return mixed	 Vlan assigned to a port, false otherwise
   */
   public function getPortDefaultVlan()
   {
	if ($this->conf->use_port_default_vlan)
	   return $this->default_vlan;
	else
	{
           $this->logger->logit("Option use_port_default_vlan not enabled",LOG_WARNING);
	   return false;
        }
   }

   /**
   * Get a vlan based on switch location. This vlan should be used as an exception to the regular process
   * @return mixed	Vlan to assign, false otherwise
   */
   public function vlanBySwitchLocation()
   {
      if ($this->conf->vlan_by_switch_location)
      {
         if ($this->exception_vlan)
            return $this->exception_vlan;
         else
            return false;
      }
      else
      {
         $this->logger->logit("Option vlan_by_switch_location not enabled",LOG_WARNING);
         return false;
      }
   }

   /**
   * Get the vlan used by a system seen on this same port during the last 2 hours.
   * This is usefull to assign a vlan for VMs without causing flapping.
   * @return mixed	Vlan to assign, false otherwise
   */
   public function getVMVlan()
   {
      if ($this->conf->vm_lan_like_host)
      {
         #Lookup the last_vlan assigned to the last system which was lastseen on this port in the previous 2 hours
         $query="select s.mac as mac,s.lastvlan as lastvlan from systems s inner join port p on "
               ."s.lastport=p.id inner join switch sw on p.switch=sw.id and p.name='{$this->name}'"
               ." and sw.ip='{$this->switch_ip}' where date_sub(curdate(), interval 2 hour) <= s.lastseen"
               ." order by lastseen desc limit 1;";
         $vm_vlan=v_sql_1_select($query);
         if ($vm_vlan)
            return $vm_vlan;
         else
            return false;
      }
      else
      {
         $this->logit("Option vm_lan_like_host is not enabled",LOG_WARNING);
         return false;
      }
   }
 
   /**
   * Tell if this switch is in the DB
   * @return boolean 		Value of the 'switch_in_db' property
   */
   public function isSwitchInDB()
   {
      return $this->switch_in_db;
   }

   /**
   * Tell if this port is in the DB
   * @return boolean            Value of the 'port_in_db' property
   */
   public function isPortInDB()
   {
      return $this->port_in_db;
   }

   /**
   * Insert a switch or port into the DB if it doesn't exist.
   * @return boolean		True if an insert operation was performed, false otherwise
   */
   public function insertIfUnknown()
   {
      $counter=0;
      #Insert switch in database if it doesn't exist
      if (!$this->isSwitchInDB())
      {
         $query="insert into switch set ip='{$this->switch_ip}', name='unknown',comment='';";
         $res=mysql_query($query);
         if ($res)
         {
            #Switch has been inserted, lookup its id
            $query="select id from switch where ip='{$this->switch_ip}' limit 1;";
	    $this->props['switch_id']=v_sql_1_select($query);

            #If we have its id, change the value of our control flag
            if ($this->switch_id)
               $this->switch_in_db=true;

            #Log it and increase our internal counter to indicate that an insert has been done
            $this->logger->logit("New switch entry {$this->switch_ip} ({$this->switch_name}), please update the description.");
            $counter++;
         }
         else
         {
            $this->logger->logit(mysql_error(),LOG_ERROR);
            return false;
         }   
      }
      #Insert port in database if it doesn't exist
      if (!$this->isPortInDB())
      {
         $query="insert into port set name='{$this->port_name}', switch='{$this->switch_id}', last_vlan='{$this->last_vlan}', last_activity=NOW();";
         $res=mysql_query($query);
         if ($res)
         {
            #Port has been inserted, lookup its id
	    $query="select id from port where name='{$this->port_name}' and switch='{$this->switch_id}' limit 1;";
            $this->props['port_id']=v_sql_1_select($query);

            #If we have its id, change the value of our control flag
	    if ($this->port_id)
	       $this->port_in_db=true;

            #Log it and increase our internal counter to indicate that an insert has been done
            if ($this->conf->lastseen_patch_lookup)
               $this->logger->logit("New port {$this->port_name}. Location from patchcable: {$this->getPatchInfo()}\n");
            else
               $this->logger->logit("New port {$this->port_name} in switch {$this->switch_ip} ({$this->switch_name})"); 
            $counter++;
         }
         else
         {
            $this->logger->logit(mysql_error(),LOG_ERROR);
            return false;
         }
      }
      #Return true if our counter is greater than zero. Thus we know that an insert operation has been performed
      if ($counter)
         return true;
      else
         return false;
   }

   /**
   * Get patch information related to this port
   * @return mixed	Patch information
   */
   public function getPatchInfo()
   {
      if ($this->conf->lastseen_patch_lookup)
      {
         return $this->patch_details.'('.$this->users_in_office.')';
      }
      else
      {
         $this->logger->logit("Option lastseen_patch_lookup not enabled\n",LOG_WARNING);
         return false;
      }
   }   

   /**
   * Update last_vlan and last_activity fields for this port
   * @return boolean	True if successful, false otherwise
   */
   public function update()
   {
      if ($this->isPortInDB())
      {
         $query="update port set last_activity=NOW(), last_vlan='{$this->last_vlan}' where id='{$this->port_id}'";
         $res=mysql_query($query);
         if ($res)
         {
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
         return false;
      }
   }

   #These functions are designed to pass information from this class to the EndDevice object in vmps_lastseen

   /**
   * Get the id for this port
   * @return integer	Value of the 'port_id' property
   */
   public function getPortID()
   {
      return $this->port_id;
   }

   /**
   * Get the id of the office where this port is located
   * @return integer    Value of the 'office_id' property
   */
   public function getOfficeID()
   {
      return $this->office_id;
   }

   /**
   * Get the id of the last_vlan
   * @return integer    Value of the 'last_vlan' property
   */
   public function getLastVlanID()
   {
      return $this->last_vlan;
   }

   /**
   * Get switch information related to this port
   * @return mixed    Value of the 'switch_ip' and 'switch_comment' properties
   */
   public function getSwitchInfo()
   {
      if (strcasecmp(trim($this->switch_name),'unknown')==0)
         return "{$this->switch_ip}({$this->switch_comment})";
      else
         return "{$this->switch_name}({$this->switch_comment})"; 
   }

   /**
   * Get port name
   * @return mixed    Port name
   */
   public function getPortInfo()
   {
      return $this->port_name;
   }
}

?>
