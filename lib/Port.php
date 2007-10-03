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
 * @author                      Sean Boran (FreeNAC Core Team)
 * @author			Hector Ortiz (FreeNAC Core Team)
 * @author			Thomas Seiler (contributer)
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
      #If we don't receive an object, DENY
      if (!$object)
         DENY('No object received in constructor');
      parent::__construct();	
      if (($object instanceof VMPSRequest) || ($object instanceof VMPSResult))
      {
         # Get needed parameters from object
         $switchip=trim($object->switch);
         $portname=trim($object->port);
         $domain=trim($object->vtp);
         $lastvlan=trim($object->lastvlan);
   
         #In case we have a DENY as result from vmpsd_external, so no vlan would come in the object. If so, set vlan to
         # '--NONE--' which should deny access
         if (($object instanceof VMPSResult) && (!$lastvlan))
            $lastvlan="--NONE--";
         
         # Invalid parameters?
         if ((strlen($switchip) < 8) || (strlen($portname) <1)) {
            DENY('Invalid parameters');
         }
        
         $query=<<<EOF
         SELECT sw.id AS switch_id, sw.ip AS switch_ip, sw.name AS switch_name, sw.comment AS switch_comment, sw.notify AS notify, p.default_vlan, p.last_vlan,
            p.id AS port_id, p.name AS port_name, p.default_vlan, l.id AS office_id, l.name AS office,b.name AS building
            FROM switch sw LEFT JOIN port p ON sw.id=p.switch and p.name='$portname' LEFT JOIN location l ON sw.location=l.id
            LEFT JOIN building b ON l.building_id=b.id WHERE sw.ip='$switchip' limit 1;
EOF;
	 $this->logger->debug($query,3);
         if ($temp=mysql_fetch_one($query))
         {
            #Information found in DB.
            $this->props=$temp;

            #Lookup a vlan_id to assign depending on the switch location
            $query=<<<EOF
            SELECT vs.vlan_id FROM vlanswitch vs INNER JOIN vlan v ON vs.vid=v.id
               INNER JOIN switch s ON s.id=vs.swid WHERE s.ip='$switchip';
EOF;
            $this->logger->debug($query,3);
            $this->props['exception_vlan']=v_sql_1_select($query);
            
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
         #Chech first if we have this port in the db and if we have an office Id for this port.
         if ($this->conf->lastseen_patch_lookup && $this->port_in_db && $this->office_id)
         {
            #If so, lookup Users
            $query="SELECT GROUP_CONCAT(Surname) AS Surname FROM users WHERE PhysicalDeliveryOfficeName='" . $this->office . "'";
            $this->logger->debug($query,3);
            $users=v_sql_1_select($query);
            $this->props['users_in_office']=$users;

            #Then, patchcable information
            $query="SELECT CONCAT(outlet,', {$this->office}, ',comment) FROM patchcable WHERE port='" . $this->port_id . "'";
            $this->logger->debug($query,3);
            $patch_details=v_sql_1_select($query);
            $this->props['patch_details']=$patch_details;
         }

         #If the object is a syslog message, lookup the vlan_id for that vlan and set it to last_vlan
         if ($object instanceof VMPSResult)
         {
            $query="SELECT id FROM vlan WHERE default_name='$lastvlan';";
            $this->logger->debug($query,3);
            $temp_vlan=v_sql_1_select($query);
            if ($temp_vlan>0)
               $this->props['last_vlan']=$temp_vlan;
            else
               $this->props['last_vlan']=0;
         }
      }
      else
      {
         #Object is an unknown instance
         DENY('Unknown instance of object passed to the constructor');
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
         $query=<<<EOF
         SELECT s.mac AS mac,s.lastvlan AS lastvlan FROM systems s INNER JOIN port p ON
            s.lastport=p.id INNER JOIN switch sw ON p.switch=sw.id AND p.name='{$this->name}'
            AND sw.ip='{$this->switch_ip}' WHERE DATE_SUB(CURDATE(), INTERVAL 2 HOUR) <= s.lastseen
            ORDER BY lastseen DESC LIMIT 1;
EOF;
         $this->logger->debug($query,3);
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
         $query="INSERT INTO switch SET ip='{$this->switch_ip}', name='{$this->switch_name}',comment='';";
         $this->logger->debug($query,3);
         $res=mysql_query($query);
         if ($res)
         {
            #Switch has been inserted, lookup its id
            $query="SELECT id FROM switch WHERE ip='{$this->switch_ip}' LIMIT 1;";
            $this->logger->debug($query,3);
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
         $query="INSERT INTO port SET name='{$this->port_name}', switch='{$this->switch_id}', last_vlan='{$this->last_vlan}', last_activity=NOW();";
         $this->logger->debug($query,3);
         $res=mysql_query($query);
         if ($res)
         {
            #Port has been inserted, lookup its id
	    $query="SELECT id FROM port WHERE name='{$this->port_name}' AND switch='{$this->switch_id}' LIMIT 1;";
            $this->logger->debug($query,3);
            $this->props['port_id']=v_sql_1_select($query);

            #If we have its id, change the value of our control flag
	    if ($this->port_id)
	       $this->port_in_db=true;

            #Log it and increase our internal counter to indicate that an insert has been done. Also, if we have patchcable
            #information, add it to the log message
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
         return $this->patch_details .'('. $this->users_in_office .')';
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
         $query="UPDATE port SET last_activity=NOW(), last_vlan='{$this->last_vlan}' WHERE id='{$this->port_id}'";
         $this->logger->debug($query,3);
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
      return "{$this->switch_ip} ({$this->switch_name}: {$this->switch_comment})"; 
   }

   /**
   * Get port name
   * @return mixed    Port name
   */
   public function getPortInfo()
   {
      return $this->port_name;
   }

   /**
   * Get list of people to notify about problems related to this switch
   * @return mixed	List of emails, NULL if nothing is defined
   */
   public function getNotifyInfo()
   {
      if ($this->notify)
         return $this->notify;
      else
         return NULL;
   }
}

?>
