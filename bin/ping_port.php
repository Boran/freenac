#!/usr/bin/php -- -f 
<?
/**
 * /opt/nac/bin/ping_port.php
 *
 * Long description for file:
 *
 * This script pings a switch port to know if it is up or down.
 *
 * TESTED:
 *      Catalyst 2940 (IOS), 3560 (IOS), 2948 (CatOS), 2960G (IOS)
 *
 * USAGE :
 *      /opt/nac/bin/ping_port.php port switch
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

$logger->setDebugLevel(0);
$logger->setLogToStdOut(true);

//
//------------------------------------------ Functions ------------------------------------------------
//

function print_usage($code)
{
   global $logger;
   $usage=<<<EOF
USAGE: ping_port.php port switch

        Web:      http://www.freenac.net/
        Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Ping a switch port to tell if it is up or not.

OPTIONS:
        -h              Display this help screen

EOF;
   $logger->logit($usage);
   exit($code);
}
//
//---------------------------------------- Parsing of command line parameters --------------------------------------
//
if ($argc>3)
   $options=getopt("h");
if ($options)
{
   if (array_key_exists('h',$options))
      print_usage(0);
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
         break;
      default:
         $command_line[$j]=$argv[$i];
         $j++;
         break;
   }
}

switch($j)
{
   case 0:
      print_usage(1);
      break;
   case 1:
      $logger->logit("Port and switch must be specified\n");
      print_usage(1);
      break;
   case 2:
      $logger->logit("Switch must be specified\n");
      print_usage(1);
      break;
   case 3:
      $switch=$command_line[2];
      $port=$command_line[1];
      break;
   default:
      $logger->logit( "Only one port can be pinged at the time\n");
      print_usage(1);
      break;
}

//
//---------------------------------------  Main stuff ----------------------------------------------------------
//
db_connect();

#Look up this port in the database
$query="SELECT p.id FROM port p INNER JOIN switch s ON s.id=p.switch WHERE p.name='$port' AND s.ip='$switch' LIMIT 1;";
$port_id = v_sql_1_select($query);

if (!$port_id)
{
   $logger->logit("Attempted to ping port $port on $switch, but this port wasn't found in the database", LOG_WARNING);
   exit(1);
}

#Retrieve port's snmp index
$port_index=get_snmp_port_index($port,$switch);

if ($port_index)
{
   $oid='1.3.6.1.2.1.2.2.1.7';
   #For some reason, the following
   #$oid='1.3.6.1.2.1.2.2.1.7.'.$port_index;
   #doesn't work. It gives back an empty array which 
   #is pretty much useless, that's why
   #we get the status for all ports
   $answer=snmprealwalk($switch,$snmp_rw,$oid);
   #Remove type, so we keep only the value
   $answer=array_map("remove_type",$answer);

   #Go through all the ports
   foreach ($answer as $key => $value)
   {
      #And find the one of interest
      if (strstr($key,$port_index))
      {
         #And get its status
         if (strpos($value,'1'))
            $status=1;
         else if (strpos($value,'2'))
            $status=2;
         else if (strpos($value,'3'))
            $status=3;
         #Update database
         $query="UPDATE port SET get_status='$status' WHERE id='$port_id';";
         mysql_query($query);
         #And show its status
         if ($status==1)
            $logger->logit("Port $port on $switch is UP"); 
         else if ($status==2)
            $logger->logit("Port $port on $switch is DOWN");
         else if ($status==3)
            $logger->logit("Port $port on $switch is TESTING");
         exit(0);
      }
   }
}
else
{
   $logger->logit("Port not found on switch");
   exit(1);
}

?>
