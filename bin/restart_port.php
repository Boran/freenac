#!/usr/bin/php -- -f
<?
/**
 * /opt/nac/bin/restart_port
 *
 * Long description for file:
 *
 * This script configures a switch port as static or dynamic.
 * Default is dynamic.
 * If static, the vlan name that will be configured on that interface must be provided
 *
 * TESTED:
 *	Catalyst 2940 (IOS), 3560 (IOS), 2948 (CatOS)
 * 
 * USAGE :
 * 	/opt/nac/bin/restart_port port switch
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
#$logger->setLogToStdErr();

//
//------------------------------------------ Functions ------------------------------------------------
//

function print_usage($code)
{
   $usage=<<<EOF
USAGE: restart_port port switch 

	Web:      http://www.freenac.net/
	Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Restart a switch port.

OPTIONS: 
        -h		Display this help screen	

EOF;
   echo $usage;
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
      echo "Port and switch must be specified\n";
      print_usage(1);
      break;
   case 2:
      echo "Switch must be specified\n";
      print_usage(1);
      break;
   case 3:
      $switch=$command_line[2];
      $port=$command_line[1];
      break;
   default:
      echo "Only one port can be restarted at the time\n";
      print_usage(1);
      break;
}

//
//Ok, here we go ----------------------------------------- main stuff ------------------------------------------
//

debug1("Port $port on $switch");
logit("Port restart try: $port on switch $switch");

$ports_on_switch=snmprealwalk($switch,$snmp_rw,'1.3.6.1.2.1.31.1.1.1.1');	//Get the list of ports on the switch
if (empty($ports_on_switch))
{
   echo "ABORTED: Could not contact switch $switch or unknown Switch.\n";
   logit("ABORTED: Could not contact switch $switch or unknown Switch");
   exit(2);
}
$ports_on_switch=array_map("remove_type",$ports_on_switch);		//We are only interested in the string
$port_oid=array_search($port,$ports_on_switch);				//Is the port from the command line present in this switch?
if (empty($port_oid))
{
   echo "Port $port not found on switch $switch\n";
   logit("Port $port not found on switch $switch");
   log2db('info',"Port $port not found on switch $switch");
   exit(1);
}

$port_index=get_last_index($port_oid);					//Port found, get the index

if (turn_off_port($port_index))
{
   if (turn_on_port($port_index))
   {
      logit("Port successfully restarted $port on switch $switch");
      log2db('info',"Port successfully restarted $port on switch $switch");
      exit(0);
   }
   else
   {
      logit("Port $port on switch $switch couldn't be restarted");
      log2db('info',"Port $port on switch $switch couldn't be restarted");
      exit(1);
   }
}
else
{
   logit("Port $port on switch $switch couldn't be restarted");
   log2db('info',"Port $port on switch $switch couldn't be restarted");
   exit(1);
}

?>
