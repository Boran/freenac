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
 * @author                      Sean Boran (FreeNAC Core Team)
 * @copyright           2006 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                                http://www.freenac.net
 *
 */

require_once 'funcs.inc.php';

$logger->setDebugLevel(0);
#$logger->setLogToStdOut();

$query=<<<EOF
SELECT p.name AS port, 
   s.ip AS switch, 
   p.auth_profile, 
   v.default_name AS vlan 
   FROM port p 
   INNER JOIN switch s 
   ON p.switch=s.id 
   INNER JOIN vlan v 
   ON p.set_staticvlan=v.id
   WHERE p.auth_profile=1;
EOF;
$logger->debug($query);

$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error());
   exit(1);
}

while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   if ($row['auth_profile']=='1') && ($row['vlan'])
   {
      #Program port as static
      $command="./snmp_set_port.php {$row['switch']} {$row['port']} -s {$row['vlan']}";
      $logger->logit($command);
      syscall($command);
      
   }
   else if ($row['auth_profile']=='2')
   {
      #Program port as dynamic
      $command="./snmp_set_port.php {$row['switch']} {$row['port']} -d";
      $logger->logit($command);
      syscall($command);
   }
   else
   {
      continue;
   }
}
?>
