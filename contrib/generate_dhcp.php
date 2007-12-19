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

//include_once('/opt/nac/bin/funcs.inc.php');
include_once('/opt/nac/web/webfuncs.inc');
include_once('/opt/nac/etc/config.inc');

db_connect($dbuser,$dbpass);


$dhcp_preamble = "
#
# DON'T EDIT - Generated automatically from FreeNAC 
#\n";

$dhcp_preamble .= $dhcp_defaults;


// 1. Options

$sel = "select * from dhcp_options;";// where scope = 0;";

$res = mysql_query($sel) or die ("Cannot query MySQL : $sel \n");

if (mysql_num_rows($res) > 0) {
	while ($option = mysql_fetch_array($res)) {
		$options[$option['scope']] .= "\toption ".$option['name'].' '.$option['value'].";\n";
	};
};


// 2. Fixed IP Adressses
$dhcp_fixedips = "\n\n";

$sel = "select name,mac,r_ip,dhcp_ip from systems where dhcp_fix = 1";
$res = mysql_query($sel) or die ("Cannot query MySQL");

if (mysql_num_rows($res) > 0) {
	while ($host = mysql_fetch_array($res)) {
		$dhcp_fixedips .= "host ".$host['name']." {\n";
		$dhcp_fixedips .= "\thardware ethernet ".reformat_mac($host['mac']).";\n";
		$dhcp_fixedips .= "\tfixed-address ".$host['dhcp_ip'].";\n";
		$dhcp_fixedips .= "}\n\n";
	};
};

// 3. Subnets

//$sel = "SELECT * FROM subnets";
$sel = "SELECT subnets.id as scope, subnets.ip_address as ip_address, subnets.ip_netmask as ip_netmask,
		dhcp_subnets.dhcp_from as dhcp_from, dhcp_subnets.dhcp_to as dhcp_to, dhcp_subnets.dhcp_defaultrouter as dhcp_defaultrouter
	FROM subnets LEFT JOIN dhcp_subnets ON dhcp_subnets.subnet_id = subnets.id;";

$res = mysql_query($sel) or die ("Cannot query MySQL");

if (mysql_num_rows($res) > 0) {
	while ($subnet = mysql_fetch_array($res)) {
		$dhcp_subnets .= "subnet ".$subnet['ip_address']." netmask ".$netmask[$subnet['ip_netmask']]." {\n";
		$dhcp_subnets .= "\trange dynamic-bootp ".$subnet['dhcp_from']." ".$subnet['dhcp_to'].";\n";
		$dhcp_subnets .= "\toption routers ".$subnet['dhcp_defaultrouter'].";\n";
		$dhcp_subnets .= $options[$subnet['scope']];
		$dhcp_subnets .= "}\n\n";
	};
};

$dhcp_config = $dhcp_preamble. $options[0] . $dhcp_fixedips. $dhcp_subnets;

		$outfile = $dhcp_configfile;
		$fp = fopen($outfile,'w');
		fwrite($fp,$dhcp_config);
		fclose($fp);

?>
