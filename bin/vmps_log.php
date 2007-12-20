#!/usr/bin/php
<?php
/**
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright			2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

/**
* Update the vmpslog table
*/

/**
* Load settings and common functions
*/
require_once "funcs.inc.php";
$logger="logger -t vmps_log -p local5.info";

## Connect to DB
db_connect();

$fd = fopen("php://stdin", "r");
while ( !feof($fd) ) {
  $line=fgets($fd);
  if (strlen($line)>0) {
    $line=trim($line);           # remove whitespace
    #print "Original:#$line#\n";

    $query="INSERT INTO naclog "
      . "SET what='" . $line . "', "
      . "priority='info' ";
    $res = mysql_query($query);
    if (!$res) 
    { 
       $logger->logit('Invalid query: ' . mysql_error(), LOG_ERR); 
       exit(1);
    }
  }
}

mysql_close($connect);

?>
