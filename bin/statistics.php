#!/usr/bin/php
<?
/**
 * /opt/nac/bin/statistics
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
 * @link                        http://www.freenac.net
 *
 */
require_once 'funcs.inc.php';

$logger->setDebugLevel(0);
$logger->setLogToStdOut();

db_connect();
#Get number of active systems within last 24 hours
$query="select id, health, status from systems where date_sub(CURDATE(),interval 1 day) <= LastSeen;";
$logger->debug($query,3);
$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error(),LOG_ERROR);
   exit(1);
}
$num_systems=mysql_num_rows($res);
$systems=array();

while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   if (!$row['health'])
      $row['health']=0;
   $systems['health'][$row['health']]++;
   $systems['status'][$row['status']]++;
}
$logger->debug(print_r($systems,true),3);

#Number of active ports within last 24 hours
$query="select p.id, p.switch, s.ip from port p inner join switch s on p.switch=s.id where date_sub(CURDATE(),interval 1 day) <= p.last_activity;";
$logger->debug($query,3);
$res=mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error(),LOG_ERROR);
   exit(1);
}
$num_ports=mysql_num_rows($res);
$switches=array();
while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   $switches[$row['ip']]++;
}
$num_switches=count($switches);
$logger->debug(print_r($switches,true),3);

#Display results on screen
$logger->debug("Active systems within last 24 hours: $num_systems");
$logger->debug("Ports used: $num_ports in $num_switches switches");

#Store results per health
foreach ($systems['health'] as $k => $v)
{
   $query="insert into stats set code='health_$k', value='$v', datetime=NOW();";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if (!$res)
   {
      $logger->logit(mysql_error(),LOG_ERROR);
   } 
}

#Store results per status
foreach ($systems['status'] as $k => $v)
{
   $query="insert into stats set code='status_$k', value='$v', datetime=NOW();";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if (!$res)
   {
      $logger->logit(mysql_error(),LOG_ERROR);
   }
}

#How many ports were used?
$query="insert into stats set code='ports', value='$num_ports', datetime=NOW();";
$logger->debug($query,3);
$res = mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error(),LOG_ERROR);
}

#How many switches were used?
$query="insert into stats set code='switches', value='$num_switches', datetime=NOW();";
$logger->debug($query,3);
$res = mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error(),LOG_ERROR);
}

?>
