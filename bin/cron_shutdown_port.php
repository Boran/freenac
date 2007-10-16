#!/usr/bin/php -f
<?
/**
 * /opt/nac/bin/cron_shutdown_port.php
 *
 * Long description for file:
 * Go through the port table and check for the shutdown flag, and
 * shutdown those ports via SNMP
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
#$logger->setLogToStdOut();

#Get list of ports to shutdown
$query=<<<EOF
SELECT p.id,
   p.name as port,
   s.ip as switch 
   FROM port p
   INNER JOIN switch s
   ON p.switch=s.id
   WHERE p.set_shutdown=1;
EOF;
$logger->debug($query);
$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error());
   exit(1);
}

#Go through the list
while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   #Check if we have a port name and a switch ip
   if ($row['port'] && $row['switch'])
   {
      #Get its snmp_port_index
      $port_index=get_snmp_port_index($row['port'],$row['switch']);
      $switch = $row['switch'];
      $port = $row['port'];
      #And try to turn it off
      if (turn_off_port($port_index))
      {
         $string="Port $port on switch $switch was successfully shutdown";
         $query = "update port set set_shutdown='0' where id = '{$row['id']}';";
         $logger->logit($query);
         mysql_query($query);
      }
      else
      {
         $string="Port $port on switch $switch couldn't be shutdown";
      }
      $logger->logit($string);
      log2db('info', $string);
   }
   # Go to next row
   else
      continue;
}

exit(0);
?>
