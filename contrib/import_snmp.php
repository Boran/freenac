#!/usr/bin/php -f
<?php
/**
 * /opt/nac/contrib/import_snmp
 *
 * Long description for file:
 * - Get all current active computers and insert them into the systems table
 * - Get all switch port and current vlan and insert them into the ports table
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
 * @package			FreeNAC
 * @author			Thomas Dagonnier - Sean Boran (FreeNAC Core Team)
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link			http://www.freenac.net
 *
 */

$debug_flag1=false;
$debug_flag2=false;

require_once "../bin/funcs.inc";               # Load settings & common functions
require_once "../etc/config.inc";  
$debug_to_syslog=FALSE;
$logit_to_stdout = FALSE;

define_syslog_variables();              # not used yet, but anyway..
openlog("import_snmp", LOG_PID, LOG_LOCAL5);

snmp_set_oid_numeric_print(TRUE);
snmp_set_quick_print(TRUE);
snmp_set_enum_print(TRUE); 

// following should be arguments / config / database;
$snmp_ro = 'public';
$set_default_vlan = TRUE;

// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;

db_connect();
global $connect, $core_routers, $router_mac_ip_ignore_ip, $router_mac_ip_ignore_mac, $snmpwalk;
global $router_msc_ip_update_from_dns;


$snmp_sw['descr'] = 		'SNMPv2-MIB::sysDescr.0';
$snmp_sw['name'] = 			'SNMPv2-MIB::sysName.0';
$snmp_sw['location'] = 		'SNMPv2-MIB::sysLocation.0';
$snmp_sw['contact'] = 		'SNMPv2-MIB::sysContact.0';

$snmp_ifaces = 				'1.3.6.1.2.1.31.1.1.1.1';
$snmp_cisco['hw'] = 		'SNMPv2-SMI::mib-2.47.1.1.1.1.13';

$snmp_if['name'] = 			'1.3.6.1.2.1.31.1.1.1.1'; 
$snmp_if['highspeed'] = 	'1.3.6.1.2.1.31.1.1.1.15';				//	10 - 100 - 1000 
$snmp_if['description'] = 	'1.3.6.1.2.1.31.1.1.1.18';
$snmp_if['phys'] = 			'1.3.6.1.2.1.31.1.1.1.17';				// true - false
$snmp_if['trunk'] = 		'1.3.6.1.4.1.9.9.46.1.6.1.1.13';		//	1 : on	2 : off	3 : desirable	4 : auto	5 : onNoNegotiate
$snmp_if['vlan'] = 			'1.3.6.1.4.1.9.9.68.1.2.2.1.2';

// http://tools.cisco.com/Support/SNMP/do/BrowseOID.do for details

/* 
	Some SNMP requests

1.3.6.1.2.1.31.1.1.1.1 => short port names
IF-MIB::ifName.10116 = STRING: Gi1/0/16

1.3.6.1.4.1.9.9.68.1.2.2.1.2 => vlan id
SNMPv2-SMI::enterprises.9.9.68.1.2.2.1.2.10116 = INTEGER: 523

1.3.6.1.2.1.31.1.1.1.17 => true or false / if physical or virtual port
IF-MIB::ifConnectorPresent.10116 = INTEGER: true(1)

1.3.6.1.4.1.9.9.46.1.6.1.1.13 => trunk (1) or not (2) - 4 = 
SNMPv2-SMI::enterprises.9.9.46.1.6.1.1.13.10116 = INTEGER: 2

*/


$switches = mysql_fetch_all("SELECT * FROM switch;");

foreach ($switches as $switchrow) {
	$switch = $switchrow['ip'];
	$switchname = $switchrow['name'];
	$location = $switchrow['location'];

//	debug2("snmpwalk $switch for interfaces");

	$ifaces = snmprealwalk($switch,$snmp_ro,"1.3.6.1.2.1.31.1.1.1.1");

	foreach ($ifaces as $oid => $name) {
			$oids = explode('.',$oid);
			$idx = $oids[12];
			$iface[$id]['id'] = $idx;
			$index[] = $idx;
	};
	unset($idx);

	foreach ($snmp_if as $field => $snmp_query) {
		foreach($index as $idx) {
			$iface[$idx][$field] = '';
		};
		$walk = snmprealwalk($switch,$snmp_ro,$snmp_query);
		foreach ($walk as $oid => $value) {	
			$oids = explode('.',$oid);
			$idx = $oids[count($oids)-1];
			$iface[$idx][$field] = $value;
		};
		unset($walk);
	};

	foreach ($iface as $idx => $myiface) {
	// first, check if we have a real interface
		if (($myiface['phys'] == 1) && ($myiface['trunk'] != 1) &&
				((stristr($myiface['name'],'Fa') || stristr($myiface['name'],'Gi')))) {
		// then, if we have a real default vlan & we were called with "set_default_vlan"
			if ($set_default_vlan === TRUE) {
				if ($myiface['vlan'] > 1) {
						$vlan = $myiface['vlan'];
				} else {
						$vlan = $default_vlan;
				};
				$query .= "INSERT INTO port(switch,name,default_vlan,location) VALUES ('$switch','".$myiface['name']."','$vlan','$location');\n";	
			} else {
				$query .= "INSERT INTO port(switch,name,location) VALUES ('$switch','".$myiface['name']."','$location');\n";	
			};
		mysql_query($query) or die ("Unable to insert ports into table\n");
		unset($query);		
		};
	};
};



  // measure performance
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   debug1("Time taken= ".$totaltime." seconds\n");
   #logit("Time taken= ".$totaltime." seconds\n");

exit(0);

?>
