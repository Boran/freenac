#!/usr/bin/php -- -f
<?
/**
 * enterprise/deactivate_vmps
 *
 * Long description for file:
 *
 * This script configures all switch ports in the FreeNAC database to static.
 *
 * TESTED:
 *      Catalyst 2940 (IOS), 3560 (IOS), 2948 (CatOS)
 *
 * USAGE :
 *     	deactivate_vmps [switches...]
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

function print_usage($code)
{
   $usage=<<<EOF
USAGE: activate_vmps [switches...] [OPTIONS]

        Web:      http://www.freenac.net/
        Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Activate VMPS from the switches specified on the command line which are in the FreeNAC database.
	     If no switches are specified, it takes the list of all switches present in the FreeNAC database.
	     If the -f option is present, it will read information from filename instead of database.

WARNING: No providing a list of switches from the command line, activates VMPS in your entire network.
         Use it wisely!

OPTIONS:
        -h              Display this help screen
	-f filename	Read information from filename instead of database
	-c rw_community	Use this SNMP RW community instead of the one defined in config.inc

EOF;
   $logger->logit($usage);
   exit($code);
}

$read_from_db=true;

if ($argc>1)
   $options=getopt('hf:c:');
if ($options)
{
   if (array_key_exists('h',$options))
      print_usage(0);
   if (array_key_exists('f',$options))
   {
      $read_from_db=false;
      #$file_name=dirname(__FILE__).'/'.trim($options['f']);
      $file_name=trim($options['f']);
   }
   if (array_key_exists('c',$options))
      $snmp_rw=trim($options['c']);   
}

//
//Take parameters off the command line
//
$j=0;
for ($i=0;$i<$argc;$i++)
{
   switch($argv[$i])
   {
     case '-c': 
     case '-f':
         ++$i;
         break;
     case strstr($argv[$i],'-c'):
     case strstr($argv[$i],'-f'):
         break;
     default:
         $command_line[$j]=$argv[$i];
         $j++;
         break;
   }
}

if ($read_from_db)
   db_connect();

if (($j==1)&&($read_from_db))
{
   $logger->logit("\nWARNING: You are about to enable VMPS in all switches in your database\n");
   $logger->logit("\nPress Ctrl+C to stop this task before the timer expires...\n\n");
   for ($i=10;$i>0;$i--)
   {
      $logger->logit("$i seconds to go...\n");
      sleep(3);
   }
   $logger->logit("\nProceeding to enable VMPS in all switches registered in the database\n\n");
   $query="select * from switch";
}
else if (!$read_from_db)
{
   $file_contents=@file_get_contents($file_name);
   if (!$file_contents)
   {
      $logger->logit("Couldn't load contents of file $file_name\n");
      exit(1);
   }
}
else 
{
   $query="select * from switch where ";
   for ($i=1;$i<$j;$i++)
   {
      if ($i==1)
         $query.="name like '".mysql_real_escape_string($command_line[$i])."' or ip='".mysql_real_escape_string($command_line[$i])."'";
      else
         $query.="or name like '".mysql_real_escape_string($command_line[$i])."' or ip='".mysql_real_escape_string($command_line[$i])."'";
   }
   $query.=";";
}
   

if ($read_from_db)
{
   while (!$res=mysql_query($query));
   $total_switches=mysql_num_rows($res);
   if (($j>1)&&(mysql_num_rows($res)==0))
   {
      $logger->logit("No such name or ip found in the switch table\n");
      exit(1);
   }
}
else
{
   $lines=explode("\n",$file_contents);
   $total_ports=0; 
   foreach($lines as $line)
   {
      $info=explode(',',$line);
      $modify[$info[0]][]=$info[1];
      $total_ports++;
   }
   $total_ports--;
}
$counter=0;
$affected_ports=0;

if ($read_from_db)
{
   while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
   {
      $switch=$result['ip'];
      $logger->logit("Activating VMPS on switch $switch\n");
      if ( ! $ports_on_switch = ports_on_switch($switch) )      	//Get the list of ports on the switch
      {
         continue;
      }

      $query="select p.name as port_name, v.default_name as vlan from port p inner join switch sw on p.switch=sw.id inner join vlan v on p.last_vlan=v.id where p.last_vlan>2 and p.auth_profile='2' and sw.ip='$switch';";
      while (!$res1=mysql_query($query));						//Execute query
      $total_ports+=mysql_num_rows($res1);
      $counter=0;
      while ($result1=mysql_fetch_array($res1,MYSQL_ASSOC))
      {
         $port=$result1['port_name'];
   
         if (set_port_as_dynamic($switch,$port))    //Try to set port as dynamic
         {
            $counter++;
         } 
      }
      if ($counter>0)
      {
         $string="$counter ports affected on switch $switch";
         $logger->logit("\n\t$string\n\n");
         $logger->setLogToStdOut(false);
         $logger->logit($string);
         $logger->setLogToStdOut();
         log2db('info',"activate_vmps: ".$string);
      }
      $affected_ports+=$counter;

      $text=array_map("remove_type",snmprealwalk($switch,$snmp_rw,$snmp_sw['descr']));		//Determine if switch is IOS
      $ios=0;
      foreach($text as $string)	
      {
         if (strpos($string,'IOS'))
            $ios++;
      }
      $old=0;
      $written=0;
      if ($ios)											//Switch is an IOS, perform a 'wr' using the old way
      {
         if (@snmpset($switch,$snmp_rw,$write_command['old'].'.0','i',1))		
         {
            $old++;
            $written++;
            $logger->logit("\tCurrent configuration saved to switch $switch\n");
         }
         else if (@snmpset($switch,$snmp_rw,$write_command['old'],'i',1))
         {
            $old++;
            $written++;
            $logger->logit("\tCurrent configuration saved to switch $switch\n");
         }
      }

      if ($ios&&!$old)								//Old way didn't work, try the new one
      {
         $rand_val=rand(1,999);
         if (@snmpset($switch,$snmp_rw,$write_command['source'].'.'.$rand_val,'i',2))
         {
            if (@snmpset($switch,$snmp_rw,$write_command['destination'].'.'.$rand_val,'i',1))
            {
               if (@snmpset($switch,$snmp_rw,$write_command['execute'].'.'.$rand_val,'i',4))
                  $written++;
            }
         }
      }
      
      if ($ios&&!$written)
      {
         $logger->logit("\tCouldn't save current configuration to switch $switch\n");
      } 
      
      if (!@snmpset($switch,$snmp_rw,$vmps_reconfirm.'.0','i',2))			   //Do a 'reconfirm vmps'
      {
         if (!@snmpset($switch,$snmp_rw,$vmps_reconfirm,'i',2))
            $logger->logit("\tCouldn't do a reconfirm VMPS\n\n");
	 else
            $logger->logit("\tReconfirmed VMPS on switch $switch\n\n");
      }
      else
         $logger->logit("\tReconfirmed VMPS on switch $switch\n\n");
   }
}
else
{
   $total_switches=0;
   foreach ($modify as $switch=>$ports)
   {
      if (empty($switch))
         continue;
      $logger->logit("Activating VMPS on switch $switch\n");
      if ( ! $ports_on_switch = ports_on_switch($switch))        //Get the list of ports on the switch
      {
         continue;
      }
      $counter=0;
      
      foreach($ports as $port)
      {
         if (! $port_index = get_snmp_index($port,$ports_on_switch))            //Is the port present in this switch?
         {
            $logger->logit("\tPort $port not found on switch $switch\n");
            continue;
         }

         if (set_port_as_dynamic($switch,$port_index))    //Try to set port as dynamic
         {
            $logger->logit("\tPort $port successfully set to dynamic.\n");
            $counter++;
         }

      }
      if ($counter>0)
      {
         $string="$counter ports affected on switch $switch";
         $logger->logit("\n\t$string\n\n");
         $logger->setLogToStdOut(false);
         $logger->logit($string);
         $logger->setLogToStdOut();
         log2db('info',"activate_vmps: ".$string);
      }
      $total_switches++;
      $affected_ports+=$counter;

      $text=array_map("remove_type",snmprealwalk($switch,$snmp_rw,$snmp_sw['descr']));          //Determine if switch is IOS
      $ios=0;
      foreach($text as $string)
      {
         if (strpos($string,'IOS'))
            $ios++;
      }
      $old=0;
      $written=0;
      if ($ios)                                                                                 //Switch is an IOS, perform a 'wr' using the old way
      {
         if (@snmpset($switch,$snmp_rw,$write_command['old'].'.0','i',1))
         {
            $old++;
            $written++;
            $logger->logit("\tCurrent configuration saved to switch $switch\n");
         }
         else if (@snmpset($switch,$snmp_rw,$write_command['old'],'i',1))
         {
            $old++;
            $written++;
            $logger->logit("\tCurrent configuration saved to switch $switch\n");
         }
      }
      if ($ios&&!$old)                                                          		//Old way didn't work, try the new one
      {												//NOTE: This code below hasn't been tested
         $rand_val=rand(1,999);
         if (@snmpset($switch,$snmp_rw,$write_command['source'].'.'.$rand_val,'i',2))
         {
            if (@snmpset($switch,$snmp_rw,$write_command['destination'].'.'.$rand_val,'i',1))
            {
               if (@snmpset($switch,$snmp_rw,$write_command['execute'].'.'.$rand_val,'i',4))
                  $written++;
            }
         }
      }
      if ($ios&&!$written)
      {
         $logger->logit("\tCouldn't save current configuration to switch $switch\n");
      }

      if (!@snmpset($switch,$snmp_rw,$vmps_reconfirm.'.0','i',2))                          	//Do a 'reconfirm vmps'
      {
         if (!@snmpset($switch,$snmp_rw,$vmps_reconfirm,'i',2))
            $logger->logit("\tCouldn't do a reconfirm VMPS\n\n");
         else
            $logger->logit("\tReconfirmed VMPS on switch $switch\n\n");
      }
      else
         $logger->logit("\tReconfirmed VMPS on switch $switch\n\n");
   }  
}
if ($read_from_db)
   $string="Switches in total: $total_switches\tPorts affected: $affected_ports out of $total_ports. Info read from db";
else
   $string="Switches in total: $total_switches\tPorts affected: $affected_ports out of $total_ports. Info read from $file_name";
$logger->logit("$string\n");
$logger->setLogToStdOut(false);
$logger->logit($string);
$logger->setLogToStdOut();
log2db('info',"activate_vmps: ".$string);
exit(0);

?>


