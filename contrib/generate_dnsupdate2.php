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
include_once('/opt/nac/web/webfuncs.inc');
include_once('/opt/nac/etc/config.inc');

db_connect($dbuser,$dbpass);

#$tmp_dir = '/tmp';
#$tmp_file = 'nsupdate'.date("ymdHi");
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
        1 = hosts with dns_update bit 1 is set
*/
$ddns_level=0;
switch($ddns_level) {
		case 1 :
			$query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name, ip.dns_update as dns_update FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0 AND ((ip.dns_update & 1) = 1)";
			break;
		case 0 :
			$query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0";
			break;
};
        $res = mysql_query($query) or die("Unable to query MySQL : $query; \n");

        if (mysql_num_rows($res) > 0) {
                while ($host = mysql_fetch_array($res)) {
			if (($host['name'] != 'unknown') && ($host['ip'] != '') && ($host['name'] != '')) { 
			$dns_name = sanitize_name($host['name']).".".$conf->dns_domain.".";
			$dns_ip = $host['ip'];

				$dns_ina .= 'update delete '.$dns_name."\t A\r\n";
	                        $dns_ina .= 'update add '.$dns_name."\t".$conf->ddns_ttl.' A '.$dns_ip."\r\n";
				// clear update flag
				if ($ddns_level == 1) {
					$upd_clear = "UPDATE ip SET dns_update=".($host['dns_update'] & (~1))." WHERE id=".$host['id'];
					mysql_query($upd_clear) or die("Unable to update MySQL : $query; \n");
				};
                        };
                };
        };	


/* TODO : still missing :
	CNAMES !!!
	HINFO
	LOC
	SVR
*/

$dns_update = "server $ddns_server\r\n";
//$dns_update .= "zone $dns_domain\r\n"; Zone must be in name
$dns_update .= $dns_ina;
$dns_update .= "send\n";

		$outfile = $tmp_dir.'/'.$tmp_file;
		$fp = fopen($outfile,'w');
		fwrite($fp,$dns_update);
		fclose($fp);

// TODO : send file to dns server (actually execute the nsupdate)
// TODO delete temporary file

?>
