<?php
/**
 * port.php
 *
 * Long description for file:
 *
 * This file defines the Settings class which is a Singleton
 * used to hold global variables stored in the configuration files and running script.
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
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

class Port
{
   private $props=array();
   

   function __construct($switchip, $portname)
   {
      if ((strlen($switchip) < 8) || (strlen($portname) <1)) {
        // Invalid parameters
        logit("new Port(): invalid parameters, switchip=$switchip, portname=$portname");
        return undef;
      }

      // Returns an array containing all variables defined in the config table
      #$this->props=mysql_fetch_all("select * from port");
      $query=<<<EOF
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
      $query .=" WHERE port.name='$portname' and switch.ip='$switchip' LIMIT 1";
      #echo $query ."\n";
      $this->props=mysql_fetch_one($query);
   }


   public function __set($key,$val)						//Set a var in our array
   {
      $this->props[$key]=$val;
   }

   public function get1($key)							//Get the value of one var
   {
      echo "Called get1() with $key \n";
      return $this->props[$key];
   }
   public function __get($key)							//Get the value of one var
   {
      echo "Called get() with $key \n";
      return $this->props[$key];
   }

   public function getAll()						//Get our inner array
   {
      #foreach ( $this->props as $row) {
      #  print_r($row);
      #}
      print_r ($this->props);
   }
   public function getAllProperties()						//Get our inner array
   {
      return $this->props;
   }

   public function getPortDefaultVlan()
   {
      return default_vlan;
   }
}

?>
