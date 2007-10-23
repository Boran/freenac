#!/usr/bin/php -- -f
<?
/**
 * enterprise/snmp_set_port
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
 * 	snmp_set_port switch port [-s vlan_name][-d][-h][-s]
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
require_once "./funcs.inc.php";               # Load settings & common functions
require_once "./snmp_defs.inc.php";

$logger->setLogToStdOut();

$static=false;
$dynamic=false;

//
//------------------------------------------ Functions ------------------------------------------------
//

function print_usage($code)
{
   global $logger;
   $usage=<<<EOF
USAGE: snmp_set_port.php switch port [OPTIONS]

	Web:      http://www.freenac.net/
	Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Set a switch port's configuration as either static or dynamic. Default is dynamic.

OPTIONS: 
	-d		Set port to dynamic
	-s vlan_name	Set port to static and program vlan_name on that port
        -h		Display this help screen	

EOF;
   $logger->logit( $usage);
   exit($code);
}

//
//---------------------------------------- Parsing of command line parameters --------------------------------------
//

if ($argc>3)
   $options=getopt("s:dh");
if ($options)
{
   if (array_key_exists('h',$options))   
      print_usage(0);
   if (array_key_exists('s',$options))
   {
      $static=true;
      $static_vlan=trim($options['s']);
   }
   if (array_key_exists('d',$options))
   {
      $dynamic=true;
   }
}   
else
   $dynamic=true;
if ($dynamic && $static)
{
   $logger->logit( "Port can only be configured to either static OR dynamic\n");
   print_usage(1);
}
//  
//Take parameters off the command line
//

$j=0;
for ($i=0;$i<$argc;$i++)
{
   switch($argv[$i])
   {
      case '-d':
      case '-h':
         break;
      case '-s':
         ++$i;
         break;
      case strstr($argv[$i],'-s'):
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
      $logger->logit( "Switch and port must be specified\n");
      print_usage(1);
      break;
   case 2:
      $logger->logit( "Port must be specified\n");
      print_usage(1);
      break;
   case 3:
      $switch=$command_line[1];
      $port=$command_line[2];
      break;
   default:
      $logger->logit( "Only one port can be configured at the time\n");
      print_usage(1);
      break;
}

//
//Ok, here we go ----------------------------------------- main stuff ------------------------------------------
//

$ports_on_switch=@snmprealwalk($switch,$snmp_rw,$snmp_if['name']);	//Get the list of ports on the switch
if (empty($ports_on_switch))
{
   $logger->logit( "Couldn't establish communication with $switch with the defined parameters.\n");
   $logger->logit( "Check that you have properly typed the switch name or ip and your SNMP_RW community.\n");
   exit(1);
}
$ports_on_switch=array_map("remove_type",$ports_on_switch);		//We are only interested in the string
$port_oid=array_search($port,$ports_on_switch);				//Is the port from the command line present in this switch?
if (empty($port_oid))
{
   $logger->logit( "Port $port not found on switch $switch\n");
   exit(1);
}
$port_index=get_last_index($port_oid);					//Port found, get the index
$oid=$snmp_port['ad_status'].'.'.$port_index;

if (!turn_off_port($port_index))
{
   exit(1);
}

$oid=$snmp_port['type'].'.'.$port_index; 

if ($dynamic)								//Configure port to be used with VMPS
{
  if (snmpset($switch,$snmp_rw,$oid,'i',2))				//Set port as dynamic 
  {
     if (turn_on_port($port_index))
     {
        $logger->logit( "Port $port successfully set to dynamic.\n");
        exit(0);
     }
     else
        exit(1);
  }
  else
  {
     $logger->logit( "An error ocurred while communicating with the switch.\n");
     exit(1);
  }
}
else if ($static)							//Configure as static
{
  $vlans_on_switch=@snmprealwalk($switch,$snmp_rw,$snmp_vlan['name']);	//Lookup of VLAN in the switch
  if (empty($vlans_on_switch))
  {
     $logger->logit( "Couldn't establish communication with $switch. This may be due to a network failure.\n");
     turn_on_port($port_index);
     exit(1);
  }
  $vlans_on_switch=array_map("remove_type",$vlans_on_switch);
  $vlan_oid=array_search($static_vlan,$vlans_on_switch);		//Is the VLAN present in the switch?
  if (empty($vlan_oid))
  {
     $logger->logit( "VLAN $static_vlan not found on switch $switch.\n");
     turn_on_port($port_index);
     exit(1);
  }
  $vlan=get_last_index($vlan_oid);					//VLAN found, get the index
  if (snmpset($switch,$snmp_rw,$oid,'i',1))				//Set port to static
  {
     $oid=$snmp_if['vlan'].'.'.$port_index;					
     if (snmpset($switch,$snmp_rw,$oid,'i',$vlan))			//And set the VLAN on that port
     {
        if (turn_on_port($port_index))
        {
           $logger->logit( "Port $port successfully set to static with VLAN $static_vlan.\n");
           exit(0);
        }
        else
           exit(1);
     }
     else
     {
        $logger->logit( "An error ocurred while communicating with the switch.\n");
        exit(1);
     }
  }
  else
  {
     $logger->logit( "An error ocurred while communicating with the switch.\n");
     exit(1);
  }
}

?>
