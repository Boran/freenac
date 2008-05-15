#!/usr/bin/php
<?php
/**
 * contrib/querywinpc.php
 *
 * Long description for file:
 * program input: 
 * program output: 
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package             FreeNAC
 * @author              Sean Boran (FreeNAC Core Team)
 * @copyright           2008 FreeNAC
 * @license             http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version             SVN: $Id$
 * @link                http://freenac.net
 *
 */

require_once "../bin/funcs.inc.php";               # Load settings & common functions
$logger->setDebugLevel(2);

// Get a list of IPs on the network today
$query="select id, r_timestamp, r_ip from systems WHERE TO_DAYS(r_timestamp)=TO_DAYS(NOW()) ORDER BY r_ip";
#$query="select id, r_timestamp, r_ip from systems WHERE TO_DAYS(r_timestamp)=TO_DAYS(NOW()) AND r_ip like '193.5.227.1%' ORDER BY r_ip";
  #$logger->logit( $query);
  $logger->debug($query, 3);
  $res = mysql_query($query);
  if (!$res) {
    $logger->logit('Invalid query: ' . mysql_error(), LOG_ERR);
    exit(1);
  }
  while ($row=mysql_fetch_array($res, MYSQL_ASSOC))     # Iterate over all information found
  {
    #echo $row['r_ip'] ." " .$row['r_timestamp'] ."\n";
    echo $row['r_ip'] ;

    #list($ip,$mac,$domain,$usr) = split(",", syscall('../contrib/querywinpc.pl 193.5.227.11') );
    list($ip,$mac,$domain,$usr) = split(",", syscall("../contrib/querywinpc.pl " .$row['r_ip']) );
    #$mac=normalise_mac($mac);
    echo " $domain User=$usr mac=$mac\n";

  }

?>
