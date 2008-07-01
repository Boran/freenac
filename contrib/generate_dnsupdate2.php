#!/usr/bin/php
<?php
/**
 * contrib/generate_dns2.php
 *
 * Long description for file:
 * Using ip.address and systems.name from the FreeNAC 'ip' DB, generate 
 * a list of dynamic DNS updates.
 * The DNS update commands are written to $tmp_file, once the file has been 
 * written, the dns_update flag is reset for each field.
 * Inputs: ip.address, systems.name
 *   $conf->: ddns_server,  dns_domain, ddns_ttl
 * Options:  
 *   $generate_all: TRUE = all hosts, FALSE = hosts with dns_update bit 1 is set
 *  Status field
 *    -1 = special adress (network address, broadcast)
 *    0 = free
 *    1 = used, dynamic
 *    2 = fixed
 *    3 = reserved
 *
 *  DNS_update set top 1 if FreeNAC must update the dns
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Thomas Dagonnier (FreeNAC Core Team)
 * @copyright			2008 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */

// settings
// TODO : put in config table
$nsupdate="nsupdate -d ";    // -d = debug
$generate_all=TRUE; /* Generate all => all hosts, false => only hoses with dns_update bit 1 is set */


//  main ()
chdir(dirname(__FILE__));
set_include_path("./:../:../bin");
require_once("../bin/funcs.inc.php");
#include_once('/opt/nac/web/webfuncs.inc');
#include_once('/opt/nac/etc/config.inc');

$logger=Logger::getInstance();
$logger->setDebugLevel(3);
$logger->setLogToStdErr(false);


$logger->debug("ddns import started", 1);
db_connect($dbuser,$dbpass);

#$tmp_dir = '/tmp';
#$tmp_file = 'nsupdate'.date("ymdHi");
$tmp_file = system('mktemp');
#$outfile = $tmp_dir.'/'.$tmp_file;
$outfile = $tmp_file;

/*** A Records & aliases (CNAME)  ************************************************/

function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);

        return($name);
};

if ($generate_all) {
	$query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name, systems.dns_alias as cname FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0";

} else {
	 $query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name, ip.dns_update as dns_update, systems.dns_alias as cname FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0 AND ((ip.dns_update & 1) = 1)";
};

echo $query;
        $res = mysql_query($query) or die("Unable to query MySQL : $query; \n");

        $dns_ina='';
        if (mysql_num_rows($res) > 0) {
                while ($host = mysql_fetch_array($res)) {
			if (($host['name'] != 'unknown') && ($host['ip'] != '') && ($host['name'] != '')) { 
			$dns_name = sanitize_name($host['name']).".".$conf->dns_domain.".";
			$dns_ip = $host['ip'];

				$dns_ina .= 'update delete '.$dns_name."\t A\r\n";
	                        $dns_ina .= 'update add '.$dns_name."\t".$conf->ddns_ttl.' A '.$dns_ip."\r\n";
				if ($host['cname'] != '') {
					$cnames = explode(',',$host['cname']);
					foreach ($cnames as $cname) {
						$dns_cname = sanitize_name($cname).".".$conf->dns_domain.".";
						$dns_ina .= 'update delete '.$dns_cname."\t CNAME\r\n";
						$dns_ina .= 'update add '.$dns_cname."\t".$conf->ddns_ttl.' CNAME '.$dns_name."\r\n";

					};
				};
				// clear update flag
				if (!$generate_all) {
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

$dns_update = "server $conf->ddns_server\r\n";
//$dns_update .= "zone $dns_domain\r\n"; Zone must be in name
$dns_update .= $dns_ina;
$dns_update .= "send\n";

  $fp = fopen($outfile,'w');
  fwrite($fp,$dns_update);
  fclose($fp);
  $logger->debug("dns_update=" .$dns_update, 3);

// send file to dns server (actually execute the nsupdate)
  $res=syscall("$nsupdate $tmp_file");
  $logger->debug("answer=" .$res, 1);

// delete temporary file
  unlink($tmp_file);

?>
