<?php
/**
 * /opt/nac/contrib/generate_dhcp.php
 *
 * Generate DHCP configuration file from the FreeNAC database
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
chdir(dirname(__FILE__));
set_include_path("../:./");
require_once('../bin/funcs.inc.php');

db_connect($dbuser,$dbpass);


$dhcp_preamble = "
#
# DON'T EDIT THIS FILE - Generated automatically from FreeNAC 
#
# You can edit
# - the default in the configuration (dhcp_default variable)
# - subnets and their options in dhcp_subnets and dhcp_options
# - fix IP adresses trough the system/ip table (main view)
# - reserve IP adresse trough the ip table (*** view)
#
#\n";



// Preamble, default configuration
$dhcp_preamble .= $conf->dhcp_default."\n\n";

// DHCP -> dynamic DNS Updates

if ($conf->dhcp_ddns) {
	$dhcp_ddns = "ddns-update-style interim;\n";
	$dhcp_ddns .= "allow unknown-clients;\n";
	$dhcp_ddns .= "allow client-updates;\n";
	$dhcp_ddns .= "ddns-domainname \"".$conf->dns_domain."\";\n";
	$dhcp_ddns .= "ddns-rev-domainname \"in-addr.arpa\";\n\n";

	$dhcp_ddns .= "key DHCP_UPDATER {\n\talgorithm hmac-md5;\n";
	$dhcp_ddns .= "\t secret \"".$conf->dhcp_ddns_secret."\";\n}\n";

	$dhcp_ddns .= "zone $conf->dns_domain. {\n";
	$dhcp_ddns .= "\tprimary\t".$conf->ddns_server.";\n";
	$dhcp_ddns .= "\tkey\tDHCP_UPDATER;\n}\n";

} else {
	$dhcp_ddns = "\nddns-update-style ad-hoc;\n";
};
// 1. Options (per subnet)

$sel = "select * from dhcp_options;";// where scope = 0;";

$res = mysql_query($sel) or die ("Cannot query MySQL : $sel \n");

if (mysql_num_rows($res) > 0) {
	while ($option = mysql_fetch_array($res)) {
		$options[$option['scope']] .= "\toption ".$option['name'].' '.$option['value'].";\n";
	};
};


// 2. Fixed IP Adressses
$dhcp_fixedips = "\n\n";
#$sel = "SELECT systems.name as name, systems.mac as mac, INET_NTOA(ip.address) as ip, ip.status as status FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE (ip.status = 2) OR (ip.status=3);";
# What about the empty fields? They will prevent dhcpd from running. 
# This query below tests for such fields
$sel = "SELECT systems.name as name, systems.mac as mac, INET_NTOA(ip.address) as ip, ip.status as status FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ( (ip.status = 2) OR (ip.status=3) ) and ( (name is not null) and (mac is not null) );";
$res = mysql_query($sel) or die ("Cannot query MySQL");
if (mysql_num_rows($res) > 0) {
        while ($host = mysql_fetch_array($res)) {
                if (! $dhcp_configuredip[$host['dhcp_ip']] ) {
			if ($host['status'] == 3) {
				$resnum++;
				$name = 'reserved-'.$resnum;
				$mac =  sprintf("%08s",$resnum);
				$mac = '0101.'.substr($mac,0,4).'.'.substr($mac,4,4);
				$mac = reformat_mac($mac);
			} else {
				$name = $host['name'];
				$mac = reformat_mac($host['mac']);
			};
			$dhcp_fixedips .= "host $name {\n";
			$dhcp_fixedips .= "\thardware ethernet $mac;\n";
	                $dhcp_fixedips .= "\tfixed-address ".$host['ip'].";\n";
	                $dhcp_fixedips .= "}\n\n";
			$dhcp_configuredip[$host['ip']] = TRUE;
		 } else {
                        $dhcp_fixedips .= "\n#\n# Desired IP address for host $name ($mac)";
                        $dhcp_fixedips .= ") is already configured (was ".$host['ip'].")\n#\n\n";
                };
        };
};




// 3. Subnets

//$sel = "SELECT * FROM subnets";
/*$sel = "SELECT subnets.id as scope, subnets.ip_address as ip_address, subnets.ip_netmask as ip_netmask,
		dhcp_subnets.dhcp_from as dhcp_from, dhcp_subnets.dhcp_to as dhcp_to, dhcp_subnets.dhcp_defaultrouter as dhcp_defaultrouter
	FROM subnets LEFT JOIN dhcp_subnets ON dhcp_subnets.subnet_id = subnets.id;";*/
// Same case here, you need to test for empty fields
$sel = "SELECT subnets.id as scope, subnets.ip_address as ip_address, subnets.ip_netmask as ip_netmask,                 dhcp_subnets.dhcp_from as dhcp_from, dhcp_subnets.dhcp_to as dhcp_to, dhcp_subnets.dhcp_defaultrouter as dhcp_defaultrouter         FROM subnets LEFT JOIN dhcp_subnets ON dhcp_subnets.subnet_id = subnets.id where subnets.ip_address is not null and subnets.ip_netmask is not null and  dhcp_subnets.dhcp_from is not null and dhcp_subnets.dhcp_to is not null and dhcp_subnets.dhcp_defaultrouter is not null;"

$res = mysql_query($sel) or die ("Cannot query MySQL");

if (mysql_num_rows($res) > 0) {
	while ($subnet = mysql_fetch_array($res)) {
		$dhcp_subnets .= "subnet ".$subnet['ip_address']." netmask ".transform_netmask($subnet['ip_netmask'])." {\n";
		$dhcp_subnets .= "\trange dynamic-bootp ".$subnet['dhcp_from']." ".$subnet['dhcp_to'].";\n";
		$dhcp_subnets .= "\toption routers ".$subnet['dhcp_defaultrouter'].";\n";
		$dhcp_subnets .= $options[$subnet['scope']];
		$dhcp_subnets .= "}\n\n";

		if ($conf->dhcp_ddns) {
			$dhcp_ddns .= "zone ".get_arpaname($subnet['ip_address']).". {\n";
		        $dhcp_ddns .= "\tprimary\t".$conf->ddns_server.";\n";
			$dhcp_ddns .= "\tkey\tDHCP_UPDATER;\n}\n";
		};
	};
};

$dhcp_config = $dhcp_preamble. $dhcp_ddns. $options[0] . $dhcp_fixedips. $dhcp_subnets;

		$outfile = $conf->dhcp_configfile;
		$fp = fopen($outfile,'w');
		fwrite($fp,$dhcp_config);
		fclose($fp);

?>
