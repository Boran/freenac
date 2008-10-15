#!/usr/bin/php
<?php
/**
 * enterprise/sms_test.php
 *
 * Long description for file:
 * Test SMS SQL connection
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
require_once "bin/funcs.inc.php";      # Load settings & common functions

putenv('FREETDSCONF=/opt/freetds/etc/freetds.conf');


// the following come from config_en.inc
global $conf,$sms_dbuser,$sms_dbpass;
// other settings are loaded by the conf class, which loads the
// config table in the DB

# $check_sms_mac_dbalias, $check_sms_mac_db, $check_sms_view;

set_time_limit(0);
define_syslog_variables();
openlog("sms_test", LOG_PID , LOG_LOCAL5);


## Is the SMS module enabled??
db_connect();
$enabled=v_sql_1_select("select value from config where name='sms_enabled'");
if (! $enabled) {
  echo "ERROR: sms_enabled is not set in the opennac.config table\n";
  exit;
}


echo "Connect to alias:{$conf->sms_dbalias} DB:$conf->sms_db $sms_dbuser, $sms_dbpass\n";
$msconnect = mssql_connect($conf->sms_dbalias, $sms_dbuser, $sms_dbpass);
if (! $msconnect ) {
  echo "Cannot connect to DB server {$conf->sms_dbalias}:" . mssql_get_last_message();
  return;
}

$d = mssql_select_db($conf->sms_db, $msconnect) 
  or die("Couldn't open database ".$conf->sms_db ." ".mssql_get_last_message());


# Sample query: 6 entries witha  valid IP address
$query=<<<TXT
SELECT DISTINCT TOP 6 
  LOWER(Name0) AS ComputerName, User_Domain0 as ComputerDomain,
  LOWER(MACAddress0) AS mac, UPPER(User_Name0) As UserName, 
  IPAddress0 as IPAddress, IPSubnet0 as IPSubnet,
  Operating_System_Name_and0 AS os 
from {$conf->sms_view}
WHERE datalength(IPAddress0) > 0
ORDER BY ComputerName DESC
TXT;

echo "$query\n";

// Either:
$r= mssql_fetch_all($query);
foreach ($r as $row) { 
  #echo "ROW: Computer=$row[ComputerName], MAC=$row[mac], User=$row[UserName], OS=$row[os]\n"; 
  print_r($row);
}

// OR:
#$res = mssql_query($query);
#if (! $res) {
#  echo "Cannot execute query\n";
#  return;
#}
#while ( list($f1, $f2, $f3)=mssql_fetch_array($res) ) {
#  echo "ROW: $f1,$f2,$f3 \n";
#}

?>
