#!/usr/bin/php -f
<?php
/**
 * /opt/nac/contrib/snmp_import.php
 *
 * Long description for file:
 * - Get all current active computers and insert them into the systems table
 * - Get all switch port and current vlan and insert them into the ports table
 *
 * On IOS do "show ip arp" - "show vlan"
 *        or "sh ip arp vrf insec"
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
 * @copyright			2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link			http://www.freenac.net
 *
 */

$debug_flag1=false;
$debug_flag2=false;

include_once "../bin/funcs.inc";               # Load settings & common functions
include_once "snmp_defs.inc";
$debug_to_syslog=TRUE;

define_syslog_variables();              # not used yet, but anyway..
openlog("import_snmp", LOG_PID, LOG_LOCAL5);

snmp_set_oid_numeric_print(TRUE);
snmp_set_quick_print(TRUE);
snmp_set_enum_print(TRUE); 

// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;

db_connect();



$flagscannow=0;
for ($i=0;$i<$argc;$i++) {
// TODO switch instead of if
   if ($argv[$i]=="--switch") {
	  $single = TRUE;
	  $singleswitch = $argv[$i+1];
	};
};


if (!$single) {
	$switches =  mysql_fetch_all("SELECT * FROM switch");
} else {
	$switches =  mysql_fetch_all("SELECT * FROM switch WHERE name='$singleswitch'");
};

if (is_array($switches)) {

	foreach ($switches as $switchrow) {
		$switch = $switchrow['ip'];
		$switchname = $switchrow['name'];
		$location = $switchrow['location'];
	        debug2("snmpwalk $switch for interfaces");

		$ifaces = walk_ports($switch,$snmp_ro);
  	    if (count($ifaces) > 0) {
		 foreach ($ifaces as $idx => $myiface) {
			if ($myiface['vmps'] && $myiface['vlan'] > 0) {
				if (iface_exist($switch,$myiface['name'])) {
					$query = "UPDATE port SET default_vlan='".$myiface['vlan']."' WHERE ";
					$query .= "switch='$switch' AND name='".$myiface['name']."';";
				} else {
					$query = "INSERT INTO port(switch,name,default_vlan,location) VALUES (";
					$query .= "'$switch','".$myiface['name']."','".$myiface['vlan']."','$location');";

				};
				echo "$query\n";
			//        mysql_query($query) or die("unable to query");
				unset($query);
			};
		 };
        };
		unset($ifaces);
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


?>
