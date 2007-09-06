<?php
/**
 * VMPSRequest.php
 *
 * Long description for file:
 *
 * Singleton to hold values related to a VMPS Request.
 *
 * CONSTRUCTOR SUMMARY:
 * *    private __construct();
 *              Empty
 *
 * INTERCEPTOR SUMMARY:
 * *    final public __set($key,$value);
 *		Avoid overwritting properties related to a VMPS request
 *      PARAMETERS:
 *              $key :          The key in our internal array that we want to set.
 *              $value :        The value we want to assign to this key.
 *
 * *    public __get($key);
 *              Get the value of key $key from our internal array.
 *      PARAMETERS:
 *              $key :          The key we want to retrieve from our internal array.
 *
 * *	final public __clone():
 *		Prevent clonning the instance  
 *
 * METHOD SUMMARY:
 * *    public setValues($mac,$switch,$port,$vtp,$lastvlan);
 *              Set the values of our internal properties to those of a VMPS request
 *      PARAMETERS:
 *		$mac :		MAC address of the connecting device
 *		$switch : 	Switch that sent the request
 *		$port :		Port where this MAC address has been learnt
 *		$vtp :		VTP domain
 *		$lastvlan :	Last vlan used for this device
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
*/


include_once "Request.php";

final class VMPSRequest	extends Request		//Disallow inheriting from this class
{
   private $props=array();			//Here we hold our internal properties
   private static $instance;			//Our instance of this class

   public function setValues($tmac, $tswitch,$tport,$tvtp,$tlastvlan)
   {	
      //Set our internal properties
      //Could we set our properties only once per request that comes into VMPS
      //and thus avoid rewriting these properties while we are attending that request?
      if ((strlen($tmac)>0)&&(strlen($tswitch)>0)&&(strlen($tport)>0)&&(strlen($tvtp)>0)&&(strlen($tlastvlan)>0))
      {
         $this->props['mac']=$tmac;
         $this->props['switch']=$tswitch;
         $this->props['port']=$tport;
         $this->props['vtp']=$tvtp;
         $this->props['lastvlan']=$tlastvlan;
         return true;
      }
      else return false;
   }
  
   private function __construct() {}

   public static function getInstance()
   {
      if (empty(self::$instance))		//Is there an instance of this class?
         self::$instance=new VMPSRequest();	//No, then create it
      return self::$instance; 			//Yes, return it 
   }

   public function __get($key)			//Return the value of a property
   {
      if (array_key_exists($key,$this->props))	//First look if it exists in our internal array
         return $this->props[$key];		//If so, return it
      else
         return NULL;				//Nothing found, return NULL
   }

   final public function __set($key,$value) {}	//Disallow overriding this method. This prevents our internal
						//array from being modified by other classes.

   final public function __clone()			//Prevent clonning the instance
   {
      throw new Exception("Cannot clone the VMPSRequest object");
   }
}
