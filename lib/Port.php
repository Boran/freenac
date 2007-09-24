<?php
/**
 * port.php
 *
 * Long description for file:
 *
 * The Port class describes the interfaces to information on Ports and Switches
 * TBD; update the rest of this description
 *
 * CONSTRUCTOR SUMMARY:
 * *	private __construct(array $var_list, array $exclude_list);
 *		Compute the difference of $var_list and $exclude_list and store it in an internal array.
 *	PARAMETERS: 
 *		$var_list : 	An array containing the list of variables defined.
 *		$exclude_list :	The list of variables we want to exclude from our final array.	
 *
 * INTERCEPTOR SUMMARY:
 * *	public __set($key,$value);
 *		Set the key $key with the value $val stored in our internal array. If $key doesn't existe, create it.
 *	PARAMETERS:
 *		$key :		The key in our internal array that we want to set.
 *		$value :	The value we want to assign to this key.
 *
 * *	public __get($key);
 *		Get the value of key $key from our internal array.
 *	PARAMETERS:
 *		$key : 		The key we want to retrieve from our internal array.
 *
 * METHOD SUMMARY: 
 * *	public static getInstance(array $vars=array(),array $list=array('GLOBALS','^_','^HTTP'));
 *		Create an instance of the Settings class if one hasn't been defined yet and return the instance to the calling code.
 *	PARAMETERS:
 *		array $vars:	Array containing the list of vars defined. Ideally, the result of get_defined_vars() should be passed on.
 *				If no $vars has been passed, an empty array will be used.
 *		array $list:	Array containing the list of vars we want to exclude.
 *				If no $list has been passed, a default list, which excludes PHP defined vars, is used.	
 * 
 * * 	public getAllProperties();
 *		Returns the internal array which contains the vars in the configuration files.
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

class Port extends Common
{
   private $props=array();
   function __construct($object)
   {
      parent::__construct();
      if (($object instanceof VMPSRequest) || ($object instanceof VMPSResult))
      {
         $switchip=$object->switch;
         $portname=$object->port;
         $domain=$object->vtp;
         $lastvlan=$object->lastvlan;
         // Invalid parameters ?
         if ((strlen($switchip) < 8) || (strlen($portname) <1)) {
            //logit("new Port(): invalid parameters, switchip=$switchip, portname=$portname");
            return undef;
         }

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
         $query="select sw.id as switch_id, sw.ip as switch_ip, sw.name as switch_name, p.default_vlan, p.last_vlan, p.id as port_id, p.name as port_name, p.default_vlan, l.id as office_id, l.name as office,b.name as building from switch sw left join port p on sw.id=p.switch and p.name='$portname' left join location l on sw.location=l.id left join building b on l.building_id=b.id where sw.ip='$switchip' limit 1;";
	 if ($temp=mysql_fetch_one($query))
         {
            $this->props=$temp;
            $this->props['exception_vlan']=v_sql_1_select("select vs.vlan_id from vlanswitch vs inner join vlan v on vs.vid=v.id"
                                ." inner join switch s on s.id=vs.swid where s.ip='$switchip'");
	    if ($this->switch_ip)
               $this->props['switch_in_db']=true;
            else
               $this->props['switch_in_db']=false;
            if ($this->port_name)
               $this->props['port_in_db']=true;
            else
               $this->props['port_in_db']=false;
	    if (!$this->port_name)
               $this->port_name=$portname;
         }
         else
         {
            $this->props['switch_ip']=$switchip;
            $this->props['port_name']=$portname;
            $this->props['switch_name']=gethostbyaddr($switchip);
	    $this->props['switch_in_db']=false;
            $this->props['port_in_db']=false;
	 }
         if ($this->conf->lastseen_patch_lookup && $this->port_in_db && $this->office_id)
         {
            $query="SELECT GROUP_CONCAT(Surname) as Surname from users WHERE PhysicalDeliveryOfficeName='" . $this->office . "'";
            $users=v_sql_1_select($query);
            $this->props['users_in_office']=$users;
            $query="select CONCAT(outlet,', {$this->office}, ',comment) from patchcable where port='" . $this->port_id . "'";
            $patch_details=v_sql_1_select($query);
            $this->props['patch_details']=$patch_details;
         }
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

   protected function __get($key)							//Get the value of one var
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

   public function getAllProps()						//Get our inner array
   {
      return $this->props;
   }

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

   public function getVMVlan()
   {
      if ($this->conf->vm_lan_like_host)
      {
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
 
   public function isSwitchInDB()
   {
      return $this->switch_in_db;
   }

   public function isPortInDB()
   {
      return $this->port_in_db;
   }

   public function insertIfUnknown()
   {
      #Insert switch in database if it doesn't exist
      if (!$this->isSwitchInDB())
      {
         $query="insert into switch set ip='{$this->switch_ip}', name='unknown';";
         $res=mysql_query($query);
         if ($res)
         {
            $this->switch_in_db=true;
            $query="select id from switch where ip='{$this->switch_ip}' limit 1;";
	    $this->props['switch_id']=v_sql_1_select($query);
            $this->logger->logit("New switch entry {$this->switch_ip} ({$this->switch_name}), please update the description.");
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
            $this->port_in_db=true;
	    $query="select id from port where name='{$this->port_name}' and switch='{$this->switch_id}' limit 1;";
            $this->props['port_id']=v_sql_1_select($query);
            $this->logger->logit("New port {$this->port_name} in switch {$this->switch_ip} ({$this->switch_name})"); 
         }
         else
         {
            $this->logger->logit(mysql_error(),LOG_ERROR);
            return false;
         }
      }
      return true;
   }

   public function patch_information()
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
   public function getPortID()
   {
      return $this->port_id;
   }

   public function getOfficeID()
   {
      return $this->office_id;
   }

   public function getLastVlanID()
   {
      return $this->last_vlan;
   }
}

?>
