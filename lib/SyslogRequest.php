<?php
/**
 * SyslogRequest.php
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
 * Class that contains information related to a syslog request
 * This class extends the {@link Result} class
 */
final class SyslogRequest extends Result		# Disallow inheriting from this class
{
   private $props=array();			# Here we hold our internal properties
   private static $instance=NULL;			# Our instance of this class
   public $switch_port=NULL;
   public $host=NULL;

   public function __construct($tmac, $tswitch,$tport,$tvtp,$tlastvlan='--NONE--')
   {
      parent::__construct();
      #Sanity checks
      if (is_string($tmac) && is_string($tswitch) && is_string($tport) && is_string($tvtp) && is_string($tlastvlan) &&
         (strlen($tmac)>0) && (strlen($tswitch)>0) && (strlen($tport)>0) && (strlen($tvtp)>0) && (strlen($tlastvlan)>0))
      {
         #Copy what we've received to our internal array
         $this->props['mac']=$tmac;
         $this->props['switch']=$tswitch;
         $this->props['port']=$tport;
         $this->props['vtp']=$tvtp;
         $this->props['lastvlan']=$tlastvlan;
         
         #Initialize a port and a system objects
         $this->props['created']=true;
	 $this->switch_port=new CallWrapper(new Port($this));
         $this->host=new CallWrapper(new EndDevice($this));
      }
      else
      {
         $this->logger->logit("A SyslogRequest object wasn't initialized. Only non empty strings are allowed to be passed to its constructor");
         $this->props['created']=false;
      }
   }
 
   /**
   * Get the value of one property if it exists
   * @param mixed $key          Property to lookup
   * @return mixed              The value of the wanted property, or false if such a property doesn't exist
   */
   public function __get($key)		
   {
      if ($this->props['created'])
      {
         if (array_key_exists($key,$this->props))       #First look if it exists in our internal array
            return $this->props[$key];          #If so, return it
         else
            return false;                               #Nothing found, return false
      }
      else
      {
         $this->logger->logit("SyslogRequest object contains no properties because it hasn't been properly initialized");
      }
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
      if ($this->created)
      {
         throw new Exception("Cannot clone the SyslogRequest object");
      }
      else
      {
         $this->logger->logit("A SyslogRequest object wasn't initialized");
      }
   }

   /**
   * Get a copy of our EndDevice object
   * @return object	EndDevice
   */
   public function getEndDevice()
   {
      if ($this->created)
      {
         return $this->system;
      }
      else
      {
         $this->logger->logit("EndDevice object hasn't been created");
      }
   }

   /**
   * Get a copy of our Port object
   * @return object	Port
   */
   public function getPort()
   {
      if ($this->created)
      {
         return $this->port;
      }
      else
      {
         $this->logger->logit("Port object hasn't been created");
      }
   }
}
