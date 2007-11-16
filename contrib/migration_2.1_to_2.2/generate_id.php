<?php


include_once('/opt/nac/web1/config.inc');
include_once('/opt/nac/web1/functions.inc');
include_once('/opt/nac/web1/print.inc');

db_connect();


$tables['users'] = array("key" => "AssocNtAccount", "id" => "id");
$tables['systems'] = array("key" => "mac", "id" => "id");
$tables['port'] = array("key" => array('switch','name'), "id" => "id");
$tables['EpoComputerProperties'] = array("key" => array('ComputerName','NetAddress'), "id" => "id");
$tables['nac_openports'] = array("key" => array('port','protocol','host'), "id" => "id");
$tables['switch'] = array("key" => "ip", "id" => "id");
$tables['patchcable'] = array("key" => array('rack','rack_location','outlet','other'), "id" => "id");
$tables['nac_wsuscomputertarget'] = array("key" => "TargetID", "id" => "sid");
$tables['vlan'] = array("key" => "default_id", "id" => "id");

#$tables[''] = array("key" => "", "id" => "id");
#$tables[''] = array("key" => array('',''), "id" => "id");


foreach ($tables as $table => $fields) {
	$key = $fields['key'];
	$id =  $fields['id'];
	$i=1;
	if (is_array($key)) {
		$query = "SELECT ";
		foreach ($key as $keyfield) {
			$query .= "$keyfield,";
		};
		$query .= "$id FROM $table WHERE $id = 0;";
	} else {
		$query = "SELECT $key FROM $table WHERE $id = 0;";
	};
echo $query."\n";
	$res = mysql_query($query);
	while ($row = mysql_fetch_array($res)) {
		if (is_array($key)) {
			$upd = "UPDATE $table SET id=$i WHERE ";
			$keyecho = '';
			foreach ($key as $keyfield) {
				$upd .= "($keyfield = '".$row[$keyfield]."') AND ";
				$keyecho .= "$keyfield = '".$row[$keyfield]."', ";
			};
			$upd .= "(1 = 1);";
			echo "$keyecho -> $i\n";
		} else {
			$upd = "UPDATE $table SET id=$i WHERE $key='".$row[$key]."'";
			echo "$key ".$row[$key]." -> $i\n";
		};
#		echo $upd.";\n";
		mysql_query($upd);
		$i++;
	};
};

#echo $updlist;
?>
