<?php
/**
 * web/dnsupdate2.php.php
 *
 * Long description for file:
 * Using ip.address and systems.name from the FreeNAC 'ip' DB, generate 
 * a list of dynamic DNS updates.
 * The DNS update commands are written to $tmp_file, once the file has been 
 * written, the dns_update flag is reset for each field.
 * Inputs: ip.address, systems.name
 *   $conf->: ddns_server,  dns_domain, ddns_ttl
 * Options:  
 *   $ddns_level: 0 = all hosts, 1 = hosts with dns_update bit 1 is set
 *  Status field
 *    0 = free
 *    1 = used: updated by Dyn dns
 *    -1 = LATER: special address (network address, broadcast)
 *    2 = LATER: fixed
 *    3 = reserved
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran/Thomas Dagonnier (FreeNAC Core Team)
 * @copyright			2008 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */

// settings
$nsupdate="nsupdate -v -t 10 ";    // -d = debug, 10 sec timeout, use tcp
$ddns_level=0; /* DDNS level: 0 = all hosts, 1 = hosts with dns_update bit 1 is set */



## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../lib:../web:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standard header ------

#$logger->setLogToStdErr(true);
#$logger->setLogToStdOut();
$logger->openFacility(500);
$logger->setDebugLevel(3);

//  main ()
$logger->logit("ddns import started");
echo "<hr><h3 text-align='centre'>Updating DNS </h3>";
db_connect($dbuser,$dbpass);


/*** A Records & aliases (CNAME)  ************************************************/

function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);

        return($name);
};

switch($ddns_level) {
	case 1 :
		$query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name, ip.dns_update as dns_update FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0 AND ((ip.dns_update & 1) = 1)";
		break;
	case 0 :
		$query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.system != 0";
		break;
};
	$logger->debug($query, 3);
        $res = mysql_query($query) or die("Unable to query MySQL: $query\n");

        $dns_ina='';
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

$dns_update = "server $conf->ddns_server\r\n";
  //$dns_update .= "zone $dns_domain\r\n"; Zone must be in name
  $dns_update .= $dns_ina;
  if ($logger->getDebugLevel()>2) {
    // request verbose answer, i.e. NOERROR below:
    # Outgoing update query:
    # ;; ->>HEADER<<- opcode: UPDATE, status: NOERROR, id:  29828
    # ;; flags: qr ra ; ZONE: 0, PREREQ: 0, UPDATE: 0, ADDITIONAL: 0
    $dns_update .= "answer\n";    
  }
  $dns_update .= "send\n";
  echo "The update request is:<pre>$dns_update</pre>";

#$fp = fopen($outfile,'w');
#$fp = fopen($outfile,'w');
if ($logger->getDebugLevel()<4) {

  $fp = popen("$nsupdate",'w');
  if( ! $fp ){
    print "<h3>Error while sending to: $nsupdate</h3>\n";
    return false;
  }
  fwrite($fp,$dns_update);
  pclose($fp);
  echo "<p>Update completed</p>";

} else {
  print "<h3>Debug level 3: </h3>\n";
  echo "<p>no commands actually sent to DNS.</p>";
}

  echo "<br><p>Go back to the <a href='{$this->calling_href}'>{$this->module} list</a></p>";
  #fclose($fp);
  //$logger->debug("dns_update=" .$dns_update, 3);

// send file to dns server (actually execute the nsupdate)
  #$res=syscall("$nsupdate $tmp_file");
  #$logger->debug("answer=" .$res, 1);

// delete temporary file
  #unlink($tmp_file);


?>
