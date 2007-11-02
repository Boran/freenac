#!/usr/bin/php
<?php
/**
 * /opt/nac/bin/dump_ports
 *
 * Long description for file:
 * ...
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


require_once "funcs.inc.php";

$logger->setDebugLevel(0);
#$logger->setLogToStdOut();

## Connect to DB
  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect : " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database");

$msg='';

#$query="SELECT (SELECT swgroup FROM switch where switch.ip=systems.switch) as Stockwerk, port, (SELECT name from switch where switch.ip=systems.switch) as switch, switch as ip,  vlan, (SELECT value from vlan where vlan.id=systems.vlan) as Vlan_name from systems ORDER BY switch;";
#$query="select sw.swgroup as Stockwerk,p.name,sw.name as switch,sw.ip as ip, v.default_id as vlan, v.default_name as vlan_name from switch sw  inner join port p on p.switch=sw.id right join systems s on s.lastport=p.id inner join vlan v on s.vlan=v.id  order by sw.ip;";
$query="select sw.swgroup as Stockwerk,p.name,sw.name as switch,sw.ip as ip, v.default_id as vlan, v.default_name as vlan_name from switch sw  inner join port p on p.switch=sw.id inner join systems s on s.lastport=p.id inner join vlan v on s.vlan=v.id  order by sw.ip";
if ($logger->getDebugLevel()) { $query=$query . " LIMIT 10"; }

  debug1($query);
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }

  if (mysql_num_rows($res) ==0) {
    $msg="No port entries found!";

  } else {
    $msg="Stockwerk;Port;Switch Name; Switch IP;Vlan Number\n";

    while ($line = mysql_fetch_array($res, MYSQL_NUM)) {
      #debug1($line[0]);
      if ($line[0]!=NULL) {
        #$logger->logit("$line[0];$line[1];$line[2];$line[3];$line[4]\n");
        $msg=$msg ."$line[0];$line[1];$line[2];$line[3];$line[4]\n";
      }
    }

  }


#log2db('info', 'dump_ports: completed');
$logger->logit('completed');
if ($conf->mail_user)
   $logger->mailit("VMPS dump_ports",$msg,$conf->mail_user);
else
   $logger->mailit("VMPS dump_ports",$msg);

mysql_close($connect);
?>
