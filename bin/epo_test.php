#!/opt/php5/bin/php -f
<?php
/**
 * enterprise/epo_test.php
 *
 * Long description for file:
 * Test McAfee EPO SQL connection
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Sean Boran (FreeNAC Core Team)
 * @copyright                   2006 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     CVS: $Id:$
 * @link                        http://www.freenac.net
 *
 */

# Php weirdness: change to script dir, then look for includes
chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "./funcs.inc.php";               # Load settings & common functions
$logger->setLogToStdOut(0);

set_time_limit(0);

debug1("Connect to alias:".$conf->epo_alias." DB:".$conf->epo_db);
$msconnect = @mssql_connect($conf->epo_dbalias, $epo_dbuser, $epo_dbpass);
if (! $msconnect ) {
  $logger->logit("Cannot connect to DB server $epo_dbalias:" . mssql_get_last_message());
  return;
}

$d = @mssql_select_db($conf->epo_db, $msconnect);
if ( ! $d)
{
   $logger->logit("Couldn't open database ".$conf->epo_db." ".mssql_get_last_message(), LOG_ERR);
   exit(1);
}


#$query="SELECT name FROM sysobjects WHERE xtype = 'u'";
#$query="select TOP 5 * from ComputerProperties ";
$query="SELECT TOP 5 ParentID, ComputerName, IPHostName, DomainName, IPAddress, OSType, OSVersion, OSServicePackVer, NetAddress, UserName, TheTimestamp, TheHiddenTimestamp, Description  FROM ComputerProperties";

$logger->logit("$query\n");

#$res = mssql_query($query);
#if (! $res) {
#  $logger->logit("Cannot execute query\n");
#  return;
#}

#while ($arr = mssql_fetch_array($res)) {
#  print $arr["i"] . " " . $arr["v"] . "<br>\n";
#}
#while ( list($f1, $f2, $f3)=mssql_fetch_array($res) ) {
#  $logger->logit("$f1,$f2,$f3 \n");
#}

$r= mssql_fetch_all($query);
foreach ($r as $row) { $logger->logit("$row[ComputerName], $row[IPAddress]\n"); }

?>
