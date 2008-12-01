#!/usr/bin/php
<?php
/**
 * bin/delete_not_seen.php
 *
 * Long description for file:
 *
 * This script deletes all references to systems not seen in the last $conf->delete_not_seen months
 * from the FreeNAC database.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "./funcs.inc.php";               # Load settings & common functions

$logger->setDebugLevel(0);
$logger->setLogToStdOut(false);

if ($conf->delete_not_seen)
{
   $num_months = mysql_real_escape_string($conf->delete_not_seen);
}
else
{
   $logger->logit("delete_not_seen hasn't been defined in the config table");
   exit(1);
}
if ($num_months > 0)
{
   $logger->logit("Deleting systems not seen for the last $num_months months");
   $query =<<<EOF
SELECT s.mac, 
       s.name,
       s.r_ip,
       s.comment,
       u.username
FROM systems s 
LEFT JOIN users u 
ON s.uid=u.id 
WHERE LastSeen < DATE_SUB(NOW(), INTERVAL $num_months MONTH); 
EOF;
   $logger->debug($query,3);
   $res = mysql_query($query);
   if ( ! $res ) 
   {
      $logger->logit(mysql_error($res), LOG_ERR);
      exit(1);
   }
   while ($result = mysql_fetch_array($res,MYSQL_ASSOC))
   {
      $result['name'] = mysql_real_escape_string($result['name']);
      $result['mac'] = mysql_real_escape_string($result['mac']);
      $result['username'] = mysql_real_escape_string($result['username']);
      $result['ip'] = mysql_real_escape_string($result['ip']);
      $result['comment'] = mysql_real_escape_string($result['comment']);
      $string="System deleted. name={$result['name']}; mac={$result['mac']}; user={$result['username']}; ip={$result['ip']}; comment={$result['comment']}";
      $logger->logit($string);
      cascade_delete($result['mac']); 
      log2db('info',$string);
   } 
}


?>
