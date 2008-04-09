#!/usr/bin/php
<?php
/**
 * /opt/nac/bin/vmpsd_external
 *
 * Long description for file:
 * FUNCTION:
 *   "external" program called by the vmps daemon "vmpsd". This program
 *   decides what to do, in real time, when access is requested by  a
 *   Switch for a MAC address. Since its is real time perfomance is important,
 *   so some jobs such as document what was last seen, where, or recognising 
 *   Infnet PCs, is done in the vmps_lastseen script, which is not real time.
 *     o If the MAC is active in the DB authorise it.
 *     o If the mac is active on a port where another system has been
 *       active withein the last hour, try to use the vlan last seen on the 
 *       port, nut the vlan assigned to this system. This is to detect hubs and
 *       prevent .flapping.. 
 *       This feature is only allowed if the vlan on the port and assigned to 
 *       the MAC 
 *       are in the same vlan group (otherwise the new MAC is denied)
 *     o If the MAC is unknown, check to see if a default vlan has been 
 *       configured for 
 *       that port and use it, otherwise use the default vlan.
 *     o Log decisions to syslog, and key events to DB (visible in the GUI).
 * 
 *   program input:
 *         <domain> <switch ip> <port> <lastvlan> <mac address>
 *   program output
 *         ALLOW <vlan name>
 *         DENY
 *         SHUTDOWN
 *         DOMAIN
 * 
 *   Important: this script writes to stdout and is captured by vmpsd.
 *              So send debugging output to syslog, not stdout. Or just start
 *              directly from the commandline to check for classical
 *              PHP syntax problems.
 *              Do not log to the DB either (with log2db()), because this
 *              program can also run on a secondary and should NOT write to any
 *              other tables than vmpsauth, which is not replicated.
 *
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package		FreeNAC
 * @author		Sean Boran (FreeNAC Core Team)
 * @author		Hector Ortiz (FreeNAC Core Team)
 * @author		Thomas Seiler (Contributer)
 * @copyright		2006 FreeNAC
 * @license		http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version		SVN: $Id$
 * @link		http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");

/**
* Load exceptions
*/
require_once("../lib/exceptions.php");

/**
* Load settings and common functions
*/
require_once("./funcs.inc.php");

# Open Syslog channel for logging 
$logger=Logger::getInstance();
$logger->setDebugLevel(0);
$logger->setLogToStdErr(false);

$policy_file='../etc/policy.inc.php';
/**
* Load the policy file 
*/
require_once "$policy_file";

$file_read=readlink($policy_file);

# Create policy object
if ($conf->default_policy)
   $policy=new $conf->default_policy();
else
{
   $logger->logit("A default policy hasn't been defined in the config table", LOG_ERR);
   exit(1);
}

# Open stdin and stdout - These connect us to vmpsd 
$in = STDIN;
$out = STDOUT;

#Variables used to trace where an exception was thrown
#Helpful if we change our decision in catch_ALLOW method
$temp_vlan=0;
$trace=NULL;
$message=NULL;

#Display a message if everything is fine
$logger->logit("Started. Policy loaded from file $file_read");

# Loop Forever (we are a daemon) 
while ($in && $out) 
{
   # Read one line from vmpsd and parse it 
   $line=rtrim( fgets($in, 1024) );
   # Clean tracing variables
   $temp_vlan=0;
   $trace=NULL;
   $message=NULL;
   # If there are some characters 
   if (strlen($line) > 0) 
   {
      # Log Request Start and Input */
      $logger->debug("----------------------------\n");
      $logger->debug("$line\n");
      # split by space       	
      $splitted = explode(" ", $line);

      try 
      {
         #If some parameter in the request is missing, DENY
         if (empty($splitted[0]) || empty($splitted[1]) || empty($splitted[2])
            || empty($splitted[3]) || empty($splitted[4]))
         {
            $logger->logit("Invalid request\n");
            DENY('Invalid request');
         }

         # sanity checks, 5 values 
         if (count($splitted) != 5 || ((strlen($splitted[4]) < 12) || (strlen($splitted[4]) > 17))) 
         {
            $logger->logit("Invalid request\n");
            DENY('Invalid request');
         }

         #extract values 
         list($domain, $switch, $port, $lastvlan, $mac)=$splitted;

         # Create request 
         $request=new VMPSRequest($mac,$switch,$port,$domain,$lastvlan);
         # Call Default policy 
         if ($conf->default_policy)
         {
            try
            {
               $policy->preconnect($request);
            }
            catch(Exception $e)
            {
               #Store current status of thrown Allow exception
               if ($e instanceof AllowException)
               {
                  $temp_vlan=$e->getDecidedVlan();
               }
               $trace=$e->GetTrace();
               $message=$e->getMessage();
               # Do we have the catch_ALLOW method?
               #This method should be used to change our decision
               if (method_exists($policy,'catch_ALLOW'))
               {
                  #If we have a DENY, rethrow the exception
                  if ($e instanceof DenyException)
                     DENY($e->getMessage());
                  else
                     #This is an allow, rethink our decision 
                     $policy->catch_ALLOW($e->getDecidedVlan());
               }
               else 
               {
                  #Rethrow our decision
                  if ($e instanceof DenyException)
                     DENY($e->getMessage());
                  else 
                     ALLOW($e->getDecidedVlan());
               }
            }
         }
  
         # This is the default action 
         DENY('Default action');
      }
      catch (DenyException $e)
      {
         fputs($out, "DENY\n");
         reportException($e);
      }
      catch (AllowException $e) 
      {
         fputs($out, "ALLOW ".$e->getDecidedVlan()."\n");
         reportException($e);
      }
      catch (Exception $e) 
      {
         fputs($out, "DENY\n");
         reportException($e);
      }
 
      $logger->debug("----------------------------\n");
   }                # strlen >0
}


exit(0);
# End of Main -----------------------

/**
* Report where an exception was thrown
*/
function reportException(Exception $e) 
{
   global $logger, $temp_vlan, $trace, $message;
   #Get the proper values to report if a Deny exception was thrown
   if ($e instanceof DenyException)
   {
      if ($trace && $message)
      {
         $t = $trace;
         $msg=$message;
      }
      else
      {
         $t = $e->GetTrace();
         $msg = $e->getMessage();
      }
   }
   #Get the proper values to report if an Allow exception was thrown
   else if ($e instanceof AllowException)
   {
      if ($e->GetDecidedVlan() === $temp_vlan)
      {
         $t = $trace;
         $msg = $message;
      }
      else
      {
         $t = $e->GetTrace();
         $msg = $e->getMessage();
      }
   }
   #And report it as debug level 1
   $logger->debug($msg ." (at ".basename($t[0]['file']).":". $t[0]['line'].")\n");
}
$logger->logit("Stopped");
?>
