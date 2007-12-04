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
$logger->setLogToStdOut(false);

db_connect();

# Check how many records we have already inserted today
$query = "SELECT count(*) FROM stats WHERE datetime >= DATE_SUB(NOW(), INTERVAL 23 HOUR);";
$logger->debug($query,3);
$today_records = v_sql_1_select($query);
# Do we have records from today?
if ($today_records == 0)
{
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
} //if ($today_records == 0)

#Get statistics for the whole month
$today = date('Y-m-d');
$query = "SELECT LAST_DAY(NOW());";
$logger->debug($query,3);
$mysql_last_day = v_sql_1_select($query);
if (!$mysql_last_day)
   $logger->logit(mysql_error(), LOG_ERROR);
if ($mysql_last_day && (strcmp($mysql_last_day,$today)==0))
{
   #Create variable holding monthly stats
   $days = explode('-',$today);
   $days = $days[2];
   $query = "SELECT * FROM stats WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL $days DAY);";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if (!$res)
   {
      $logger->logit(mysql_error(), LOG_ERROR);
   }
   while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
   {
      $month_stats[$row['code']]+=$row['value'];
   }
   $logger->debug(print_r($month_stats,true),3); 
   # Status values and description
   $query = "SELECT * FROM vstatus;";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if (!$res)
   {
      $logger->logit(mysql_error(), LOG_ERROR);
   }
   while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
   {
      $status['code'][] = $row['id'];
      $status['description'][] = $row['value'];
   }
   $logger->debug(print_r($status,true),3);
   # Health values and description
   $query = "SELECT * FROM health;";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if (!$res)
   {
      $logger->logit(mysql_error(), LOG_ERROR);
   }
   while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
   {
      $health['code'][] = $row['id'];
      $health['description'][] = $row['value'];
   }
   $logger->debug(print_r($health,true),3);
   #Subject of the email
   $subject = "Summary of connections during ".date('F Y');
   #And now build the texts to send
   foreach ($month_stats as $k => $v)
   {
      #Status messages
      if (strstr($k,'status'))
      {
         $code = explode('_',$k);
         $code = $code[1];
         $temp_key = array_search($code,$status['code']);
         $description = $status['description'][$temp_key];
         $messages['status'][] = "Number of systems with $description status: $v\n";
      }
      # Health messages
      else if (strstr($k,'health'))
      {
         $code = explode('_',$k);
         $code = $code[1];
         $temp_key = array_search($code,$health['code']);
         $description = $health['description'][$temp_key];
         $messages['health'][] = "Number of systems with health $description: $v\n";
      }
   }
   $average_ports = round( $month_stats['ports'] / $days);
   $average_switches = round ( $month_stats['switches'] / $days);
   $messages['ports'][] = "Average of ports used by day during this month: $average_ports\n";
   $messages['switches'][] = "Average of switches used by day during this month: $average_switches\n";
   $logger->debug(print_r($messages,true),3);   
   foreach($messages['status'] as $k => $v)
      $body.=$v;
   foreach($messages['health'] as $k => $v)
      $body.=$v;
   foreach($messages['ports'] as $k => $v)
      $body.=$v;
   foreach($messages['switches'] as $k => $v)
      $body.=$v;
   $body.="\n\nPlease consider forwarding these statistics to freenac@vptt.ch";
   #Now we finally have the statistics. Mail them
   $logger->mailit($subject,$body);
   #Display results on screen
   $logger->debug($subject);
   $logger->debug($body);
}
?>
