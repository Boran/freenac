#!/usr/bin/php
<?php
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

if ($dynamic)					//Configure port to be used with VMPS
{
   if (set_port_as_dynamic($switch, $port))
   {
      $string="Port $port on switch $switch successfully set to dynamic.";
      log2db('info', $string);
      $logger->setLogToStdOut(false);
      $logger->logit($string);
      $logger->setLogToStdOut(true);
   }
   else
   {
      exit(1);
   }
}
else if ($static)							//Configure as static
{
  if (set_port_as_static($switch,$port,$static_vlan))
  {
     $string="Port $port successfully set to static with VLAN $static_vlan.";
     log2db('info',$string);
     $logger->setLogToStdOut(false);
     $logger->logit($string);
     $logger->setLogToStdOut(true);
  }
  else
  {
     exit(1);    // error written to syslog by set_port_as_static
  }
}

?>
