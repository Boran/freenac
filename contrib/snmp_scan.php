#!/usr/bin/php -- -f
<?php
/**
 * contrib/snmp_scan.php
 *
 * Long description for file:
 * This script will scan all existing switches & routers using SNMP
 * It focuses on getting informations about systems who are not managed
 * or/and on static access ports. 
 * Such systems are typically critical servers, network equipment, ...
 * It is complementary to the snmp_import script
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


# Php weirdness: change to script dir, then look for includes
chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "bin/funcs.inc";               # Load settings & common functions
require_once "snmp_defs.inc";

define_syslog_variables();              # not used yet, but anyway..
openlog("snmp_import.php", LOG_PID, LOG_LOCAL5);

db_connect();


// Enable debugging to understand how the script works
  $debug_flag1=false;
  $debug_flag2=false;
  $debug_to_syslog=FALSE;
// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;


if ($snmp_dryrun) {
  $debug_flag2=true;
  $domysql=false;
} else {
  $domysql=true;
};


    $vlans =  mysql_fetch_all("SELECT * FROM vlan");
    $switches =  mysql_fetch_all("SELECT * FROM switch");

#	$switch = '192.168.254.7';
	$vlans = array(520,521,522,523,524,525,526);
#	$vlans = array(523);

	foreach ($switches as $switchrow) {
		$switch = $switchrow['ip'];
		$switch_ifaces = walk_ports($switch,'public');
		foreach ($vlans as $vlan) {
			$macs = walk_macs($switch,$vlan,$snmp_ro);
			foreach ($macs as $idx => $mac) {
				if($mac['trunk'] != 1) {
					if (mac_exist($mac['mac'])) {
						$query = "UPDATE systems SET switch='$switch', port='".$mac['port']."', LastSeen=NOW() ";
						$query .= "WHERE mac='".$mac['mac']."';";
						debug2($switch." - ".$mac['port']." - ".$mac['mac']." - Insert host ");
					} else {
						$query = 'INSERT INTO systems (name, mac, switch, port, vlan, status) VALUES ';
						$query .= "('unknown','".$mac['mac']."','$switch','".$mac['port']."',$vlan,3);";
						debug2($switch." - ".$mac['port']." - ".$mac['mac']." - Update host ");
					};
					if($domysql) { mysql_query($query) or die("unable to query"); };
					unset($query);
				};
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
?>
