#!/usr/bin/php 
<?php
/**
 * /opt/nac/bin/cron_program_port.php
 *
 * Long description for file:
 * Go through the port table and check for the program flag, and
 * program the ports via SNMP 
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright           	2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                                http://www.freenac.net
 *
 */

require_once 'funcs.inc.php';
require_once 'snmp_defs.inc.php';

$logger->setDebugLevel(0);
$logger->setLogToStdOut(false);

/**
* Restart daemons on master server
*/
function restart_daemons()
{
   global $conf, $logger;
   if ($conf->restart_daemons)
   {
      # Reset flag
      $query="UPDATE config SET value='false',who='', LastChange=NOW() WHERE name='restart_daemons';";
      $logger->debug($query,3);
      $result = mysql_query($query);
      if ( ! $result)
      {
         $logger->logit(mysql_error(), LOG_ERR);
      }
      # Restart them
      popen('/etc/init.d/vmps restart 2>&1','r');
      popen('/etc/init.d/postconnect restart 2>&1','r');
      # Write to the db log
      log2db('info','Daemons have been restarted');
      # Reload settings?
      $conf=Settings::getInstance();   
   }
}

/**
* Delete the pid file
* This function is also called the following signals have been caught:
* SIGTERM, SIGHUP, SIGINT
*/
function delete_pid_file()
{
   global $file_name;
   unlink($file_name);
}

## ------------------------------------------- Main stuff ---------------------------------------

$file_name='cron_restart_port.pid';
#Check for PID file
if (is_readable($file_name))
{
   $pid = file_get_contents($file_name);
   $processes = syscall("ps uax | grep $pid | awk '{print $2}'");
   if ( ! $processes )
   {
      $logger->logit("An error ocurred when calling syscall.", LOG_ERR);
      exit(1);
   }
   $processes = explode("\n",$processes);
   if (array_search($pid, $processes) === false )
   {
      delete_pid_file();
   }
   else
   {
      $logger->logit("A previous instance of cron_restart_port.php is still running.", LOG_ERR);
      exit(1);
   }
}
#Create PID file
$file=fopen($file_name,'w');
if ( ! $file )
{
   $logger->logit("Can't write PID file", LOG_ERR);
   exit(1);
} 
$pid = posix_getpid();
fprintf($file,'%d',$pid);
fclose($file);

#Handle signals
pcntl_signal(SIGTERM, "delete_pid_file");
pcntl_signal(SIGHUP, "delete_pid_file");
pcntl_signal(SIGINT, "delete_pid_file");

#Should we restart the daemons?
if ($conf->restart_daemons)
   restart_daemons();

 
if ( $conf->clear_mac_enable )
{
   $query=<<<EOF
SELECT sys.mac, sw.ip, sw.name FROM systems sys 
   INNER JOIN port p ON sys.LastPort=p.id 
   INNER JOIN switch sw ON p.switch=sw.id 
   WHERE sys.clear_mac='1' AND sw.switch_type='1'
   AND sys.lastseen>=DATE_SUB(NOW(),INTERVAL 3 HOUR);   
EOF;
   $logger->debug($query, 3);
   $res = mysql_query($query);
   if ( ! $res )
   {
      $logger->logit(mysql_error(), LOG_ERR);
   }
   else
   {
      while ( $row = mysql_fetch_array($res, MYSQL_ASSOC) )
      {
         if ( clear_mac($row['mac'], $row['ip']) )
         {
            $logger->logit("MAC address {$row['mac']} has been deleted from switch {$row['name']}({$row['ip']}) CAM table");
         }
         else
         {
            $logger->logit("Couldn't delete MAC {$row['mac']} from switch {$row['name']}({$row['ip']})");
         }
      }
      $query = "UPDATE systems SET clear_mac='0';";
      $logger->debug($query, 3);
      $res = mysql_query($query);
      if ( ! $res )
      {
         $logger->logit(mysql_error(), LOG_ERR);      
      }
   }
}

# Continue with the normal flow
$query=<<<EOF
SELECT p.id, 
   p.name AS port, 
   s.ip AS switch, 
   s.name AS switch_name,
   p.auth_profile, 
   p.shutdown,
   p.restart_now,
   v.default_name AS vlan 
   FROM port p 
   LEFT JOIN vlan v
   ON p.staticvlan=v.id
   INNER JOIN switch s 
   ON p.switch=s.id 
   WHERE p.restart_now='1'
   AND (p.last_auth_profile='1' OR p.last_auth_profile='2') 
   ORDER BY s.ip ASC;
EOF;
$logger->debug($query, 3);
$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error());
   #Delete PID file
   delete_pid_file();
   exit(1);
}

while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
{
   foreach ($row as $k => $v)
      $switch_ports[$row['switch']][$k][]=$v;
}

#No ports have restart_now=1;
if ( ! isset($switch_ports) )
{
   #Delete PID file
   delete_pid_file();
   exit();
}

foreach ($switch_ports as $switch => $properties)
{
   #Retrieve the list of ports from the switch. If we don't get it, go to the next switch
   if ( ! $ports_on_switch =  ports_on_switch($switch))
      continue;
   #Retrieve vlan membership type for the switch ports
   if ( ! $vm_type = vm_type($switch))
      continue;

   for ($i=0; $i<count($properties['port']); $i++)
   {
      $port = $properties['port'][$i];
      $dont_restart=0;
      #If we have an empty port name, go to the next one
      if (! $port)
         continue;
      #Get the index for this port
      $port_index = get_snmp_index($port, $ports_on_switch);
      if (! $port_index)
         continue;
 
      ## Check if it is not a trunk port
      if ($properties['auth_profile'][$i] == '3')
      {
         $logger->logit("Port $port on switch $switch({$properties['switch_name'][$i]}) is a trunk port and cannot be programmed");
         continue;
      }
      ## Program port as static or dynamic
      else if (($properties['auth_profile'][$i] == '1') && ($properties['vlan'][$i]))
      {
         set_port_as_static($switch, $port, $properties['vlan'][$i], $port_index);
         $dont_restart++;
      }
      else if ($properties['auth_profile'][$i] == '2')
      {
         #Check if the port is static. If it is, program it, otherwise, don't do anything (CatOS issues)?
         # Look for a SNMP OID key [OID.x.y.1]=value that ends in .1 and get the value after it
         if (array_find_key($port_index, $vm_type, '.', 1) == '1')
         {
            set_port_as_dynamic($switch, $port, $port_index);
            $dont_restart++;
         }
         else {
           $logger->debug('Port is already dynamic, do not reprogram', 2);
         }
      }

      # Shut down the port
      if ($properties['shutdown'][$i])
      {
         #Try to turn it off
         if (turn_off_port($switch, $port, $port_index))
         {
            $string="Port $port on switch $switch({$properties['switch_name'][$i]}) was successfully shutdown";
            $dont_restart++;
         }
         else
         {
            $string="Port $port on switch $switch({$properties['switch_name'][$i]}) could not be shutdown";
         }
         $logger->logit($string);
         log2db('info', $string);
      }

      #Restart port
      if ( ! $dont_restart)
      {
         if (turn_off_port($switch, $port, $port_index))
         {
            turn_off_port($switch, $port, $port_index); 	#CatOS issues?
            if (turn_on_port($switch, $port, $port_index))
            {
               $string="Port $port successfully restarted on switch $switch({$properties['switch_name'][$i]})";
            }
            else
            {
               $string="Port $port on switch $switch({$properties['switch_name'][$i]}) couldn't be restarted";
            }
         }
         else
         {
            $string="Port $port on switch $switch({$properties['switch_name'][$i]}) couldn't be restarted";
         }
         $logger->logit($string);
         log2db('info',$string);
      }
   
   }
}

if ( mysql_num_rows($res) )
{
   # Ok, we are done, reset the restart_now flag, for ALL ports
   $query = "UPDATE port SET restart_now=0;";
   $logger->debug($query, 3);
   $result = mysql_query($query);
   if ( ! $result)
   {
      $logger->logit(mysql_error(),LOG_ERR);
   }
}
#Delete PID file
delete_pid_file($file_name);
?>
