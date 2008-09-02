<?php
/**
 * /opt/nac/contrib/snmp_defs.inc
 *
 * Long description for file:
 * - Specific SNMP queries
 * - Some functions for snmp scripts
 *
 * - tested on the following switches:
 * - Cisco
 *	- 3500xl
 * 	- 2950, 2940-8TT
 *	- 3550
 *	- 3750
 *
 * Further reading:
 *    http://www.cisco.com/public/sw-center/netmgmt/cmtk/mibs.shtml
 *    The "getif" tool for exploring MIBs.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Thomas Dagonnier - Sean Boran (FreeNAC Core Team)
 * @copyright                   2006 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     CVS: $Id:$
 * @link                        http://www.freenac.net
 *
 */


$snmp_sw['descr'] =             '1.3.6.1.2.1.1.1';
#$snmp_sw['soft_version'] = 	'1.3.6.1.2.1.47.1.1.1.1.10';
$snmp_sw['name'] =              '1.3.6.1.2.1.1.5';
$snmp_sw['location'] =          '1.3.6.1.2.1.1.6';
$snmp_sw['contact'] =           '1.3.6.1.2.1.1.4';
// $snmp_sw['cdp'] =		'1.3.6.1.4.1.9.9.23.1.2.1.1.8';		// get array with port index + CDP neigbours

$snmp_ifaces =                  '1.3.6.1.2.1.31.1.1.1.1';
$snmp_mac =			'1.3.6.1.2.1.17.4.3.1.1';
$snmp_bridge =			'1.3.6.1.2.1.17.4.3.1.2';
$snmp_ports =			'1.3.6.1.2.1.17.1.4.1.2';

$snmp_cisco['hw'] =             '1.3.6.1.2.1.47.1.1.1.1.13.1';

$snmp_if['name'] =              '1.3.6.1.2.1.31.1.1.1.1';
$snmp_if['highspeed'] =         '1.3.6.1.2.1.31.1.1.1.15';              //      10 - 100 - 1000 
$snmp_if['description'] =       '1.3.6.1.2.1.31.1.1.1.18';
$snmp_if['phys'] =              '1.3.6.1.2.1.31.1.1.1.17';              // true - false
$snmp_if['trunk'] =             '1.3.6.1.4.1.9.9.46.1.6.1.1.14';        // 1 : on - 2 : off - 3 : desirable - 4 : auto - 5 : onNoNegotiate
$snmp_if['vlan'] =              '1.3.6.1.4.1.9.9.68.1.2.2.1.2';
$snmp_if['type'] =		'1.3.6.1.4.1.9.9.68.1.2.2.1.1';                 // 1 - static; 2 - dynamic; 3 - multivlan

#$snmp_vlan['id'] =		'1.3.6.1.4.1.9.9.46.1.3.1.1.1';			// not reliable ???
$snmp_vlan['state'] =		'1.3.6.1.4.1.9.9.46.1.3.1.1.2';
$snmp_vlan['type'] =		'1.3.6.1.4.1.9.9.46.1.3.1.1.3';			// 1 : ethernet - 2, 4 : fddi -3 tokenring - 5, trnet
$snmp_vlan['name'] =		'1.3.6.1.4.1.9.9.46.1.3.1.1.4';

$snmp_port['type'] = 		'1.3.6.1.4.1.9.9.68.1.2.2.1.1';			// 1 - static; 2 - dynamic; 3 - multivlan
$snmp_port['trunk'] = 		'1.3.6.1.4.1.9.9.46.1.6.1.1.14';		// 1 - trunking; 2 - ot trunking
#$snmp_port['stp'] =		'1.3.6.1.4.1.9.9.82.1.9.3.1.2';			// 1 - true; 2 - false
#$snmp_port['802.1x'] =		'1.3.6.1.4.1.9.5.1.19.1.1.20';			// 1 - Port support for 802.1x; 2 - No support for 802.1x on this port

$snmp_port['ad_status'] = 	'1.3.6.1.2.1.2.2.1.7';	// 1 - up; 2 - down; 3 - testing;
#$snmp_port['op_status'] = 	'1.3.6.1.2.1.2.2.1.8';	// 1 - up; 2 - down; 3 - testing; 4 - unknown; 5 - dormant; 6 - notpresent; 7 - lowerLayerDown
#$snmp_port['dot1x_control'] =	'1.1.8802.1.1.1.1.2.1.1.6';  // 1 - forceUnauthorized; 2 - auto; 3 - forceAuthorized
#$snmp_port['dot1x_status'] =	'1.1.8802.1.1.1.1.2.1.1.5';  // 1 - authorized; 2 - unauthorized;
#$snmp_port['dot1x_state'] = 	'1.1.8802.1.1.1.1.2.1.1.1';  	// 1 - initialize; 2 - disconnected; 3 - connecting;
							   	// 4 - authenticating; 5 - authenticated; 6 - aborting;
								// 7 - held; 8 - forceAuth; 9 - forceUnauth
#$snmp_port['dot1x_eapolrx'] = 	'1.1.8802.1.1.1.1.2.2.1.1';	//Number of valid EAPOL frames received
#$snmp_port['dot1x_quietp'] = 	'1.1.8802.1.1.1.1.2.1.1.7';
#$snmp_port['dot1x_authtxp'] =	'1.1.8802.1.1.1.1.2.1.1.8';
$vmps_reconfirm = 		'1.3.6.1.4.1.9.9.68.1.1.4';	// 2 to reconfirm
$write_command['old'] = 	'1.3.6.1.4.1.9.2.1.54';	//1 to write
$write_command['source'] =	'1.3.6.1.4.1.9.9.96.1.1.1.1.3';	//2 for running config
$write_command['destination'] =	'1.3.6.1.4.1.9.9.96.1.1.1.1.4';	//1 for startup config
$write_command['execute'] =	'1.3.6.1.4.1.9.9.96.1.1.1.1.14'; //4 for create and go
# 1.0.8802.1.1.1.1.2.1.1.1	//Current value of the Authenticator PAE state machine
/** This function is called for any errors or
 *  messages sent to stdout/err. The idea is to catch all
 *  such messages and send them to syslog, this this is a daemon normally
 *  detached from the console
 */
function callback($buffer)
{
  if (strlen($buffer) > 1) {
    logit('callback:[' . $buffer .']');
  }
  #return(true);
}


/**
* Get the snmp port index
* @param mixed $port    Port name to look for.
* @param mixed $switch  Switch to ask
* @return mixed         Port index if found, false otherwise
*/
function get_snmp_port_index($switch,$port)
{
   global $logger;
   if ($switch && $port)
   {
      if ( ! $ports_on_switch = ports_on_switch($switch) )              //Get the list of ports on the switch
      {
         return false;                                             # Error handling in ports_on_switch
      }

      if ( ! $snmp_port_index = get_snmp_index($port,$ports_on_switch))                   //Get port's index
      {
         $logger->logit("Port $port not found on switch $switch");
         return false;
      }
      return $snmp_port_index;
   }
   else
   {
      return false;
   }
}

/**
* Turn on a determined port identified by its index.
* @param mixed  $port_index             Port index according to SNMP
* @return boolean                       True if port was successfully switched on, false otherwise
*/
function turn_on_port($switch,$port,$port_index=false)
{
   global $snmp_rw,$snmp_port,$logger;
   if ($switch && $port)
   {
      $logger->debug("Turning on port $port on switch $switch",2);
      if ( ! $port_index)
      {
         if ( ! $port_index = get_snmp_port_index($switch, $port))
         {
            return false;
         }
      }
      #$oid=$snmp_port['ad_status'].'.'.$port_index;
      $oid='1.3.6.1.2.1.2.2.1.7.'.$port_index;
      $logger->debug("Setting $oid to 1 in $switch (Turning on)",3);
      if (!snmpset($switch,$snmp_rw,$oid,'i',1))
      {
         $logger->logit("Could not turn on $switch port index $port_index");
         return false;
      }
      else
      {
         return true;
      }
   }
   else
   {
      return false;
   }
}


/**
* Turn off a determined port identified by its index.
* @param mixed  $port_index             Port index according to SNMP
* @return boolean                       True if port was successfully switched off, false otherwise
*/
function turn_off_port($switch, $port, $port_index=false)
{
   global $snmp_rw,$snmp_port,$logger;
   if ($switch && $port)
   {
      $logger->debug("Turning off port $port on switch $switch",2);
      if ( ! $port_index)
      {
         if ( ! $port_index = get_snmp_port_index($switch, $port))
         {
            return false;
         }
      }
      #$oid=$snmp_port['ad_status'].'.'.$port_index;
      $oid='1.3.6.1.2.1.2.2.1.7.'.$port_index;
      $logger->debug("Setting $oid to 2 in $switch (Turning off)",3);
      if (!snmpset($switch,$snmp_rw,$oid,'i',2))
      {
         $logger->logit("Couldn't shut down port $port.");
         return false;
      }
      else
      {
         return true;
      }
   }
   else
   {
      return false;
   }
}

/**
* Remove the type of one element and leave only the value.
* This function is to be used when performing SNMP operations
* Example: INTEGER:33
           Returns: 33
* @param mixed $element         Element to remove type from
* @return mixed                 Value without type
*/
function remove_type($element)
{
   if ( ! $element )
      return false;
   else
      return trim(trim(substr($element,strpos($element,':')+1,strlen($element))),'"');
}

/**
* Ping a MAC address on a specific switch port
* @param mixed $mac     MAC address to ping
* @param mixed $switch  Switch we want to query
* @param mixed $port    switch port we want to query
* @param mixed $vlan    VLAN to use to perform the query
* @return boolean       True if MAC has been found on the switch port
*/
function ping_mac2($mac,$switch,$port,$vlan)
{
   global $logger;
   if (!$vlan)
      return false;
   $logger->debug("Querying if $mac is on port $port in switch $switch using vlan $vlan",2);
   if (is_mac_on_port($mac,$switch,$port,$vlan))
      return true;
   else
      return false;
}

/**
* Detect if a hub is attached to a certain port
* If a hub is detected, suggest another vlan to avoid port flapping.
* So far it is only an adaptation from the old algorithm
* It hasn't been tested yet
*/
function detect_hub ($REQUEST)
{
   global $logger, $conf;
   if ($conf->detect_hubs)
   {
      #Get vlan_groups
      $query = "SELECT vlan_group FROM vlan WHERE id='{$REQUEST->host->getNewVLAN_id()}';";
      $logger->debug($query,3);
      $new_vlan_group = v_sql_1_select($query);

      $query = "SELECT vlan_group FROM vlan WHERE id='{$REQUEST->host->getLastVLAN_id()}';";
      $logger->debug($query,3);
      $last_vlan_group = v_sql_1_select($query);

      if (($new_vlan_group) && ($last_vlan_group == $new_vlan_group))
      {
         #Stay with the existing vlan, to preserve connectivity
         $result = $lvlan_id;
      }
      else
      {
         #Use the normal vlan for this device
         $result = $nvlan_id;
      }

      $query=<<<EOF
SELECT sid, AuthVlan, AuthLast FROM vmpsauth WHERE
   TIME_TO_SEC(TIMEDIFF(NOW(), AuthLast)) < 7500 AND
   sid!='{$REQUEST->host->getSid()}' AND
   AuthVlan!='{$REQUEST->host->getNewVlan_id()}' AND
   AuthPort='{$REQUEST->switch_port->getPort_id()}' ORDER BY AuthLast DESC;
EOF;
      $logger->debug($query,3);
      $res = mysql_query($query);
      if (!$res)
         return false;
      if (mysql_num_rows($res) > 0)
      {
         while (list($othersid, $tempvlan, $authlast)=mysql_fetch_array($res,MYSQL_NUM))
         {
            $query = "SELECT mac FROM systems WHERE id='$othersid';";
            $logger->debug($query,3);
            $other_mac = v_sql_1_select($query);

            $query = "SELECT default_id FROM vlan WHERE id='$tempvlan';";
            $logger->debug($query,3);
            $other_vlan = v_sql_1_select($query);

            if (ping_mac2($other_mac, $REQUEST->switch_port->getSwitch_Name(), $REQUEST->switch_port->getPort_Name(),$other_vlan))
            {
               $query = "SELECT vlan_group FROM vlan WHERE id='$other_vlan';";
               $logger->debug($query,3);
               $other_vlan_group = v_sql_1_select($query);
               if (($other_vlan_group) && ($other_vlan_group == $new_vlan_group))
               {
                  $result = $other_vlan;
                  continue;
               }
               else
               {
                  $result=false;
               }
            }
         }
         return $result;
      }
   }
   else
   {
      return false;
   }
}

/**
* Wrapper around the restart_port script.
* Restart a switch port
* @param mixed $port    Port name
* @param mixed $switch  Switch
*/
function snmp_restart_port($port, $switch) {
  global $lastseen_sms_restart,$logger;
  #if ($lastseen_sms_restart)
  {
     /*$answer=syscall("./restart_port.php $port $switch");
     debug1($answer);
     logit("snmp_restart_port: $answer");*/
     if (turn_off_port($switch, $port) && turn_off_port($switch, $port))
        return turn_on_port($switch, $port);
     else
        return false;
  }
}


/**
* Wrapper around snmp_restart_port
* @param integer $port_id       ID of the port we want to restart
*/
function snmp_restart_port_id($port_id)
{
   global $logger;
   if (is_numeric($port_id) && ($port_id>0))
   {
      $query="select p.name as port, s.ip as switch from port p inner join switch s on p.switch=s.id where p.id='$port_id' limit 1;";
      $logger->debug($query,3);
      $result=mysql_fetch_one($query);
      if ($result)
      {
         $port=$result['port'];
         $switch=$result['switch'];
         return snmp_restart_port($port,$switch);
      }
      else
         return false;
   }
}

/**
* Tell whether a MAC address is on a certain port using SNMP
* @param mixed $mac     MAC to look for
* @param mixed $switch  Switch to look on
* @param mixed $port    Switch port to look on
* @param mixed $vlan    Vlan we'll use to look for that MAC address
* @return boolean       True if MAC found on that port, false otherwise
*/
function is_mac_on_port($mac,$switch,$port,$vlan)
{
   global $snmp_ro,$logger;                                     //Read Only community
   if (!$vlan)
      return false;
   $macs_on_vlan=@snmprealwalk($switch,"$snmp_ro@$vlan",'1.3.6.1.2.1.17.4.3.1.1');      //Obtain MAC address table
   if (empty($macs_on_vlan))
   {
      $logger->logit("Couldn't establish communication with $switch using the SNMP_RO community.");
      return false;
   }
   $macs_on_vlan=array_map("remove_type",$macs_on_vlan);
   $macs_on_vlan=array_map("normalise_mac",$macs_on_vlan);
   $mac_on=array_isearch($mac,$macs_on_vlan);                                           //Is this MAC in this switch?
   if (empty($mac_on))
      return false;                                                                     //No, return

   $bridge_port_number=@snmprealwalk($switch,"$snmp_ro@$vlan",'1.3.6.1.2.1.17.4.3.1.2'); //Yes, get bridge port number for vlan
   if (empty($bridge_port_number))
      return false;
   $bridge_port_number=array_map("remove_type",$bridge_port_number);
   $bridge_port=array_find_key($mac_on,$bridge_port_number,'.',5);                      //Where is this MAC?
   if (empty($bridge_port))
      return false;

   $map_bridge_port=@snmprealwalk($switch,"$snmp_ro@$vlan","1.3.6.1.2.1.17.1.4.1.2");   //Map the bridge port to the ifIndex
   if (empty($map_bridge_port))
      return false;
   $map_bridge_port=array_map("remove_type",$map_bridge_port);
   $map_bridge=array_find_key($bridge_port,$map_bridge_port,'.',1);                   //Get the one that is of interest to us
   if (empty($map_bridge))
      return false;

   $port_names=@snmprealwalk($switch,"$snmp_ro@$vlan","1.3.6.1.2.1.31.1.1.1.1");        //Get the name of the interfaces
   if (empty($port_names))
      return false;
   $port_names=array_map("remove_type",$port_names);
   $port_learnt=array_find_key($map_bridge,$port_names,'.',1);                          //What is the port name of this interface?
   if (strcmp($port_learnt,$port)==0)                                                   //Is this name equal to the one we provided??
      return true;                                                                      //Yes, the MAC is on this port
   else
      return false;                                                                     //No, MAC is not using this port
}

function ports_on_switch($switch)
{
   global $logger, $snmp_rw, $snmp_if, $snmp_ifaces;
   $oid = '1.3.6.1.2.1.31.1.1.1.1';
   if ($switch)
   {
      $logger->debug("Retrieving ports on $switch",2);
      #$logger->debug("Sending {$snmp_if['name']} to $switch",3);
      #$ports_on_switch=@snmprealwalk($switch,$snmp_rw,$snmp_if['name']);        //Get the list of ports on the switch

      $logger->debug("Sending $oid to $switch",3);
      $ports_on_switch=@snmprealwalk($switch,$snmp_rw,$oid);        //Get the list of ports on the switch
      if (empty($ports_on_switch))
      {
         $logger->logit("Couldn't establish communication with $switch with the defined parameters.");
         return false;
      }
      $ports_on_switch=array_map("remove_type",$ports_on_switch);               //We are only interested in the value
      $logger->debug(print_r($ports_on_switch,true),3);
     return $ports_on_switch;
   }
   else
   {
      return false;
   }
}

function vm_type($switch)
{
   global $logger, $snmp_rw, $snmp_port;
   if ($switch)
   {
      $logger->debug("Retrieving vlan membership types on $switch",2);
      $logger->debug("Sending {$snmp_port['type']} to $switch",3);
      $vm_type=@snmprealwalk($switch, $snmp_rw, $snmp_port['type']);
      if (empty($vm_type))
      {
         $logger->logit( "Couldn't establish communication with $switch with the defined parameters");
         return false;
      }
      $vm_type=array_map("remove_type",$vm_type);
      $logger->debug(print_r($vm_type,true),3);
      return $vm_type;
   }
   else
   {
      return false;
   }
}

function vlans_on_switch($switch)
{
   global $logger, $snmp_rw, $snmp_vlan;
   if ($switch)
   {
      $logger->debug("Retrieving vlans on $switch",2);
      $logger->debug("Sending {$snmp_vlan['name']} to $switch",3);
      $vlans_on_switch=@snmprealwalk($switch,$snmp_rw,$snmp_vlan['name']);         //Lookup of VLAN in the switch
      if (empty($vlans_on_switch))
      {
         $logger->logit( "Couldn't establish communication with $switch with the defined parameters");
         return false;
      }
      $vlans_on_switch=array_map("remove_type",$vlans_on_switch);
      $logger->debug(print_r($vlans_on_switch,true),3);
      return $vlans_on_switch;
   }
   else
   {
      return false;
   }
}

function get_snmp_index($what, $where)
{
   if ( ! $what || ! $where )
      return false;
   if ( ! is_array($where))
      return false;
   $what_oid=array_search($what,$where);                                //Is what we look for present in this array?
   if (empty($what_oid))
   {
      return false;
   }
   $what_index=get_last_index($what_oid);
   return $what_index;
}

function set_port_as_dynamic($switch,$port, $snmp_port_index=false)
{
   global $snmp_rw, $snmp_port, $logger;
   $logger->debug("Setting port $port on switch $switch to dynamic",2);
   if (! $snmp_port_index)
   {
      if ( ! $snmp_port_index = get_snmp_port_index($switch, $port))                   //Get port's index
      {
         return false;
      }
   }

   if (turn_off_port($switch, $port, $snmp_port_index))                                           //Shut down port to configure it
   {
      $oid=$snmp_port['type'].'.'.$snmp_port_index;
      $logger->debug("Setting $oid to 2 in $switch (dynamic)",3);
      if (@snmpset($switch,$snmp_rw,$oid,'i',2))                                   //Set port to dynamic
      {
         if (turn_on_port($switch, $port, $snmp_port_index))                                      //Done, turn it on
         {
            $logger->logit("Port $port on switch $switch successfully set to dynamic.");
            log2db('info',"Port $port on switch $switch successfully set to dynamic.");
            return true;
         }
         else
         {
            $logger->logit("Could not turn back on port $port on switch $switch");
            return false;
         }
      }
      else
      {
         $logger->logit("A communication problem with $switch occurred. Maybe $port is a trunk port?");
         turn_on_port($switch, $port, $snmp_port_index);
         return false;
      }
   }
   else
   {
      $logger->logit("Could not shut down port $port on switch $switch");
      return false;
   }
}

function set_port_as_static($switch, $port, $vlan,$snmp_port_index=false)
{
   global $snmp_rw, $snmp_port, $snmp_if, $logger;
   $logger->debug("Setting port $port to switch $switch as static with vlan $vlan",2);
   if (! $snmp_port_index)
   {
      if ( ! $snmp_port_index = get_snmp_port_index($switch, $port))                   //Get port's index
      {
         return false;
      }
   }

   if ( ! $vlans_on_switch=vlans_on_switch($switch))                            //Lookup of VLANs in the switch
   {
      return false;                                             # Error handling in vlans_on_switch
   }

   if ( ! $snmp_vlan_index = get_snmp_index($vlan, $vlans_on_switch))
   {
      $logger->logit("Vlan $vlan not found on switch $switch");
      return false;
   }

   if (turn_off_port($switch, $port, $snmp_port_index))                                      //Shut down port to configure it
   {
      $oid=$snmp_port['type'].'.'.$snmp_port_index;
      $logger->debug("Setting $oid to 1 in $switch (Static)",3);
      if (@snmpset($switch,$snmp_rw,$oid,'i',1))                              //Set port to static
      {
         $oid=$snmp_if['vlan'].'.'.$snmp_port_index;
         $logger->debug("Setting $oid in $switch (VLAN)",3);
         if (snmpset($switch,$snmp_rw,$oid,'i',$snmp_vlan_index))                       //And set the VLAN on that port
         {
            if (turn_on_port($switch, $port, $snmp_port_index))                                           //Done, turn it on
            {
               $logger->logit("Port $port on switch $switch successfully set to static with vlan $vlan");
               log2db('info',"Port $port on switch $switch successfully set to static with vlan $vlan");
               return true;
            }
            else
            {
               $logger->logit("Could not turn back on port $port on switch $switch");
               return false;
            }
         }
         else
         {
            $logger->logit("A communication problem with $switch occurred");
            return false;
         }
      }
      else
      {
         $logger->logit("A communication problem with $switch occurred");
         return false;
      }
   }
   else
   {
      $logger->logit("Could not shut down port $port on switch $switch");
      return false;
   }
}

/*
 * is_port_vmps() is this a vmps candidate port?
 * Assume that if its not a trunk, iis physical, and the interface i
 * name starts with Fa,Gi or X/YY, then its a candidate.
 */
function is_port_vmps($myiface) 
{
   if ( ! isset($myiface['phys']) || ! isset($myiface['trunk']) || ! isset($myiface['type']) || ! isset($myiface['name']) )
      return false;
   if ( ($myiface['phys'] == 1) && ($myiface['trunk'] != 1) && ($myiface['type'] == 2) &&
      (( stristr($myiface['name'],'Fa') || stristr($myiface['name'],'Gi') ||
      preg_match("/\d+\/\d+/", $myiface['name'])     )) )
      {
         debug2(" int=" .$myiface['name']
           .', vlan=' .$myiface['vlan'] .', trunk= ' .$myiface['trunk']
           .', phys=' .$myiface['phys'] .', vmps=YES' );
         return(TRUE);
      } 
      else 
      {
         debug2(" int=" .$myiface['name']
           .', vlan=' .$myiface['vlan'] .', trunk= ' .$myiface['trunk']
           .', phys=' .$myiface['phys'] .', vmps=NO' );
         return(FALSE);
      };
};

function walk_ports($switch,$snmp_ro)
{
   snmp_set_oid_numeric_print(TRUE);
   snmp_set_quick_print(TRUE);
   snmp_set_enum_print(TRUE); 
   global $snmp_ifaces; // query to get all interfaces
   global $snmp_if; // sub-queries with interfaces characteristics
   global $snmp_port;
   global $logger;
#	ob_start("callback"); 
   $iface = array();
   $iface_from_db=array();
   debug2("snmprealwalk $switch $snmp_ro $snmp_ifaces");
   
   // Read the list of interfaces present on the switch
   $ifaces = @snmprealwalk($switch,$snmp_ro,$snmp_ifaces);

   /* The following is to delete old ports present in the database but not on the switch.
   *  Suppose you have the following situation:
   *  You buy a new switch and replace an old one with this new one.
   *  You give to this switch the same IP address the old one had.
   *  The script should document the ports found on the new switch and delete all ports belonging to the old one. 
   */
   // Escape the switch ip for use in MySQL queries 
   $switch = mysql_real_escape_string($switch);
   // Retrieve the list of ports associated to this switch from the database
   $query = "SELECT p.id, p.name FROM port p INNER JOIN switch s ON p.switch=s.id WHERE s.ip='$switch' OR s.name='$switch'";
   $logger->debug($query, 3);

   $res = mysql_query($query);
   $counter = 0;

   if ( ! $res )
   {
      $logger->logit(mysql_error());
   }
   else
   {
      while ($row = mysql_fetch_array($res))
      {
         $iface_from_db[$counter]['id'] = $row['id'];
         $iface_from_db[$counter]['name'] = $row['name'];
         $counter++;
      }
   }

   // Have we retrieved any records from the database?
   if ( $counter > 0 )
   {
      // Yes, then check if the ports defined in the database are on the switch
      $ids_to_delete = array();

      foreach ( $iface_from_db as $k => $array)
      {
         // Is this port on the switch?
         if ( ! array_search($array['name'], $ifaces) )
         {
            // No, then mark it for deletion
            $ids_to_delete[] = $array['id'];
         }         
      }

      // Have we marked any ports to delete?
      if ( count($ids_to_delete) > 0 )
      {
         foreach ( $ids_to_delete as $id )
         {
            // Build the query to delete the obsolete port from the port table 
            $query = "DELETE FROM port WHERE id='$id';";
            $logger->debug($query, 3);
            // And actually delete it
            $res = mysql_query($query);
            if ( ! $res )
               $logger->logit(mysql_error());
         }
      }
   }

   // Obsolete ports in the database belonging to this switch shouldn't exist anymore from this point on 

   if ((count($ifaces) == 0) || !(is_array($ifaces))) { return($iface); };

   foreach ($ifaces as $oid => $name) 
   {
      $oids = explode('.',$oid);
      $idx = $oids[12];
      if ($idx > 0 && ($oids[7] == '31')) 
      {
         $iface[$idx]['id'] = $idx;
         $index[] = $idx;
      };
   };
   unset($idx);

   if (count($index) > 0) 
   {
      foreach ($snmp_if as $field => $query) 
      {
         foreach($index as $idx) 
         {
            $iface[$idx][$field] = '';
         };
         debug2("snmprealwalk $switch $query");	
         $walk = snmprealwalk($switch,$snmp_ro,$query);
         foreach ($walk as $oid => $value) 
         {
            $oids = explode('.',$oid);
            $idx = $oids[count($oids)-1];
            $iface[$idx][$field] = $value;
         };
         unset($walk);

      };
	
      foreach ($iface as $idx => $myiface) 
      {
         $iface[$idx]['vmps'] = is_port_vmps($myiface);
      };

  
// big debug
/*
	foreach ($iface as $idx => $myiface) {
		foreach ($myiface as $key => $value) {
		echo $value."\t";
		};
	echo "\n";
	};
*/
	
   };

   if (count($index) > 0) 
   {
      foreach ($snmp_port as $field => $query) 
      {
         foreach($index as $idx) 
         {
            $iface[$idx][$field] = '';
         };
         debug2("snmprealwalk $switch $query");
         $walk = snmprealwalk($switch,$snmp_ro,$query);
         foreach ($walk as $oid => $value) 
         {
            $oids = explode('.',$oid);
            $idx = $oids[count($oids)-1];
            $iface[$idx][$field] = $value;
         };
         unset($walk);
      };

      foreach ($iface as $idx => $myiface) 
      {
         $iface[$idx]['vmps'] = is_port_vmps($myiface);
      };

   };

   #ob_flush();
   return($iface);
};

function mac_exist($mac) 
{
   global $connect, $logger;
   $mac=strtolower($mac);
   $query = "SELECT * FROM systems WHERE mac='$mac'";
   $result = mysql_query($query);
   if (! $result)
   {
      $logger->logit("Unable to query systems table", LOG_ERR);
      exit(1);
   } 
   if (mysql_num_rows($result) > 0) 
   {
      $row = mysql_fetch_array($result);
      return($row['id']);
   } 
   else 
   {
      return(FALSE);
   };
};

function iface_exist($switchid,$portname) 
{
   global $connect, $logger;
   $query = "SELECT * FROM port WHERE switch=$switchid AND name='$portname'";
   $result = mysql_query($query);
   if ( ! $result)
   {
      $logger->logit("Unable to query port table",LOG_ERR);
      exit(1);
   } 
   if (mysql_num_rows($result) > 0) 
   {
      $row = mysql_fetch_array($result);
      return($row['id']);
   }
   else 
   {
      return(FALSE);
   };
};


function switch_exist($name,$value) 
{
   global $connect, $logger;
   $query = "SELECT * FROM switch WHERE $name='$value'";
   $result = mysql_query($query);
   if ( ! $result)
   {
      $logger->logit("Unable to query switch table", LOG_ERR);
      exit(1);
   } 
   if (mysql_num_rows($result) > 0) 
   {
      $row = mysql_fetch_array($result);
      return($row['id']);
   }
   else 
   {
      return(FALSE);
   };
};

function get_vlanid($default_id) 
{
   global $connect, $logger;
   $query = "SELECT id FROM vlan WHERE default_id='$default_id'";
   $result = mysql_query($query);
   if ( ! $result)
   {
      $logger->logit("Unable to query vlan table", LOG_ERR);
      exit(1);
   } 
   if (mysql_num_rows($result) > 0) 
   {
      $vlan = mysql_fetch_array($result);
      return($vlan['id']);
   }
   else 
   {
      return(FALSE);
   };
};

function get_cisco_info($switch,$snmp_ro) 
{
   global $snmp_sw;
   // will return an array with name, hardware, software, catos
   if (!empty($snmp_sw))
   {
      foreach ($snmp_sw as $field => $query) 
      {
         debug2("snmpget $switch $query");
         $sw[$field] = snmpget($switch,$snmp_ro,$query);
      };
      // get short name
      $names = explode('.',$sw['name']);
      $sw['shortname'] = $names[0];

      // parse description field
      if (stristr($sw['descr'],'cisco')) 
      {
         $words = explode(' ',$sw['descr']);
         foreach($words as $idx => $word) 
         {
            // first, version
            if (stristr($word,'Version')) 
            {
               $sw['cisco_sw'] = rtrim($words[$idx+1],',');
            }; 
            if (stristr($word,'IOS')) 
            {
               $sw['catos'] = FALSE;
            };
         };
         // then hardware
         debug2("snmprealwalk $switch ".$snmp_cisco['hw']);	
         $hw_versions = snmprealwalk($switch,$snmp_ro,$snmp_cisco['hw']);
         foreach ($hw_versions as $value) 
         {
            if (strstr($value,'WS')) 
            {
               $sw['cisco_hw'] = rtrim(ltrim($value,'"'),'"');
            };
         };
         unset($words);		
      };

      return(array($sw['shortname'],$sw['cisco_hw'],$sw['cisco_sw'],$sw['catos']));
   }
   else return;
};

function format_snmpmac($mac) 
{
   // input  = "00 02 44 45 9B FE "
   // output = 0002.4445.9BFE
   $mac = rtrim(ltrim($mac,'"'),'"');
   $mb = explode(' ',$mac);
   $newmac = $mb[0].$mb[1].'.'.$mb[2].$mb[3].'.'.$mb[4].$mb[5];
   return(strtolower($newmac));
};

function walk_macs($switch,$vlanid,$snmp_ro) 
{
   snmp_set_oid_numeric_print(TRUE);
   snmp_set_quick_print(TRUE);
   snmp_set_enum_print(TRUE); 
   global $snmp_mac;
   global $snmp_bridge;
   global $snmp_ports;
   global $switch_ifaces;
   #ob_start("callback"); 

   $snmp_ro_vlan = $snmp_ro.'@'.$vlanid;

   $iface = array();
   debug2("snmprealwalk $switch $snmp_ro_vlan $snmp_mac");
   $macs = @snmprealwalk($switch,$snmp_ro_vlan,$snmp_mac);
        
   $mac=array();
   $mac2=array();

   if ((count($macs) == 0) || !(is_array($macs)))  { return($mac); };

   foreach ($macs as $oid => $macaddress) 
   {
      $oids = explode('.',$oid);
      if ( isset($oids[12]) && isset($oids[13]) && isset($oids[14]) && isset($oids[15]) && isset($oids[16]) && isset($oids[17]) )
      {
         $idx = $oids[12].'.'.$oids[13].'.'.$oids[14].'.'.$oids[15].'.'.$oids[16].'.'.$oids[17];
         $mac[$idx]['mac'] = format_snmpmac($macaddress);
         $mac[$idx]['bridgeref'] = $idx;
      }
   };
   unset($idx);


    debug2("snmprealwalk $switch $snmp_ro_vlan $snmp_bridge");
    $bridges = snmprealwalk($switch,$snmp_ro_vlan,$snmp_bridge);

   if (count($bridges) == 0) { return($mac); };

   foreach($bridges as $oid => $bridgeid) 
   {
      $oids = explode('.',$oid);
      if ( isset($oids[12]) && isset($oids[13]) && isset($oids[14]) && isset($oids[15]) && isset($oids[16]) && isset($oids[17]) )
         $idx = $oids[12].'.'.$oids[13].'.'.$oids[14].'.'.$oids[15].'.'.$oids[16].'.'.$oids[17];
      if (isset($mac[$idx])) 
      {
         $mac2[$bridgeid] = $mac[$idx];
      };
      #echo "$bridgeid - $idx - ".$mac[$idx]['mac']."\n";
   };

   debug2("snmprealwalk $switch $snmp_ro_vlan $snmp_ports");
   $ports = snmprealwalk($switch,$snmp_ro_vlan,$snmp_ports);

   if (count($bridges) == 0) { return($mac); };

   foreach($ports as $oid => $portid) 
   {
      $oids = explode('.',$oid);
      if (strcmp($oids[0],$oid)===0)
         continue;
      $idx = $oids[12];
      if (isset($mac2[$idx])) 
      {
         $mac2[$idx]['portid'] = $portid;
         $mac2[$idx]['port'] = $switch_ifaces[$portid]['name'];
         $mac2[$idx]['trunk'] = $switch_ifaces[$portid]['trunk'];
      };
   };
/*
   foreach ($mac2 as $key => $value) 
   {			
      echo "$vlanid - $key : \t";
      foreach ($value as $k2 => $v2) 
      {
         echo $k2.' = '.$v2."\t";
      };
      echo "\n";
   };
*/

   #ob_flush();
   return($mac2);
};


function walk_vlans($switch,$snmp_ro) 
{
   snmp_set_oid_numeric_print(TRUE);
   snmp_set_quick_print(TRUE);
   snmp_set_enum_print(TRUE); 
   global $snmp_vlan;
   #ob_start("callback"); 
   $vlans = array();

   foreach ($snmp_vlan as $key => $query) 
   {
      debug2("snmprealwalk $switch $query");
      $listvlans = snmprealwalk($switch,$snmp_ro,$query);

      if (count($listvlans) == 0) { return($vlans); };
      if (is_array($listvlans)) 
      {	
         foreach ($listvlans as $oid => $value) 
         {
            $oids = explode('.',$oid);
            $idx = $oids[count($oids)-1];
            if ($key == 'name') { $value = rtrim(ltrim($value,'"'),'"'); };
            $vlans[$idx][$key] = $value;
            unset($value);
         };
      };
   };
   ob_flush();
   return($vlans);
};

function walk_switchhw($switch,$snmp_ro) 
{
   global $snmp_sw;
   $hw_versions = @snmprealwalk($switch,$snmp_ro,$snmp_sw['ciscohw']);
   $cisco_hw = false;
   if ((!empty($hw_versions))&&(count($hw_versions)>0))
   {
      foreach ($hw_versions as $value) 
      {
         if (strstr($value,'WS')) 
         {
            $cisco_hw = rtrim(ltrim($value,'"'),'"');
          };
      };
      return($cisco_hw);
   }
   else return;
};


function walk_switchsw($switch,$snmp_ro) 
{
   global $snmp_sw;

   $descr = snmpwalk($switch,$snmp_ro,$snmp_sw['descr']);
   if (!$descr)
      return false;
   #$cisco_sw = snmpget($switch,$snmp_ro,$snmp_sw['soft_version']);
   $words = explode(' ',$descr[0]);
   foreach($words as $idx => $word) 
   {
      if (stristr($word,'Version')) 
      {
         $cisco_sw = rtrim($words[$idx+1],',');
         if (stristr($cisco_sw,"\n")) 
         {
            $parts = explode("\n",$cisco_sw);
            $cisco_sw = $parts[0];
         };
      }; 
   };
   return($cisco_sw);
};


?>
