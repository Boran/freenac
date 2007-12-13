<?php

  header("Content-Type: application/xml; charset=UTF-8"); 
  require_once('webfuncs.inc');
  db_connect($dbuser,$dbpass);

$num = 10;

function get_items($num) {
global $connect;
$select = "SELECT systems.id as id, mac,systems.name as hostname,vlan.default_name as vlan,r_ip as ip, switch.name as switch, port.name as port from systems
  LEFT JOIN vlan ON vlan.id = systems.vlan 
 LEFT JOIN port ON port.id = systems.LastPort
	LEFT JOIN switch ON switch.id = port.switch order by LastSeen desc limit $num;";

$result = mysql_query($select);

while ($row = mysql_fetch_array($result)) {
	$items .= "\t\t<item>\n";
	$items .= "\t\t\t<title>".$row['hostname'].' on '.$row['switch'].' - '.$row['port'].' ('.$row['vlan'].")</title>\n";
//	$items .= "\t\t\t<link>find.php?action=edit&id=".$row['id']."</link>\n";
	$items .= "\t\t\t<description>".$row['switch'].' - '.$row['port'].' - '.$row['vlan']."\n";
	$items .= $row['mac'].' - '.$row['ip']."\n";
	$items .= "</description>\n";
	$items .= "\t\t</item>\n";
};
return($items);



};

function get_header($num) {
	$header = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
	$header .= "\n\t<rss version=\"2.0\">\n";
	$header .= "\t\t<channel>\n";
	$header .= "\t\t\t<title>FreeNAC</title>\n";
        $header .= "\t\t\t<link>/nac</link>\n";
        $header .= "\t\t\t<description>FreeNAC LAN Management System</description>\n";
	return($header);
};

$rss_header = get_header($num);
$rss_items = get_items($num);
echo $rss_header.$rss_items;
echo "\n\t\t</channel>\n\t</rss>\n"; //channel>\n</rss>";

?> 
