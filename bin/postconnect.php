#!/usr/bin/php -f
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

/* include files */
require_once("../lib/exceptions.php");
require_once("./funcs.inc.php");
/* Open Syslog channel for logging */
$logger=Logger::getInstance();
#$logger->setDebugLevel(2);
#$logger->setLogToStdErr();

/* Load the policy file */
require_once "../etc/policy.inc.php";

/*$class_string = file_get_contents("../etc/policy.inc.php");
$class_string = preg_replace('/<\\?php/','',$class_string);
$class_string = preg_replace('/\\?>/','',$class_string);
$class_string = preg_replace('/\\$HOST/','$GLOBALS["HOST"]',$class_string);
$class_string = preg_replace('/\\$PORT/','$GLOBALS["PORT"]',$class_string);
//$class_string = preg_replace('/\\$REQUEST/','$GLOBALS["REQUEST"]',$class_string);
$class_string = preg_replace('/\\$RESULT/','$GLOBALS["RESULT"]',$class_string);
$class_string = preg_replace('/\\$CONF/','$GLOBALS["REQUEST"]',$class_string);
#echo $class_string;
eval($class_string);*/

// create policy object
$policy=new $conf->default_policy();

$in=STDIN;
$out=STDOUT;

$logger->logit("Started\n");

do 
{
   while ( ! feof($in) ) 
   {
      $line=rtrim(fgets($in,1024));
      if (strlen($line)<=0) 
         continue;
      $regs=array();
      if (ereg("(.*) TEST: .*(ALLOW|DENY): (.*) -> (.*), switch (.*) port (.*)<<", $line, $regs))
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
            $result=new VMPSResult($mac,$switch,$port,$success,$vlan);
            if ($conf->default_policy)
            {
               #$policy=new $conf->default_policy();
               try
               {
                  $GLOBALS["RESULT"] = $result;
                  $GLOBALS["PORT"]   = $result->getPort();
                  $GLOBALS["HOST"] = $result->getEndDevice();
                  $GLOBALS["CONF"] = Settings::getInstance();

	          #Passing of information between objects
	          $HOST->setPortID($PORT->getPortID());
		  $HOST->setOfficeID($PORT->getOfficeID());
	          $HOST->setVlanID($PORT->getLastVlanID());
	          $HOST->setPatchInfo($PORT->getPatchInfo());
	          $HOST->setSwitchInfo($PORT->getSwitchInfo());
	          $HOST->setPortInfo($PORT->getPortInfo());
	          
	          #Call our policy
                  $policy->postconnect();
               }
	       catch (Exception $e)
	       {
                  echo "Exception caught inner catch\n";
               }
            }
         }
         catch (Exception $e)
         {
            echo "Exception caught outer catch\n";
         }
      }
   }
}while (!$conf->lastseen_dryrun);
   
    


?>
