#!/usr/bin/php
<?
/**
 * /opt/nac/bin/postconnect.php
 *
 * Long description for file:
 * FUNCTION:
 * - Update the "last seen" entry for a specific MAC address.
 * - If the system is new, insert new Users, Ports, Switches, System as appropriate
 * - and send an email alert.
 * - Automatically recognise and allow GWPs.
 *  This function is called for any errors or
 *  messages sent to stdout/err. The idea is to catch all
 *  such messages and send them to syslog, this this is a daemon normally
 *  detached from the console
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Sean Boran (FreeNAC Core Team)
 * @copyright           	2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");

/**
* Load exceptions
*/
require_once("../lib/exceptions.php");

/* include files */
require_once("./funcs.inc.php");
/* Open Syslog channel for logging */
$logger=Logger::getInstance();
$logger->setDebugLevel(0);
$logger->setLogToStdErr(false);

$policy_file='../etc/policy.inc.php';
/**
* Load the policy file
*/
require_once "$policy_file";

$file_read=readlink($policy_file);

// create policy object
$policy=new $conf->default_policy();

$in=STDIN;
$out=STDOUT;

$logger->logit("Started. Policy loaded from file $file_read");
log2db('info',"postconnect started. Policy loaded from file $file_read");

while ( ! feof($in) ) 
{
   $line=rtrim(fgets($in,1024));
   if (strlen($line)<=0) 
      continue;
   $regs=array();
   if (ereg("(.*) vmpsd: .*(ALLOW|DENY): (.*) -> (.*), switch (.*) port (.*)<<", $line, $regs))
   {
      $success=trim($regs[2]);
      $mac=trim($regs[3]);
      $vlan=trim($regs[4]);
      $switch=trim($regs[5]);
      $port=trim($regs[6]);
      $details="$regs[1]";
      
      #Maybe there is no vlan because answer was a DENY, in such case, set to '--NONE--'
      if (!$vlan)
         $vlan='--NONE--';

      #If there are empty parameters, go to next request
      if (empty($switch) || empty($port) || empty($success) || empty($vlan) || empty($mac))
         continue;
      $mac="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";
      try 
      {
         $result=new SyslogRequest($mac,$switch,$port,$success,$vlan);
         if ($conf->default_policy)
         {
            #Call our policy
            $policy->postconnect($result);
         }
      }
      catch (Exception $e)
      {
         $logger->logit("Postconnect exception",LOG_WARNING);
      }
   }
}

$logger->logit("Stopped");

?>
