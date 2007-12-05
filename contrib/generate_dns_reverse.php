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


/*** Origin & SOA *****************************************************/
function make_soa($subnet) {
global $dns_primary;
global $dns_mail;
global $arpaname;
$soa_serial = date("ymdHi");

$dns_soa = "\$ORIGIN .
\$TTL 6h

$arpaname       IN      SOA    $dns_primary $dns_mail (
		$soa_serial		;serial
		1h                      ; refresh
                30m                     ; retry
                7d                      ; expiration
                1h )                    ; minimum
\n"; 

return($dns_soa);
};

$dns_preamble = "
;
; DON'T EDIT - Generated automatically from FreeNAC 
;\n";

/*** Name & Mail servers (NS & MX) **i***********************************/


$nameservers = explode(',',$dns_ns);
$dns_inns = "; Name servers (NS) \n";
foreach ($nameservers as $i => $nameserver) {
        $dns_inns .= "\t\t\tIN\tNS\t".$nameserver.'.'.$dns_domain.".\n";
};


function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);

        return($name);
};

function get_arpaname($subnet) {
	$parts = explode('.',$subnet);
	$name = 'in-addr.arpa';
	foreach ($parts as $i => $part) {
		$name = $part.'.'.$name;
	};
	return($name) ;
};

/*** Make IN PTR Records **********************************************/
function make_ptr($subnet) {
  global $dns_domain;
        $query = "SELECT * FROM systems WHERE r_ip regexp('$subnet') ORDER by r_ip ASC";
	$res = mysql_query($query) or die("Unable to query MySQL : $query; \n");

        if (mysql_num_rows($res) > 0) {
                while ($host = mysql_fetch_array($res)) {
                        $ip = explode('.',$host[r_ip]);
                        $num = $ip[3];
                        $dns_inptr .= $num."\t\tIN\tPTR\t".sanitize_name($host['name']).".$dns_domain.\n";
                };
        };
        return($dns_inptr);
};

$subnets = explode(',',$dns_subnets);

foreach ($subnets as $id => $subnet) {
	$arpaname = get_arpaname($subnet);
	$origin = '$ORIGIN '.$arpaname.".\n";
echo "*** $arpaname \n";
	$dns_soa = make_soa($subnet);
	$dns_inptr = make_ptr($subnet);
	
	$dns_zone = $dns_soa.$dns_preamble.$dns_inns."\n\n".$origin."\n".$dns_inptr;

		$outfile = $dns_outdir.'/'.$arpaname;
		$fp = fopen($outfile,'w');
		fwrite($fp,$dns_zone);
		fclose($fp);
};
?>
