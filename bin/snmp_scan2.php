#!/usr/bin/php
<?
/**
 * snmp_scan2.php
 *
 * Long description for file:
 * This script will scan all existing switches & routers using SNMP
 * It focuses on getting information about systems who are not managed
 * or/and on static access ports. With this information, NAC then has
 * an overview of all systems on the network, providing the Network
 * manager with a more complete picture. This non-vmps-managed systems
 * can still be scanned, and their Anti-Virus status shown.
 * Such non-managed are typically critical servers, network equipment,
 * VirtualServers, systems with static vlan ports ...
 *
 * Newly discovered devices are inserted into the systems table
 *   as status=3, name=unknown, and update mac,lastseen,switch,port
 *   For existing systems; lastseen,switch,port is updated.
 *
 * USAGE :
 *   -switch name - only scan given switch (require switch name)
 *   -vlan name - only scan given vlan (require vlan name)
 *   -help - print usage
 *   (no args) : will scan all switches and all vlans
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

function validate_ip($ip)
{
   $return = true;
   $tmp = explode(".", $ip);
   if(count($tmp) < 4)
   {
      $return = false;
   }
   else 
   {
      foreach($tmp AS $sub)
      {
         if($return != false)
         {
            if(!eregi("^([0-9])", $sub))
            {
               $return = false;
            } 
            else {
               $return = true;
            }
         }
      }
   }
   return $return;
} 


function print_usage($code)
{
   global $logger;
   $usage=<<<EOF
USAGE: snmp_scan2.php [switch_ips...] [OPTIONS]

        Web:      http://www.freenac.net/
        Email:    opennac-devel@lists.sourceforge.net

DESCRIPTION: Perform an SNMP scanning of switches and routers.
	     It focuses on getting information about systems who are not managed
	     or/and on static access ports. With this information, NAC then has
	     an overview of all systems on the network, providing the Network
	     manager with a more complete picture. This non-vmps-managed systems
	     can still be scanned, and their Anti-Virus status shown.
	     Such non-managed are typically critical servers, network equipment,
	     VirtualServers, systems with static vlan ports ...

 	     Newly discovered devices are inserted into the systems table
	     as status=3, name=unknown, and update mac,lastseen,switch,port
	     For existing systems; lastseen,switch,port is updated.
	     If no switches are specified, it takes the list of all switches present in the FreeNAC database.

OPTIONS:
        -h              Display this help screen
	-s		Log to syslog instead of STDOUT
        -d level        Activate debug level. Valid levels are 1,2 and 3.
        -c community 	Use this SNMP community instead of the one defined in config.inc
	-r 		Dry-run mode. Don't write changes to database
	-e		Document also devices connected to the switch (Slow)

EOF;
   $logger->logit($usage);
   exit($code);
}

$do_mysql=true;
$community=$snmp_ro;
$connected_devices=false;

if ($argc>1)
   $options=getopt('hsred:c:');
if ($options)
{
   if (array_key_exists('h',$options))
   {
      print_usage(0);
   }
   if (array_key_exists('s',$options))
   {
      $logger->setLogToStdOut(false);
   }
   if (array_key_exists('e', $options))
   {
      $connected_devices=true;
   }
   if (array_key_exists('r', $options))
   {
      $do_mysql=false;
   }
   if (array_key_exists('d',$options))
   {
      if (is_numeric($options['d']))
      {
         $logger->setDebugLevel((int)$options['d']);
      }
      else
         print_usage(1);
   }
   if (array_key_exists('c',$options))
   {
      $community=trim($options['c']);
      if (!$community)
         print_usage(1);
   }
}

//
//Take parameters off the command line
//
$j=0;
for ($i=0;$i<$argc;$i++)
{
   switch($argv[$i])
   {
     case '-e':
     case '-r':
     case '-s':
     case '-d':
     case '-c':
         ++$i;
         break;
     case strstr($argv[$i],'-d'):
     case strstr($argv[$i],'-c'):
         break;
     default:
         $command_line[$j]=$argv[$i];
         $j++;
         break;
   }
}
$ips=array();
if ($j > 1)
{
   for ($i=1; $i < $j; $i++)
   {
      if (validate_ip($command_line[$i]))
         $ips[]=$command_line[$i];
   }
}
else
{
   $query="select ip from switch";
   $logger->debug($query,3);
   $res = mysql_query($query);
   if ( ! $res)
   {
      $logger->logit(mysql_error(),LOG_ERR);
      exit(1);
   }
   while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
      $ips[]=$result['ip'];
   
}
if ($ips && is_array($ips))
{
   foreach($ips as $ip)
   {
      $switchid=v_sql_1_select("select id from switch where ip='$ip'");
      if ( ! $switch_id)
      {
         $query="insert into switch set ip='$ip'";
         $logger->debug($query,3);
         if ($do_mysql)
         {
            $res = mysql_query($query);
            if (! $res)
            {
               $logger->logit(mysql_error(),LOG_ERR);
               continue;
            }
            $switchid=v_sql_1_select("select id from switch where ip='$ip'");
         }
      }
      $logger->logit("Start scanning $ip");
      $switches=new Switch_SNMP($ip);
      $switches->Interfaces($community);
      if (! $switches->interfaces)
         continue;
      foreach ($switches->interfaces as $port)
      {
         if ($switches->getPortType($port,false,$community)==1)
            $port_type='static';
         else if ($switches->getPortType($port,false,$community)==2)
            $port_type='dynamic';
         else if ($switches->getPortType($port,false,$community)==3)
            $port_type='trunk';
         $type_id=v_sql_1_select("select * from auth_profile where method='$port_type'");    //Get the id from auth_profile
         if (!$type_id)              //If we didn't get an id, set it to 0
            $type_id=0;
         $vlan=$switches->getVlanOnPort($port,false,$community);
         if ($vlan>1)
         {
            $vlan=v_sql_1_select("select id from vlan where default_id='$vlan'");
         }
         $query="select * from port where name='$port' and switch='$switchid';";
         $logger->debug($query,3);
         $res=mysql_query($query);
         if ($res)                   //Is this port in the DB?
         {
            $result=mysql_fetch_array($res,MYSQL_ASSOC);
            $comment=mysql_real_escape_string($switches->getPortDescription($port,false,$community));
            if ($result['id'])       //Yes, update its comment and its auth_profile
            {
               if ($result['comment'])
                  $comment=$result['comment'];
               if (($port_type=='static')&&($vlan))
                  $query="update port set last_auth_profile='$type_id',comment='$comment', up='{$switches->getPortStatus($port,false,$community)}', last_vlan='$vlan' where id='".$result['id']."';";
               else
                  $query="update port set last_auth_profile='$type_id', up='{$switches->getPortStatus($port,false,$community)}', comment='$comment' where id='".$result['id']."';";
               $logger->debug($query,3);   
               if ($do_mysql)
               {
                  $res=mysql_query($query);
                  if (!$res)
                  {
                     $logger->logit(mysql_error(),LOG_ERR);
                  } // if (!$res)
               } // if ($do_mysql)
               
            }
            else // No, insert it
            {
               if (($port_type=='static')&&($vlan))
                  $query="insert into port set switch='$switchid', name='$port',comment='$comment',last_auth_profile='$type_id',last_vlan='$vlan',up='{$switches->getPortStatus($port,false,$community)}'";
               else
                  $query="insert into port set switch='$switchid', name='$port',comment='$comment',last_auth_profile='$type_id',up='{$switches->getPortStatus($port,false,$community)}'";
               $logger->debug($query,3);
               if ($do_mysql)
               {
                  $res=mysql_query($query);
                  if (!$res)
                  {
                     $logger->logit(mysql_error(),LOG_ERR);
                  } // if (!$res)
               } // if ($do_mysql)
            } // if ($result['id']) 
         } //if ($res)
         else
         {
            $logger->logit(mysql_error(),LOG_ERR);
            continue;
         }
         if (! $switches->software && ! $switches->model )
         {
            $sw = mysql_real_escape_string($switches->software($community));
            $hw = mysql_real_escape_string($switches->model($community));
            if ($hw || $sw)
            {
               //If we don't find the hardware, at least let's update the software we found
               $logger->debug("($switchid) $ip : HW = $hw / SW = $sw ");
               $query = "UPDATE switch SET hw='$hw',sw='$sw' WHERE id=$switchid;";
               $logger->debug($query,3);
               if($domysql)
               {
                  $res=mysql_query($query);
                  if (!$res)
                     $logger->logit("Unable to update switch info",LOG_ERR);
               }; // if($domysql)
            }
            else
            {
               $logger->debug("($switchid) $ip impossible to get HW or SW");
            }; // if ($hw || $sw)
         } // if (! $switches->software && ! $switches->model )
      } //if ($res)                   //Is this port in the DB?
      if ($connected_devices)
      { 
         $macs=$switches->connected_devices($community);
         if (! is_array($macs))
            continue;
         $counter=count($macs['mac']);
         for ($i=0; $i < $counter; $i++)
         {
            $mac=mysql_real_escape_string($macs['mac'][$i]);
            $query="select id from systems where mac='$mac'";
            $logger->debug($query,3);
            $sid=v_sql_1_select($query);

            $port=mysql_real_escape_string($macs['port'][$i]);
            $query="select id from port where name='$port' and switch='$switchid'";
            $logger->debug($query,3);
            $port_id=v_sql_1_select($query);
            if ( ! $port_id )
               continue;
            if ($switches->getPortType($port,false,$community)!=3)
            {
               if (preg_match($conf->router_mac_ip_ignore_mac, $mac))
                  continue;
               if ($sid)
               {
                  $query = "UPDATE systems SET LastPort='$port_id', LastSeen=NOW() WHERE id=$sid;";
                  $logger->debug($query,3);
                  $logger->debug("Switch $ip - $port - $mac - update host");
               }
               else
               {
                  $vlan=mysql_real_escape_string($macs['vlan'][$i]);
                  $query="select id from vlan  where default_id='$vlan'";
                  $logger->debug($query,3);
                  $vlan_id=v_sql_1_select($query);
                  $query = 'INSERT INTO systems (name, mac, LastPort, vlan, status,LastSeen) VALUES ';
                  $query .= "('unknown',$mac',$port_id,$vlan_id, 3, NOW());";
                  $logger->debug($query,3);
                  if ($do_mysql)
                  {
                     $res=mysql_query($query);
                     if (!$res)
                        $logger->logit(mysql_error(),LOG_ERR);
                  }
               } //if ($sid)
            } // if ($switches->getPortType($port,false,$community)!=3)
         } // for ($i=0; $i < $counter; $i++)
      } // if ($connected_devices)
      unset($switches);
   }
}

?>
