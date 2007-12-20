<?php
/**
 * Logger.php
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
* Define the Logger class which is a Singleton which provides for logging facilities.
*
* This class uses the constants defined by Syslog, which are listed as follows:
*   - LOG_EMERG   0       System is unusable
*   - LOG_ALERT   1       Action must be taken immediately
*   - LOG_CRIT    2       Critical conditions
*   - LOG_ERR   3       Error conditions
*   - LOG_WARNING 4       Warning conditions
*   - LOG_NOTICE  5       Normal, but significant condition
*   - LOG_INFO    6       Informational message
*   - LOG_DEBUG   7       Debug-level-message
*
* Example usage:
*   - $logger=Logger::getInstance();		Needed, before other lines
*   - $logger->setDebugLevel(3);		Log debug1,2,3 (default is only 1)
*   - $logger->logToStdErr();			If you don't want to use syslog
*   - $logger->logit("Hello world");		Will appear in syslog or stderr
*   - $logger->debug("Hello debug world");	Will be prefixed "debug1"
*   - $logger->debug("Hello debug world",3);	Will be prefixed "debug3"
*	
*/
final class Logger
{
   /**
   * Maximum debugging level
   */
   const MAX_DEBUG_LEVEL=3;				
   private $debug_level=NULL;			//Current debugging level
   private static $instance=NULL;		//Instance of this class
   private $identifier=NULL;
   private $facility=NULL;
   private $stderr=false;
   private $stdout=false;
   private $httpd_log=false;			//Log to webserver log?
   private $email_alert=false;			//Send Err or higher via email
   
   /**
   * Open logging facilities and start output buffering
   */
   private function __construct()  
   {
      define_syslog_variables();
      ob_start();
      $this->identifier=basename($_SERVER['SCRIPT_FILENAME']); #Get script's name as identifier
      $this->openFacility();   		#Open logging facilities
   }

   /**
   * Close logging facilities and flush output buffering
   */
   public function __destruct()		
   {
      ob_end_flush();
      closelog();
   }

   /**
   * Divert logging to Email
   * @param boolean $var        Activate or deactivate LogToEmail logging. Default is to activate ($var=true)
   * @return boolean		True if successful, false otherwise
   */
   public function setLogToEmail($var=true)
   {
      if (is_bool($var) && ($var===true))
      {
         $this->email_alert=true;
      }
      else
      {
         $this->email_alert=false;
      }
   }

   /**
   * Divert logging to httpd
   * @param boolean $var        Activate or deactivate httpd logging. Default is to activate ($var=true)
   */
   public function setLogToHttpd($var=true)
   {
      if (is_bool($var) && ($var===true))
      {
         $this->httpd_log=true;
      }
      else
      {
         $this->httpd_log=false;
      }
   }

   /**
   * Divert logging to StdErr
   * @param boolean $var	Activate or deactivate StdErr logging. Default is to activate ($var=true)
   */
   public function setLogToStdErr($var=true)	//Redirect logging to stderr
   {
      if (is_bool($var) && ($var===true))
      {
         closelog();		#Close syslog
         $this->stderr=true;
      }
      else
      {
         $this->stderr=false;
         $this->openFacility(); #Open logging facilities
      }
   }

   /**
   * Divert logging to StdOut
   * @param boolean $var        Activate or deactivate StdOut logging. Default is to activate ($var=true)
   */
   public function setLogToStdOut($var=true)    //Redirect logging to stderr
   {
      if (is_bool($var) && ($var===true))
      {
         closelog();            #Close syslog
         $this->stdout=true;
      }
      else
      {
         $this->stdout=false;
         $this->openFacility(); #Open logging facilities
      }
   }

   /**
   * Set the script name which will be displayed in syslog
   * @param mixed $name		Name to set to
   * @return boolean		True if successful, false otherwise
   */
   public function setIdentifier($name=NULL)	
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
  
   /**
   * The name displayed in syslog
   * @return mixed 		Name
   */
   public function getIdentifier()	
   {
      return $this->identifier;
   }

   /**
   * Get instance of this class
   * @return object	Current instance
   */
   public static function getInstance()
   {
      if (empty(self::$instance))               //Is there an instance of this class?
         self::$instance=new Logger();		//No, then create it
      return self::$instance;                   //Yes, return it
   }

   /**
   * Prevent clonning the instance
   * @throws	Exception indicating that copy can't be performed
   */
   public function __clone()                   
   {
      throw new Exception("Cannot clone the SysLogger object");
   }
 
   /**
   * Log a message to the HTTPD log
   * This method is a wrapper around the error_log function
   * @return boolean		True if successful, false otherwise
   */
   public function loghttpd($message='')
   {
      return error_log($message,0);
   }
   
   /**
   * Log a message. 
   * @param mixed $message		Message to log
   * @param integer $criticality 	How critical is this message? Default is informational
   * @return boolean			True if successful, false otherwise
   */
   public function logit($message='',$criticality=LOG_INFO)
   {
      define_syslog_variables();
      if (($criticality<0) || ($criticality > 7))	#Sanity check, defaults to LOG_INFO if user entered an invalid value
         $criticality=LOG_INFO;
      if ($criticality == LOG_ERR)
         $message="ERROR: $message";
      else if ($criticality == LOG_WARNING)
         $message="WARNING: $message";
      if (is_string($message)&&(strlen($message)>0))
      {
         if ($this->stderr)				#Should we log to stderr?
         {
            $message=trim($message);
            $message.="\n";
            fputs(STDERR,$message);
            ob_flush();
         }
         else if ($this->stdout)
         {
            $message=trim($message);
            $message.="\n";
            fputs(STDOUT,$message);
            ob_flush();
         }
         else
         {
            syslog((int)$criticality,$message); 		#Log it through syslog
            ob_flush();
         }
         if ($this->httpd_log)
         {
            $this->loghttpd($message);			#Should we log to Weblog?
         }
         if ($this->email_alert)
         {
            error_log($message,1,"root");		#TBD: Use a variable for email?
         }
         #if ($this->file_log)
         #{
         #   error_log($message,3,"/var/log/mylog");	#TBD: Use a ariable for mail?
         #}
         return true;
      }
      else
      { 
         return false;
      }
   }

   /**
   * Send an email to root
   * This is a wrapper around the php mail function
   * @return boolean			True is mail successfully sent, false otherwise
   */
   public function mailit($subject,$message,$to='root')
   {
      if (strlen($message) > 0)
      {
         return mail($to, $subject, $message);
      }
      else
      {
         return false;
      }
   }

   /**
   * Wrapper around the logit method. Log a message only if the specified level for this function
   * is less or equal than the current debugging level.
   * @param mixed $msg			Message to log
   * @param integer $to_level		Debug level where this message should be displayed. Default 1.
   * @return boolean			True if successful, false otherwise
   */
   public function debug($msg,$to_level=1)
   {
      if (is_int($to_level)&&is_string($msg))
      {
         #Perform sanity checks to see if both the current debugging level and the specified level are valid values 
         #according to MAX_DEBUG_LEVEL

         #Lower bound
         if ($to_level <= 0)
            $to_level=NULL;
         if ($this->debug_level <= 0)
            $this->debug_level=NULL;

         #Upper bound
         if ($this->debug_level > self::MAX_DEBUG_LEVEL)
            $this->debug_level=self::MAX_DEBUG_LEVEL;
         if ($to_level > self::MAX_DEBUG_LEVEL)
            $to_level=self::MAX_DEBUG_LEVEL;

         #The specified level falls within our current debugging level?
         if ($this->debug_level && ($to_level<=$this->debug_level) && (strlen($msg)>0))
         {
            $mymsg="Debug$to_level: $msg";	#Include debugging level in the message
            $this->logit($mymsg,LOG_DEBUG);	#Log it
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

   /**
   * Get the current debugging level.
   * @return integer		Current debug level
   */
   public function getDebugLevel()
   {
      return $this->debug_level;
   }

   /**
   * Set debugging level. It will cause to print all debugging messages less or equal than the value we specify in this function.
   * @param integer $var	Debug level. Default 1. 0 means no debugging
   * @return boolean		True if successful, false otherwise
   */
   public function setDebugLevel($var=1)
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

   /**
   * Open logging facility specified for the user
   * @param integer $facility	Facility to open. Default is LOG_DAEMON
   * @return boolean		True if successful, false otherwise
   */
   public function openFacility($facility=LOG_DAEMON)
   {
      if (is_integer($facility))
      {
         switch($facility)					#Sanity checks
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
      else
      {
         $this->logit("Value passed to openFacility is not an integer",LOG_WARNING);
         return false;
      }
   }
}
