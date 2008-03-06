#!/usr/bin/php
<?
/**
 * /opt/nac/contrib/flap_detect_2.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2008 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");

require_once("../bin/funcs.inc.php");

#$logger->setDebugLevel(3);
$logger->setLogToStdOut();
#Query to detect systems that are on the same port
$query = "select lastport, count(lastport) as num from systems where date_sub(curdate(), interval 2 hour) <= lastseen group by lastport having (count(lastport) > 1);";
$logger->debug($query, 3);
$res = mysql_query($query);
if ( ! $res )
{
   $logger->logit(mysql_error(), LOG_ERR);
   exit(1);
}
while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
{
   $possible_flapping = array();
   $flapping_systems = array();
   $query = "SELECT s.mac, s.vlan as vlan_id, v.default_name as vlan_name FROM systems s LEFT JOIN vlan v ON s.vlan=v.id WHERE s.lastport='{$row['lastport']}' and date_sub(curdate(), interval 2 hour) <= s.lastseen;";
   $logger->debug($query, 3);
   $res1 = mysql_query($query);
   if ( ! $res1 )
   {
      $logger->logit(mysql_error(), LOG_ERR);
      continue;
   }
   while ( $systems = mysql_fetch_array($res1, MYSQL_ASSOC) )
   {
      $possible_flapping[$systems['vlan_id']]++;
      $flapping_systems['mac'][] = $systems['mac'];
      $flapping_systems['vlan_name'][] = $systems['vlan_name'];
   }
   if ( count($possible_flapping) > 1 )
   {
      $query = "SELECT p.name AS port_name, s.name AS switch_name FROM port p INNER JOIN switch s ON p.switch=s.id WHERE p.id='{$row['lastport']}';";
      $logger->debug($query, 3);
      $res2 = mysql_query($query);
      if ( ! $res2 )
      {
         $logger->logit(mysql_error(), LOG_ERR);
         continue;
      } 
      $row = mysql_fetch_array($res2, MYSQL_ASSOC);
      print_r($row);     
      print_r($flapping_systems);
   }
}
?>
