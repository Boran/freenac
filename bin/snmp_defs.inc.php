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


/*
 * is_port_vmps() is this a vmps candidate port?
 * Assume that if its not a trunk, iis physical, and the interface i
 * name starts with Fa,Gi or X/YY, then its a candidate.
 */
function is_port_vmps($myiface) 
{
   if ( ($myiface['phys'] == 1) && ($myiface['trunk'] != 1) && ($myiface['type'] == 2) &&
      (( stristr($myiface['name'],'Fa') || stristr($myiface['name'],'Gi') ||
      preg_match("/\d+\/\d+/", $myiface['name'])     )) )
      {
         debug2("$switchname int=" .$myiface['name']
           .', vlan=' .$myiface['vlan'] .', trunk= ' .$myiface['trunk']
           .', phys=' .$myiface['phys'] .', vmps=YES' );
         return(TRUE);
      } 
      else 
      {
         debug2("$switchname int=" .$myiface['name']
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
#	ob_start("callback"); 
   $iface = array();
   debug2("snmprealwalk $switch $snmp_ro $snmp_ifaces");
   $ifaces = @snmprealwalk($switch,$snmp_ro,$snmp_ifaces);

   if ((count($ifaces) == 0) || !(is_array($ifaces))) { return($iface); };

   foreach ($ifaces as $oid => $name) 
   {
      $oids = explode('.',$oid);
      $idx = $oids[12];
      if ($idx > 0 && ($oids[7] == '31')) 
      {
         $iface[$id]['id'] = $idx;
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
      $idx = $oids[12].'.'.$oids[13].'.'.$oids[14].'.'.$oids[15].'.'.$oids[16].'.'.$oids[17];
      $mac[$idx]['mac'] = format_snmpmac($macaddress);
      $mac[$idx]['bridgeref'] = $idx;
   };
   unset($idx);


    debug2("snmprealwalk $switch $snmp_ro_vlan $snmp_bridge");
    $bridges = snmprealwalk($switch,$snmp_ro_vlan,$snmp_bridge);

   if (count($bridges) == 0) { return($mac); };

   foreach($bridges as $oid => $bridgeid) 
   {
      $oids = explode('.',$oid);
      $idx = $oids[12].'.'.$oids[13].'.'.$oids[14].'.'.$oids[15].'.'.$oids[16].'.'.$oids[17];
      if ($mac[$idx]) 
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
      $idx = $oids[12];
      if ($mac2[$idx]) 
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
