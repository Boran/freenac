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
 * *    public logit($message,$criticality):
 *              Log message to syslog
 *
 *		Parameters:
 *		$message	Message to log
 *		$criticality	How critical this message is
 *
 * *	public debug($to_level,$message):	
 *		Wrapper around the log method. Log a message only if the specified level for this function
 *		is less or equal than the current debugging level.		
 *		
 *		Parameters:
 *		$to_level	Debugging level we want to output our message to
 *		$message	Message to log
 *
 * *	public setDebugLevel($level):
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

final class Logger
{
   #This class uses the constants used by Syslog, which are listed as follows:
   #LOG_EMERG	0	System is unusable
   #LOG_ALERT	1	Action must be taken immediately
   #LOG_CRIT	2	Critical conditions
   #LOG_ERROR	3	Error conditions
   #LOG_WARNING	4	Warning conditions
   #LOG_NOTICE	5	Normal, but significant condition
   #LOG_INFO	6	Informational message
   #LOG_DEBUG	7	Debug-level-message
   

   const MAX_DEBUG_LEVEL=3;				//Maximum debugging level
   private $debug_level=NULL;			//Current debugging level
   private static $instance=NULL;		//Instance of this class
   private $identifier=NULL;
   private $facility=NULL;
   private $stderr=false;
   
   private function __construct()  
   {
      define_syslog_variables();
      ob_start();
      $this->identifier=basename($_SERVER['SCRIPT_FILENAME'],'.php');
      $this->openFacility();   
   }

   private function __destruct()
   {
      ob_end_flush();
      closelog();
   }

   public function logToStdErr($var=true)	//Redirect logging to stderr
   {
      if ($var)
      {
         closelog();
         $this->stderr=$var;
      }
      else
      {
         $this->openFacility();
      }
   }
 
   public function setIdentifier($name=NULL)	//The name which will be displayed in syslog
   {
      if ($name != NULL)
      {   
         closelog();
         $this->identifier=$name;
         $this->openFacility();
         return true;
      }
      else 
      {
         return false;
      }
   }
  
   public function getIdentifier()		//The name displayed in syslog
   {
      return $this->identifier;
   }

   public static function getInstance()
   {
      if (empty(self::$instance))               //Is there an instance of this class?
         self::$instance=new Logger();		//No, then create it
      return self::$instance;                   //Yes, return it
   }

   public function __clone()                    //Prevent clonning the instance
   {
      throw new Exception("Cannot clone the SysLogger object");
   }

   public function logit($message='',$criticality=LOG_INFO)
   {
      if (($criticality<0) || ($criticality > 7))	//Sanity check, defaults to LOG_INFO if user entered an invalid value
         $criticality=LOG_INFO;
      if (is_string($message)&&(strlen($message)>0))
      {
         if ($this->stderr)			//Should we log to stderr?
         {
            fputs(STDERR,$message);
            ob_flush();
            return true;
         }
         else
         {
            syslog($criticality,$message); 		//Log it to syslog
            ob_flush();
            return true;
         }
      }
      else
      { 
         return false;
      }
   }

   //Wrapper around the log method. Log a message only if the specified level for this function
   //is less or equal than the current debugging level.
   public function debug($msg,$to_level=1)
   {
      if (is_int($to_level)&&is_string($msg))
      {
         //Perform sanity checks to see if both the current debugging level and the specified level are valid values 
         //according to MAX_DEBUG_LEVEL

         //Lower bound
         if ($to_level <= 0)
            $to_level=NULL;
         if ($this->debug_level <= 0)
            $this->debug_level=NULL;

         //Upper bound
         if ($this->debug_level > self::MAX_DEBUG_LEVEL)
            $this->debug_level=self::MAX_DEBUG_LEVEL;
         if ($to_level > self::MAX_DEBUG_LEVEL)
            $to_level=self::MAX_DEBUG_LEVEL;

         //The specified level falls within our current debugging level?
         if ($this->debug_level && ($to_level<=$this->debug_level) && (strlen($msg)>0))
         {
            $mymsg="Debug$to_level: $msg";	//Include debugging level in the message
            $this->logit($mymsg,LOG_DEBUG);			//Log it
            return true;
         }
         else 
         {
            return false;
         }
      }
      else 
      {
         return false;
      }
   }

   public function setDebugLevel($var=1)	//Set debugging level, 0 means no debugging
   {
      if (is_int($var))
      {
         $this->debug_level=$var;
         return true;
      }
      else
      {
         $this->logit("Value passed to setDebugLevel is not an integer",LOG_WARNING);
         return false;
      }
   }

   public function openFacility($facility=LOG_DAEMON)	//Open logging facility specified for the user
   {
      switch($facility)					//Sanity checks
      {
         case LOG_AUTH:
         case LOG_AUTHPRIV:
         case LOG_CRON:
         case LOG_DAEMON:
         case LOG_KERN:
         case LOG_LOCAL0:
         case LOG_LOCAL1:
         case LOG_LOCAL2:
         case LOG_LOCAL3:
         case LOG_LOCAL4:
         case LOG_LOCAL5:
         case LOG_LOCAL6:
         case LOG_LOCAL7:
         case LOG_LPR:
         case LOG_MAIL:
         case LOG_NEWS:
         case LOG_SYSLOG:
         case LOG_USER:
         case LOG_UUCP:
            {
               closelog();
               $this->facility=$facility;
               return openlog($this->identifier,LOG_CONS | LOG_NDELAY | LOG_PID, $this->facility);
            }
         default:
            return false;
      }
   }
}
