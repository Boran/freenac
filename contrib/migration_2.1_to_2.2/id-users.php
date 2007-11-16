<?php

include_once('/opt/nac/web1/config.inc');
include_once('/opt/nac/web1/functions.inc');
include_once('/opt/nac/web1/print.inc');

db_connect();

$query = "SELECT AssocNtAccount FROM users";
$res = mysql_query($query);

while ($user = mysql_fetch_array($res)) {
	if ($user[0] == 'KEINE') {
		echo "Username : ".$user[0]." -> UID 0\n";
		$upd_users = "UPDATE users SET id=0 WHERE AssocNtAccount='KEINE';";
		$upd_systems = "UPDATE systems SET uid=0 WHERE description='KEINE';";
	} else {
		$i++;
		echo "Username : ".$user[0]." -> UID $i\n";
		$upd_users = "UPDATE users SET id=$i WHERE AssocNtAccount='".$user[0]."';";
		$upd_systems = "UPDATE systems SET uid=$i WHERE description='".$user[0]."';";
	};
	mysql_query($upd_users) or die ("Error in updating users for ".$user[0]." -> UID $i\n");
	mysql_query($upd_systems) or die ("Error in updating systems for ".$user[0]." -> UID $i\n");
};

?>
