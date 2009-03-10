#!/usr/bin/php
<?php
/**
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation
 *
 * @package		FreeNAC
 * @author		Hector Ortiz (FreeNAC Core team)
 * @copyright		2009 FreeNAC
 * @license		http://www.gnu.org/copyleft/gpl.html	GNU Public License Version 2
 * @version		SVN: $Id$
 * @link		http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");

require_once("funcs.inc.php");

$logger->setDebugLevel(0);
$logger->setLogToStdOut(false);

$query = "SELECT s.id, s.name, s.mac, s.r_ip, s.r_timestamp, s.lastseen, n.timestamp FROM systems s
		LEFT JOIN nac_hostscanned n
		ON s.id=n.sid
		WHERE s.r_timestamp>=DATE_SUB(NOW(), INTERVAL ".mysql_real_escape_string($conf->scan_hours_for_ip)." HOUR)
		AND s.lastseen>=DATE_SUB(NOW(), INTERVAL ".mysql_real_escape_string($conf->scan_hours_for_ip)." HOUR)
		AND n.timestamp<=DATE_SUB(NOW(), INTERVAL 7 DAY)
		AND s.r_timestamp IS NOT NULL;";

$logger->debug($query, 3);
$res = mysql_query($query);

if ( ! $res)
{
   $logger->logit(mysql_error());
   exit(1);
}
else
{
   $ips = NULL;
   while (($row = mysql_fetch_array($res, MYSQL_ASSOC)) != false )
   {
      $logger->logit("System {$row['name']}({$row['mac']} - {$row['r_ip']}), lastseen L2 {$row['lastseen']}, lastseen L3 {$row['r_timestamp']} and last scanned on {$row['timestamp']} is going to be scanned");
      $ips .= "{$row['r_ip']} ";
   }
   if ( $ips !== NULL )
   {
      syscall("./port_scan.php $ips");
   }
}

?>
