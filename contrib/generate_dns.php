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

chdir(dirname(__FILE__));
set_include_path("../:./");
require_once('../bin/funcs.inc.php');

db_connect($dbuser,$dbpass);

$soa_serial = date("ymdHi");

/*** Origin & SOA *****************************************************/
$dns_soa = "\$ORIGIN ".$conf->dns_domain.".
\$TTL 6h 

@       IN      SOA    ".$conf->dns_primary.' '.$conf->dns_mail." (
		$soa_serial		;serial
		1h                      ; refresh
                30m                     ; retry
                7d                      ; expiration
                1h )                    ; minimum
\n"; 

$dns_preamble = "
;
; DON'T EDIT - Generated automatically from FreeNAC by generate_dns.php
;\n";

/*** Name & Mail servers (NS & MX) **i***********************************/


$nameservers = explode(',',$conf->dns_ns);
$dns_inns = "; Name servers (NS) \n";
foreach ($nameservers as $i => $nameserver) {
	#$dns_inns .= "\t\t\tIN\tNS\t".$nameserver.'.'.$conf->dns_domain.".\n";
	$dns_inns .= "\t\t\tIN\tNS\t".$nameserver.".\n";
};

$mxservers = explode(',',$conf->dns_mx);
$dns_inmx = "; Mail servers (MX) \n";
foreach ($mxservers as $prio => $mxserver) {
	#$dns_inmx .= "\t\t\tIN\tMX\t1$prio\t".$mxserver.'.'.$conf->dns_domain.".\n";
	$dns_inmx .= "\t\t\tIN\tMX\t1$prio\t".$mxserver.".\n";
};


$dns_head = $dns_soa . $dns_preamble . $dns_inns."\n\n".$dns_inmx."\n\n";

/*** A Records & aliases (CNAME)  ************************************************/


function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);

        return($name);
};

	$query = "SELECT * FROM systems WHERE name != 'unknown' AND r_ip <> '' ORDER by r_ip ASC";
        $res = mysql_query($query) or die("Unable to query MySQL : $query; \n");

	$dns_ina = '';			// string that will contain all "A" records
	$dns_incname = '';		//                              "CNAME" records

        if (mysql_num_rows($res) > 0) {
                while ($host = mysql_fetch_array($res)) {
                        $dns_ina .= sanitize_name($host['name'])."\t\tIN\tA\t".$host['r_ip']."\n";
                        
                        // eventual aliases
                        if ($host['dns_alias'] != '') {
                                $aliases = explode(',',$host['dns_alias']);
                                foreach ($aliases as $my_alias) {
                                        $my_alias = sanitize_name($my_alias); // ltrim(rtrim($my_alias,' '),' ');
                                        if ($my_alias != '') {
                                                $dns_incname .= $my_alias."\t\tIN\tCNAME\t".$host['name'].".stns.ch.\n";
                                        };
                                };
                        };
                };
        };	


/* still missing :
	HINFO
	LOC
	SVR
*/


$dns_zone = $dns_head.$dns_ina."\n\n\n".$dns_incname;

		$outfile = $conf->dns_outdir.'/'.$conf->dns_domain.'.zone';
		$fp = fopen($outfile,'w');
		fwrite($fp,$dns_zone);
		fclose($fp);

?>
