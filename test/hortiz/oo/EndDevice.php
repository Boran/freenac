<?php
/**
 * enddevice.php
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
include_once('funcs.inc');

class EndDevice
{
   private $props=array();
   private static $instance;
   
   public function __construct($mac)
   {
      // Returns an array containing all variables defined in the config table
      if (empty($mac))
         return undef;
      $query="select id as sid, mac, expiry as expiry_date, name as hostname, vlan as default_vlan, os from systems where mac='$mac' limit 1;";						//Query
      $query="select s.id as sid, s.mac as mac, s.name as hostname, s.description, s.status, u.id as uid, u.username, s.r_ip as ip, s.expiry as expires, v.id as vid, v.default_name as vlan_name from systems s inner join users u on s.uid=u.id inner join vlan v on s.vlan=v.id where s.mac='$mac'";
      $temp=mysql_fetch_one($query);
      $this->props=$temp;							//Et voila, return it
   }

   public function __set($key,$val)						//Set a var in our array
   {
      $this->props[$key]=$val;
   }

   public function __get($key)							//Get the value of one var
   {
      return $this->props[$key];
   }

   public function getAllProperties()						//Get our inner array
   {
      return $this->props;
   }

   public function is_VM()
   {
      $counter=0;
      $parts=explode(".",$this->mac);
      $temp_mac=$parts[0].$parts[1].$parts[2];
      $query="select mac from ethernet where vendor like '%vmware%';";
      $res=mysql_query($query);
      if ($res)
      {
         while ($rows=mysql_fetch_array($res,MYSQL_ASSOC))
         {
            if (stripos($temp_mac,$rows['mac'])!==FALSE)
               $counter++;
         }
      }
      if ($counter>0)
         return true;
      else 
         return false; 
   }

   public function getVlanName()
   {
      return $this->vlan_name;
   }

   public function isExpired()
   {
      if (empty($this->expiry_date))
         return false;
      $timestamp=date('Y-m-d H:i:s');
      $time=time_diff($timestamp,$this->expires);
      echo $time;
      if ($time<0)
         return true;
      else 
         return false;
   }
}

?>
