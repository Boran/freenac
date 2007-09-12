<?php
/**
 * SysLogger.php
 *
 * Long description for file:
 *
 * Define the syslogger class which is a Singleton and provide for syslogging facilities.
 * This class is a specialization (child) of the Logger class.
 *
 * CONSTRUCTOR SUMMARY:
 * *    private __construct();
 *              Empty
 *
 * INTERCEPTOR SUMMARY:
 * *    public __clone();
 *              Prevent clonning the instance of this class
 *
 * METHOD SUMMARY:
 * *    public static getInstance():
 *              Create an instance of the Settings class if one hasn't been defined yet and return the instance to the calling code.
 *                              If no $list has been passed, a default list, which excludes PHP defined vars, is used.
 *
 * *    public log($message):
 *              Log message to syslog
 *
 *		Parameters:
 *		$message	Message to log
 *
 * *	public debug($to_level,$message):	
 *		Wrapper around the log method. Log a message only if the specified level for this function
 *		is less or equal than the current debugging level.		
 *		
 *		Parameters:
 *		$to_level	Debugging level we want to output our message to
 *		$message	Message to log
 *
 * *	public setLevel($level):
 *		Set current debug level to a certain value
 *
 *		Parameters:
 *		$level		Debug level to set
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

require_once "Logger.php";

final class SysLogger implements Logger
{
   const MAX_LEVEL=3;				//Maximum debugging level
   static private $level=NULL;			//Current debugging level
   private static $instance=NULL;		//Instance of this class
   
   private function __construct() {}

   public static function getInstance()
   {
      if (empty(self::$instance))               //Is there an instance of this class?
         self::$instance=new SysLogger();	//No, then create it
      return self::$instance;                   //Yes, return it
   }

   public function __clone()                    //Prevent clonning the instance
   {
      throw new Exception("Cannot clone the SysLogger object");
   }

   public function log($message='')		//Implementation of the log method defined in the parent class
   {
      if (is_string($message)&&(strlen($message)>0))
      {
         syslog(LOG_INFO,$message); 		//Log it to syslog
         return true;
      }
      else 
         return false;
   }

   //Wrapper around the log method. Log a message only if the specified level for this function
   //is less or equal than the current debugging level.
   public function debug($to_level,$msg)
   {
      if (is_int($to_level)&&is_string($msg))
      {
         //Perform sanity checks to see if both the current debugging level and the specified level are valid values 
         //according to MAX_LEVEL

         //Lower bound
         $to_level <= 0 ? $to_level=NULL : $to_level=$to_level;	
         $this->level <= 0 ? $this->level=NULL : $this->level=$this->level;

         //Upper bound
         $this->level > self::MAX_LEVEL ? $this->level=self::MAX_LEVEL : $this->level=$this->level;
         $to_level > self::MAX_LEVEL ? $to_level=self::MAX_LEVEL : $to_level=$to_level;

         //The specified level falls within our current debugging level?
         if ($this->level && ($to_level<=$this->level) && (strlen($msg)>0))
         {
            $mymsg="Debug$to_level: $msg";	//Include debugging level in the message
            $this->Log($mymsg);			//Log it
            return true;
         }
         else return false;
      }
      else return false;
   }

   public function setLevel($var=0)
   {
      if (is_int($var))
      {
         $this->level=$var;
         return true;
      }
      else
         return false;
   }

}
