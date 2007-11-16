<?php
/**
 * VMPSRequest.php
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


/**
 * Class that contains information related to a VMPS request
 * This class extends the {@link Request} class
 */
final class VMPSRequest	extends Request		# Disallow inheriting from this class
{
   private $props=array();			# Here we hold our internal properties
   private static $instance=NULL;			# Our instance of this class
   public $switch_port=NULL;
   public $host=NULL;

   public function __construct($tmac, $tswitch,$tport,$tvtp,$tlastvlan)
   {	
      #Sanity checks
      if ((strlen($tmac)>0)&&(strlen($tswitch)>0)&&(strlen($tport)>0)&&(strlen($tvtp)>0)&&(strlen($tlastvlan)>0))
      {
         #Copy what we've received to our internal array
         $this->props['mac']=$tmac;
         $this->props['switch']=$tswitch;
         $this->props['port']=$tport;
         $this->props['vtp']=$tvtp;
         $this->props['lastvlan']=$tlastvlan;
         
         #Initialize a port and a system objects
         $this->switch_port=new CallWrapper(new Port($this));
         $this->host=new CallWrapper(new EndDevice($this));
      }
   }
  
   /**
   * Get the value of one property if it exists
   * @param mixed $key          Property to lookup
   * @return mixed              The value of the wanted property, or false if such a property doesn't exist
   */
   public function __get($key)		
   {
      if (array_key_exists($key,$this->props))	#First look if it exists in our internal array
         return $this->props[$key];		#If so, return it
      else
         return false;				#Nothing found, return false
   }

   /**
   * Disallow overriding this method. This prevents our internal
   * array from being modified by other classes.
   */
   final public function __set($key,$value) {}

   /**
   * Prevent clonning the instance
   * @throws 	Exception indicating that a clone operation cannot be performed
   */
   final public function __clone()		
   {
      throw new Exception("Cannot clone the VMPSRequest object");
   }

   /**
   * Get a copy of our EndDevice object
   * @return object	EndDevice
   */
   public function getEndDevice()
   {
      return $this->system;
   }

   /**
   * Get a copy of our Port object
   * @return object	Port
   */
   public function getPort()
   {
      return $this->port;
   }
}
