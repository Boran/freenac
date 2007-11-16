<?php
require_once('/opt/nac/web1/config.inc');
require_once('/opt/nac/web1/functions.inc');

db_connect();



$new_db = 'opennac';
$unknown_value = 1;

mysql_select_db($new_db);

#$upd[$i] = "UPDATE location SET building_id=2 WHERE building_id=2;"; $i++;
#$upd[$i] = "UPDATE location SET building_id=2 WHERE building_id=3;"; $i++;
$upd[$i] = "UPDATE location SET building_id=2 WHERE building_id=1;"; $i++;
$upd[$i] = "UPDATE systems SET class2=4 WHERE class2=3;"; $i++;
$upd[$i] = "UPDATE systems SET class2=3 WHERE class2=2;"; $i++;
$upd[$i] = "UPDATE systems SET class2=2 WHERE class2=1;"; $i++;
$upd[$i] = "UPDATE systems SET class2=1 WHERE class2=0;"; $i++;
$upd[$i] = "UPDATE systems SET os=4 WHERE os=3;"; $i++;
$upd[$i] = "UPDATE systems SET os=3 WHERE os=2;"; $i++;
$upd[$i] = "UPDATE systems SET os=2 WHERE os=1;"; $i++;
$upd[$i] = "UPDATE systems SET os=1 WHERE os=0;"; $i++;

$upd[$i] = "UPDATE systems SET class=3 WHERE class=2;"; $i++;
$upd[$i] = "UPDATE systems SET class=2 WHERE class=1;"; $i++;
$upd[$i] = "UPDATE systems SET class=1 WHERE class=0;"; $i++;
$upd[$i] = "UPDATE systems SET class2=3 WHERE class2=2;"; $i++;

$upd[$i] = "INSERT INTO sys_class2 VALUES (2, 'GWP Typ I');"; $i++;
$upd[$i] = "INSERT INTO sys_class2 VALUES (3, 'GWP Typ II');"; $i++;
$upd[$i] = "INSERT INTO sys_class2 VALUES (4, 'GWP Typ III');"; $i++;
$upd[$i] = "INSERT INTO sys_class2 VALUES (10, 'NON-GWP Typ I');"; $i++;
$upd[$i] = "INSERT INTO sys_class2 VALUES (30, 'NON-GWP Typ II');"; $i++;

$upd[$i]= 'CREATE TABLE `ino_InvDaten` select * from inventory.ino_InvDaten;';$i++;
$upd[$i]= 'CREATE TABLE `stat1` select * from inventory.stat1;';$i++;
$upd[$i]= 'CREATE TABLE `stat2` select * from inventory.stat2;';$i++;
#$upd[$i]= 'CREATE TABLE `ino_InvDaten` select * from inventory.ino_InvDaten;';$i++;

#$upd[$i]= "INSERT INTO `cabletype` VALUES (5,'infnet');"; $i++;

$upd[$i] = "UPDATE systems SET os=2,os1=7 WHERE os=2"; $i++;
$upd[$i] = "UPDATE systems SET os=2,os1=5 WHERE os=3"; $i++;
$upd[$i] = "UPDATE systems SET os=2,os1=8 WHERE os=5"; $i++;
$upd[$i] = "UPDATE systems SET os=2,os1=6 WHERE os=1"; $i++;

#$upd[$i] = "UPDATE patchcable SET type=8 WHERE type=1"; $i++;
$upd[$i] = "UPDATE patchcable SET type=1 WHERE type=0"; $i++;
$upd[$i] = "UPDATE patchcable SET office=1 WHERE office=0"; $i++;


$upd[$i] = "UPDATE systems SET os=3,os1=12 WHERE os=49"; $i++;
$upd[$i] = "UPDATE systems SET os=3,os1=9 WHERE os=50"; $i++;
$upd[$i] = "UPDATE systems SET os=3,os1=1 WHERE os=51"; $i++;

$upd[$i] = "UPDATE systems SET os=6,os1=13 WHERE os=60"; $i++;
$upd[$i] = "UPDATE systems SET os=1,os1=1 WHERE os=99"; $i++;

$upd[$i] = "UPDATE systems SET os=5,os1=1 WHERE mac='000c.2937.2473'"; $i++;
$upd[$i] = "UPDATE systems SET os=7,os1=17 WHERE mac='0003.ba1f.98b3'"; $i++;
$upd[$i] = "UPDATE systems SET os2=4 WHERE os4 regexp 'sp2';";$i++;

foreach($upd as $key => $query) {
	mysql_query($query); 
};


$sw0_query = "SELECT id FROM switch WHERE ip='0.0.0.0';";
$sw0 = mysql_fetch_array(mysql_query($sw0_query));

$port_query = "SELECT id FROM port WHERE switch=".$sw0['id'];
$port_res = mysql_query($port_query);

while ($port = mysql_fetch_array($port_res)) {
	$del[$j] = "DELETE FROM port WHERE id=".$port['id'];
	$patch_query = "SELECT id FROM patchcable WHERE port=".$port['id'];$j++;
	$patch_res = mysql_query($patch_query);
	while ($patch = mysql_fetch_array($patch_res)) {
		$del[$j] = "UPDATE patchcable SET port=0 WHERE id=".$patch['id'];$j++;
	};
};

$del[$j] = "DELETE FROM `switch` WHERE id=".$sw0['id'];$j++;

foreach($del as $key => $query) {
	mysql_query($query) or die("DIE ON [$key] $query\n");
};

?>
