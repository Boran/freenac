#!/usr/bin/php  
<?
/**
 * bin/ping_switch.php
 *
 * Long description for file:
 *
 * This script pings a switch port to know if it is up or down.
 *
 * TESTED:
 *      Catalyst 2940 (IOS), 3560 (IOS), 2948 (CatOS), 2960G (IOS)
 *
 * USAGE :
 *      bin/ping_switch.php
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


chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "bin/funcs.inc.php";               # Load settings & common functions
require_once "bin/snmp_defs.inc.php";

$logger->setDebugLevel(0);       // 0=errors only, 1=medium, 3=queries
$logger->setLogToStdOut(true);

//
//------------------------------------------ Functions ------------------------------------------------
//

function print_usage($code)
{
   global $logger;
   $usage=<<<EOF
USAGE: ping_switch.php switch1 switch2 ... [OPTIONS]

        Web:      http://www.freenac.net/
        Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Ping switch ports to know if they are up or down.

OPTIONS:
        -h              Display this help screen
	-s		Supress messages to standard output and redirect them to syslog
	-d		Activate debugging

EOF;
   $logger->logit( $usage);
   exit($code);
}

if ($argc!=1)
   $options=getopt("hsd");
if ($options)
{
   if (array_key_exists('h',$options))
      print_usage(0);
   if (array_key_exists('s',$options))
      $logger->setLogToStdOut(false);
   if (array_key_exists('d',$options))
      $logger->setDebugLevel(3);
}

//
//Take parameters off the command line
//

$j=0;
for ($i=0;$i<$argc;$i++)
{
   switch($argv[$i])
   {
      case '-h':
      case '-s':
      case '-d':
         break;
      default:
         $command_line[$j]=$argv[$i];
         $j++;
         break;
   }
}

// allow performance measurements
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
//
//---------------------------------------  Main stuff ----------------------------------------------------------
//
db_connect();

#Look up the switches in the database
if ($j == 1) 
   $query = "SELECT id, ip, name, hw, sw FROM switch WHERE scan='1'";
else
{
   $query = "SELECT id, ip, name, hw, sw FROM switch WHERE scan='1' AND";
   for ($i=1; $i<$j; $i++)
   if ($i==1)
      $query.=" ip='{$command_line[$i]}' OR name='{$command_line[$i]}'";
   else
      $query.=" OR ip='{$command_line[$i]}' OR name='{$command_line[$i]}'";
}
$logger->debug($query,2);
$res = mysql_query($query);
if (!$res)
{
   $logger->logit(mysql_error(),LOG_ERROR);
   exit(1);
}

while ($row = mysql_fetch_array($res,MYSQL_ASSOC))
{
   $switch_ip = $row['ip'];
   $switch_id = $row['id'];
   $logger->logit("Querying switch {$row['name']}, $switch_ip, {$row['hw']}, {$row['sw']} for port status");
   
   #Query switch for list of ports and their status
   $status_of_ports = @snmprealwalk($switch_ip, $snmp_rw, $snmp_port['ad_status']);
   $ports_on_switch = @snmprealwalk($switch_ip, $snmp_rw, $snmp_if['name']);
   if ( !$ports_on_switch || !$status_of_ports )
   {
      $logger->logit("Could not communicate with switch $switch_ip");
      # Update switch's last_monitored: set=2, meaning "down" and note when we polled
      $query = "UPDATE switch set up=2, last_monitored=NOW() where id='$switch_id';";
      $logger->debug($query,2);
      $final = mysql_query($query);
      if (! $final)
      {
         $logger->logit(mysql_error(), LOG_ERROR);
      }
      continue;
   }
   #Clean values
   $ports_on_switch = array_map("remove_type", $ports_on_switch);
   $status_of_ports = array_map("remove_type", $status_of_ports);

   #Query list from ports from the database
   $query = "SELECT id, name FROM port WHERE switch='$switch_id'";
   $logger->debug($query,2);
   $result = mysql_query($query);
   if (! $result)
   {
      $logger->logit(mysql_error(),LOG_ERROR);
      continue;
   }
   $ports_up = 0; 
   while ($port_row = mysql_fetch_array($result, MYSQL_ASSOC))
   {
      $port_id = $port_row['id'];
      $port_name = $port_row['name'];
      # Get only the SNMP port index
      # Look for the port name in $ports_on_switch and return its key, and strip the '.' from the beggining of the line
      $port_index = ltrim( str_get_last( array_isearch($port_name,$ports_on_switch), '.', 1), '.');
      if (empty($port_index))
         continue;
      
      # Get port status
      $port_status = array_find_key($port_index, $status_of_ports, '.', 1);
      if (strpos($port_status,'1'))
         $status=1;
      else if (strpos($port_status,'2'))
         $status=2;
      else if (strpos($port_status,'3'))
         $status=3;

      # Update port's info in the DB
      $query = "UPDATE port SET up='$status', last_monitored=NOW() WHERE id='$port_id';";
      $logger->debug($query,2);
      $final = mysql_query($query);
      if (!$final)
      {
         $logger->logit(mysql_error(), LOG_ERROR);
      }
      $ports_up++;
   }
   # Update switch's last_monitored
   if ($ports_up)
      $query = "UPDATE switch set up=1, last_monitored=NOW() where id='$switch_id';";
   else
      $query = "UPDATE switch set up=2, last_monitored=NOW() where id='$switch_id';";
   $logger->debug($query,2);
   $final = mysql_query($query);
   if (! $final)
   {
      $logger->logit(mysql_error(), LOG_ERROR);
   }
}

// measure performance
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
$logger->debug("Time taken= ".$totaltime." seconds\n");

exit(0);
?>
