<?php
require_once('/opt/nac/web1/config.inc');
require_once('/opt/nac/web1/functions.inc');

db_connect();

$query = 'SELECT * FROM nac_servicestcp';
$res = mysql_query($query);
while ($row = mysql_fetch_array($res)) {
	$ins = "INSERT INTO opennac.services(port,protocol,name,description) VALUES ";
	$ins .= "('".$row['port']."',6,'".$row['service']."','".$row['description']."');";
	mysql_query($ins);
};

$query = 'SELECT * FROM nac_servicesudp';
$res = mysql_query($query);
while ($row = mysql_fetch_array($res)) {
	$ins = "INSERT INTO opennac.services(port,protocol,name,description) VALUES ";
	$ins .= "('".$row['port']."',17,'".$row['service']."','".$row['description']."');";
	mysql_query($ins);
};
?>
