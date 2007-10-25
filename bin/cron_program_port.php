#!/usr/bin/php -f
<?
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

$logger->setDebugLevel(0);
$logger->setLogToStdOut();

$query=<<<EOF
SELECT p.id, 
   p.name AS port, 
   s.ip AS switch, 
   p.auth_profile, 
   p.shutdown,
   p.restart_now,
   v.default_name AS vlan 
   FROM port p 
   LEFT JOIN vlan v
   ON p.staticvlan=v.id
   INNER JOIN switch s 
   ON p.switch=s.id 
   WHERE p.restart_now=1;
EOF;
$logger->debug($query, 3);
$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error());
   exit(1);
}

while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   # Check if we have a port name and a switch ip
   $dont_restart=0;
   if ($row['port'] && $row['switch'])
   {
      #Get its snmp_port_index
      $port_index=get_snmp_port_index($row['switch'],$row['port']);

      ## Program ports as static or dynamic
      if (($row['auth_profile']=='1') && ($row['vlan']))
      {
         set_port_as_static($row['switch'], $row['port'], $row['vlan'], $port_index);
         $dont_restart++;
      }
      else if ($row['auth_profile']=='2')
      {
         set_port_as_dynamic($row['switch'], $row['port'], $port_index);
         $dont_restart++;
      }

      # Shut down the port
      if ($row['shutdown'])
      {
         #Try to turn it off
         if (turn_off_port($row['switch'], $row['port'], $port_index))
         {
            $string="Port {$row['port']} on switch {$row['switch']} was successfully shutdown";
            $dont_restart++;
         }
         else
         {
            $string="Port {$row['port']} on switch {$row['switch']} could not be shutdown";
         }
         $logger->logit($string);
         log2db('info', $string);
      }

      #Restart port
      if ( ! $dont_restart)
      {
         if (turn_off_port($row['switch'], $row['port'], $port_index))
         {
            if (turn_on_port($row['switch'], $row['port'], $port_index))
            {
               $logger->logit("Port successfully restarted {$row['port']} on switch {$row['switch']}");
               log2db('info',"Port successfully restarted {$row['port']} on switch {$row['switch']}");
            }
            else
            {
               $logger->logit("Port {$row['port']} on switch {$row['switch']} couldn't be restarted");
               log2db('info',"Port {$row['port']} on switch {$row['switch']} couldn't be restarted");
            }
         }
         else
         {
            $logger->logit("Port {$row['port']} on switch {$row['switch']} couldn't be restarted");
            log2db('info',"Port {$row['port']} on switch {$row['switch']} couldn't be restarted");
         }
      }
   }
   else
   {
      continue;
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
      $logger->logit(mysql_error(),LOG_ERROR);
   }
}

?>
