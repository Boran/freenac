<?php
/**
 * /opt/nac/contrib/ip_migrate
 *
 * Migrate IP adresses to new table :
 *  1. Get all networks documented in subnet and add all adresses to the (new) ip table
 *  2. Update IP table by linking newly created adresses based with discovered ip adresses (systems.r_ip)
 *  3. Update IP table by integrating information about fixed ip adresses (system.dhcp_fix)
 *
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Thomas Dagonnier (FreeNAC Core Team)
 * @copyright			2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */
include_once('/opt/nac/web/webfuncs.inc');
include_once('/opt/nac/etc/config.inc');

db_connect($dbuser,$dbpass);

// First, create ip adresses for all subnets
// (fill table 'ip' based on table 'subnets')

$query = "SELECT id, ip_address as address, ip_netmask as mask FROM subnets ORDER BY ip_address";
$result = mysql_query($query);

while ($subnet = mysql_fetch_array($result)) {
	$id = $subnet['id'];
// network start
	$astart = mysql_fetch_array(mysql_query("SELECT INET_ATON('".$subnet['address']."');"));
	$start = $astart[0];
	$ins0 = "INSERT INTO ip(address,subnet,status,comment,source) VALUES ";
	$ins = $ins0."($start,$id,-1,'Initial import','ip_migrate');";
	mysql_query($ins);
// broadcast
	$bcast = $start + pow(2,(32-$subnet['mask'])) -1 ;
        $ins_cast = $ins0."($bcast,$id,-1,'Initial import','ip_migrate');";
	echo "Importing ".$subnet['address'] ." : $start to $bcast (".($bcast - $start).")\n";
// and fill in betwee
	for ($ip = $start+1; $ip < $bcast; $ip++) {
		$ins = $ins0."($ip,$id,0,'Initial import','ip_migrate');";
		mysql_query($ins);
//		echo $ins;
	};
	mysql_query($ins_cast);
};

// Second, update know systems : r_ip (we suppose all r_ip comes from router)
$query = "SELECT id, INET_ATON(r_ip) as ip FROM systems WHERE r_ip != ''";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
	$ip_id = mysql_fetch_array(mysql_query("SELECT id FROM ip WHERE address = ".$row['ip']));
	$upd_sys = "UPDATE systems SET r_ip = ".$ip_id[0]." WHERE id = ".$row['id'].";";
	$upd_ip = "UPDATE ip SET system= ".$row['id'].",status=1,source='snmp_router' WHERE id=".$ip_id[0].";";
	echo $upd_sys."\n";
	echo $upd_ip."\n";
	mysql_query($upd_ip);
};

// Third, update fixed ip : if dhcp_fix=1, set dhcp_ip to ip.id (& upd record)

$query = "SELECT id, INET_ATON(dhcp_ip) as ip FROM systems WHERE dhcp_ip != '' AND dhcp_fix = 1";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
        $ip_id = mysql_fetch_array(mysql_query("SELECT id FROM ip WHERE address = ".$row['ip']));
        $upd_sys = "UPDATE systems SET dhcp_ip = ".$ip_id[0]." WHERE id = ".$row['id'].";";
        $upd_ip = "UPDATE ip SET system= ".$row['id'].",status=2,source='snmp_router' WHERE id=".$ip_id[0].";";
        echo $upd_sys."\n";
        echo $upd_ip."\n";
        mysql_query($upd_ip);
//	mysql_query($upd_sys);
};


