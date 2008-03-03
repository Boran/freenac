<?php
/**
 * /opt/nac/contrib/generate_dns.php
 *
 * Generate Forward DNS configuration file from the FreeNAC database
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

/*include_once('/opt/nac-2.2/nac-2.2/web1/config.inc');
include_once('/opt/nac-2.2/nac-2.2/web1/functions.inc');
include_once('/opt/nac-seclab/web3/objects.inc');
include_once('/opt/nac-seclab/web3/defs.inc');
*/
//include_once('/opt/nac/bin/funcs.inc.php');
include_once('/opt/nac/web/webfuncs.inc');
include_once('/opt/nac/etc/config.inc');

db_connect($dbuser,$dbpass);

//$tmp_dir = '/tmp';
//$tmp_file = 'nsupdate'.date("ymdHi");
$tmp_file = system('mktemp');

/*** A Records & aliases (CNAME)  ************************************************/

function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);

        return($name);
};

/* DDNS level :
        0 = all hosts
        1 = hosts with update_dns = 1
        2 = hosts with static ip
*/

$ddns_level = 0;
switch($ddns_level) {
		case 1 :
			$query = "SELECT INET_NTOA(ip.address) as ip, systems.name AS name FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0 AND ((ip.dns_update & 2) = 2)";
			break;
		case 0 :
			$query = "SELECT INET_NTOA(ip.address) as ip, systems.name AS name FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0"; 
			break;
};


/**** make one subnet ****/
function make_arpa($ip) {
	$bits = explode('.',$ip);
	$arpaname = $bits[3].'.'.$bits[2].'.'.$bits[1].'.'.$bits[0].'.in-addr.arpa.';
	return($arpaname);
};
	
function make_ptr($subnet) {
// TODO : check for CIDR ?
	global $query;
	global $conf;
	$query2 = $query." AND ip.subnet = $subnet";

        $res = mysql_query($query2) or die("Unable to query MySQL : $query2; \n");
        if (mysql_num_rows($res) > 0) {
                while ($host = mysql_fetch_array($res)) {
			
			$dns_name = sanitize_name($host['name']);
			$dns_ipname = make_arpa($host['ip']);
			if (($dns_name != 'unknown') && ($host['ip'] != '')) {
				$dns_inptr .= 'update delete '.$dns_ipname."\t PTR\r\n";
	                        $dns_inptr .= 'update add '.$dns_ipname."\t".$conf->ddns_ttl." PTR ".$dns_name.'.'.$conf->dns_domain.".\r\n";
                        };
                        if ($ddns_level == 1) {
                              $upd_clear = "UPDATE ip SET dns_update=".($host['dns_update'] & (~2))." WHERE id=".$host['id'];
			};

                };
        };	
	return($dns_inptr);
};

$query_subnets = "SELECT id FROM subnets WHERE reversedns=1";
$res_subnets =  mysql_query($query_subnets) or die("Unable to query MySQL : $query_subnets; \n");
        if (mysql_num_rows($res_subnets) > 0) {
		while ($subnet = mysql_fetch_array($res_subnets)) {
		$dns_inptr .= make_ptr($subnet['id']);
	};
};


$dns_update = "server $ddns_server\r\n";
$dns_update .= $dns_inptr;
$dns_update .= "send\n";

		$outfile = $tmp_dir.'/'.$tmp_file;
		$fp = fopen($outfile,'w');
		fwrite($fp,$dns_update);
		fclose($fp);

?>
