#!/usr/bin/php
<?php
/**
 * /opt/nac/bin/purge_unknowns
 *
 * Long description for file:
 * Delete unknown systems older than $unknown_purge days
 * a) First list & log what we delete
 * b) Then actually wipe it!
 * Allow max. 50 entries to be deleted (for now).
 * $unknown_purge is stored in config.inc
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */


require_once "funcs.inc.php";               # settings & functions

$logger->setDebugLevel(0);
$logger->setLogToStdOut();

# We don't have PHP5, so need some compat stuff
#require_once 'PHP/Compat.php';
#PHP_Compat::loadFunction('fprintf');

$connection_timeout=20;  #seconds for initial connection
$fgets_timeout=2;        #seconds to wait for results


## Connect to DB
  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect : " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database");


## basic validity checks to make sure we don't wipe systems by accident!!
## Ensure purge at least 10 days old.
## variable is in config.inc
if (($conf->unknown_purge) && ($conf->unknown_purge>10)) {
  $logger->logit("unknown purge days= ".$conf->unknown_purge);

} else {
  $logger->logit("purge_unknowns: invalid variable unknown_purge, value= ".$conf->unknown_purge);
  exit -1;
}

## Delete unknown system older than 2 Months
## a) First list & log what we delete
#$query="SELECT mac,vlan,description,port,switch,LastSeen from systems where name='unknown' and TO_DAYS(Lastseen)<TO_DAYS(NOW())-".$conf->unknown_purge." LIMIT 50"; 
#$query="SELECT mac,vlan,description,port,switch,LastSeen from systems where name LIKE '%unknown%' and TO_DAYS(Lastseen)<TO_DAYS(NOW())-".$conf->unknown_purge." LIMIT 50"; 
$query="select mac,vlan,description,lastport,lastseen from systems where name like '%unknown%' and TO_DAYS(Lastseen)<TO_DAYS(NOW())-".$conf->unknown_purge." LIMIT 50";
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }
  while ($line = mysql_fetch_array($res, MYSQL_NUM)) {
    #printf("/opt/vmps/purge_unknowns: %s %s %s %s %s %s\n", $line[0], $line[1], $line[2], $line[3], $line[4] );
    
    $logger->logit($line[0] .' '. $line[1] .' '. $line[2] .' '. $line[3] .' '. $line[4] );
    log2db('info', 'purge_unknowns: '.$line[0] .' '. $line[1] .' '. $line[2] .' '. $line[3] .' '. $line[4] );
  }

## b) Now actually wipe it!
$query="DELETE from systems where name='unknown' and TO_DAYS(Lastseen)<TO_DAYS(NOW())-".$conf->unknown_purge." LIMIT 50"; 
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }

log2db('info', 'purge_unknowns: completed');
$logger->logit('completed');

mysql_close($connect);
?>
