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

include_once "../bin/funcs.inc";               # Load settings & common functions
$debug_to_syslog=TRUE;

define_syslog_variables();              # not used yet, but anyway..
openlog("import_snmp", LOG_PID, LOG_LOCAL5);

snmp_set_oid_numeric_print(TRUE);
snmp_set_quick_print(TRUE);
snmp_set_enum_print(TRUE); 

// communities --- SHOULD BE MOVED TO CONFIG.INC OR DATABASE
$snmp_ro = 'SNMPRO';

// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;

db_connect();
global $connect, $core_routers, $router_mac_ip_ignore_ip, $router_mac_ip_ignore_mac, $snmpwalk;
global $router_msc_ip_update_from_dns;

$query_name = '1.3.6.1.2.1.31.1.1.1.1';
$query_vlan = '1.3.6.1.4.1.9.9.68.1.2.2.1.2';
$query_trunk = '1.3.6.1.4.1.9.9.46.1.6.1.1.13';
$query_phys = '1.3.6.1.2.1.31.1.1.1.17';
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
function get_phys($switch,$port_index) {
        global $snmpwalk;
	global $snmp_ro;
        global $query_phys;

        $query = $query_phys.'.'.$port_index;
#echo "snmpget($switch,$snmp_ro,$query) \n";
        $value = snmpget($switch,$snmp_ro,$query);
        if ($value == 1) {
	  return(TRUE);
        } else {
          return(FALSE);
        };
};
function get_trunk($switch,$port_index) {
        global $snmpwalk;
	global $snmp_ro;
        global $query_trunk;

        $query = $query_trunk.'.'.$port_index;
#echo "snmpget($switch,$snmp_ro,$query) \n";
        $value = snmpget($switch,$snmp_ro,$query);
	if ($value == 1) {
		return(TRUE);
	} else {
		return(FALSE);
	};

};

function get_vlan($switch,$port_index) {
	global $snmpwalk;
	global $query_vlan;
	global $snmp_ro;

	$query = $query_vlan.'.'.$port_index;
#echo "snmpget($switch,$snmp_ro,$query) \n";
	$vlan_id = snmpget($switch,$snmp_ro,$query);
	if ($vlan_id && $vlan_id > 0) {
          return($vlan_id);
	} else {
	  return(FALSE);
	};
};

$switches = mysql_fetch_all("SELECT * FROM switch;");

foreach ($switches as $switchrow) {
	$switch = $switchrow['ip'];
	$switchname = $switchrow['name'];
	$location = $switchrow['location'];

// first, get all interfaces

	debug2("snmpwalk $switch for interfaces");
	//$ifaces=explode("\n", syscall("$snmpwalk $switch $query_name"));
	$ifaces = snmprealwalk($switch,'public',"1.3.6.1.2.1.31.1.1.1.1");

	foreach ($ifaces as $oid => $name) {

		$oids = explode('.',$oid);
		$idx = $oids[12];
		$vlan = 0;


// next is to quickly filter out all non ethernet interfaces
		if (stristr($name,'Gi') || stristr($name,'Fa')) {
// the following line gives warning on 3500XL TODO FIX
			if (get_trunk($switch,$idx)) {
			} else {
//				if (get_phys($switch,$idx)) {
					$vlan = get_vlan($switch,$idx);
					if ($vlan > 0) {
						$query .= "INSERT INTO port(switch,name,default_vlan,location) VALUES ('$switch','$name','$vlan','$location');\n";
					};
//				};
			};
		};
	};
	// mysql_query($query) or die("Unable to insert into table");
	echo $query;
};

exit();


  // measure performance
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   debug1("Time taken= ".$totaltime." seconds\n");
   #logit("Time taken= ".$totaltime." seconds\n");


?>
