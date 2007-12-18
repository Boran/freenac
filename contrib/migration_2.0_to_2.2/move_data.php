#!/usr/bin/php
<?php
require_once('/opt/nac/etc/config.inc');
require_once('/opt/nac/bin/funcs.inc.php');

db_connect();

$new_db = 'opennac';
$unknown_value = 1;
$ino = FALSE;

function insert_location($name,$building_id) {
    global $db;
    global $new_db;
    global $unknown_value;
    global $ino;

	if ($building_id > 0 ) {
	    $query="SELECT * from $new_db.location WHERE name='$name' AND building_id='$building_id' ";
	} else {
	    $query="SELECT * from $new_db.location WHERE name='$name' ";
	};
#      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query (insert location): " . mysql_error());
    ## The select query had no effect, so assume its a new user.
    if (mysql_num_rows($res)==0) {

	if (((!$building_id) || ($buildig_id == '')) && ($ino == TRUE)) {
			$building_id = 2;
	};
	      $query="INSERT INTO $new_db.location SET name='$name', building_id='$building_id';";
#      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());
      $id= mysql_insert_id();
      $str = "New building added : $name ($id)" ;
#        debug2($str);
#        reporterr('info', $str);
    } else {
        $location = mysql_fetch_array($res);
        $id = $location['id'];
    };
   return($id);
}

function insert_cabletype($name) {
	global $db;
	global $new_db;
	if ($name == '') {
		$id=0;
	} else {
		$query="SELECT * from $new_db.cabletype WHERE name='$name' ";
		$res = mysql_query($query) OR die("Error in DB-Query (insert location): " . mysql_error());
		if (mysql_num_rows($res)==0) {
			$query = "INSERT INTO $new_db.cabletype SET name='$name';";
			$res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());
		        $id= mysql_insert_id();
		        $str = "New cabletype added : $name ($id)" ;
			#        debug2($str);
			#        reporterr('info', $str);
		} else {
        		$cabletype = mysql_fetch_array($res);
        		$id = $cabletype['id'];
		};
	};
	return($id);
};


function insert_building ($name) {
    global $db;
    global $new_db;
    $query="SELECT * from $new_db.building WHERE name='$name' ";
#      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query (insert building): " . mysql_error());
    ## The select query had no effect, so assume its a new user.
    if (mysql_affected_rows()==0) {
      $query="INSERT INTO $new_db.building SET name='$name'";
#      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());
      $id= mysql_insert_id();
      $str = "New building added : $name ($id)" ;
#        debug2($str);
#        reporterr('info', $str);
    } else {
        $building = mysql_fetch_array($res);
        $id = $building['id'];
    };
   return($id);
}

// first, those with buildings
function create_locations() {
	global $new_db;
	global $ino;
	global $unknown_value;

	$dualtarget[0] = array("db" => "users", "building" => "HouseIdentifier", "office" => "PhysicalDeliveryOfficeName", "id" => "AssocNtAccount");
	$dualtarget[1] = array("db" => "systems", "building" => "building", "office" => "office", "id" => "mac");

	if ($ino) { mysql_query("INSERT INTO building VALUES (2,'Ber-Omu93');");};

	foreach ($dualtarget as $target) {
		$query = 'SELECT '.$target['id'].' as id,' .$target['building'].' as building,'.$target['office'].' as office FROM '.$target['db'];
		$res = mysql_query($query);
		if (mysql_affected_rows() > 0) {
			while ($loc = mysql_fetch_array($res)) {
				if ($loc['building'] != '') {
					$building_id = insert_building($loc['building']);
				} else {
					$building_id = 0;
				};
				if ($loc['office'] != '') {
					$location_id = insert_location($loc['office'],$building_id);
				} else {
					$location_id = 0;
				};
			};
		};
	};


#	$singletargets[0] = array("db" => "port", "id" => "id", "office" => "location");
	$singletargets[0] = array("db" => "switch", "id" => "ip", "office" => "location");
	$singletargets[1] = array("db" => "patchcable", "id" => "outlet", "office" => "office");
#	$singletargets[2] = array("db" => "systems", "id" => "mac", "office" => "office");

	foreach ($singletargets as $target) {
		$query = 'SELECT '.$target['id'].' as id,' .$target['office'].' as office FROM '.$target['db'];
		$res = mysql_query($query);
		if (mysql_affected_rows() > 0) {
			while ($loc = mysql_fetch_array($res)) {
				if ($loc['office'] != '') {
					$location_id = insert_location($loc['office'],1);
				} else {
					$location_id = 0;
				};
			};
		};
	};
};


function get_locationid($building,$office) {
	global $new_db;
	global $unknown_value;
	global $ino;

#echo "** $building * $office **";
	if ($office == '') { return(0); };
	if ($building == 'omu93') { $building = 'Ber-Omu93'; };
	if (($ino == TRUE) && ($building == '')) { $building = 'Ber-Omu93'; };
	if ($building == '') { 
		$query = "SELECT location.id FROM $new_db.location location LEFT JOIN $new_db.building building ON building.id = location.building_id WHERE location.name='$office' AND building.id=1 limit 1;";
	} else {
		$query = "SELECT location.id FROM $new_db.location location LEFT JOIN $new_db.building building ON building.id = location.building_id WHERE location.name='$office' AND building.name='$building' limit 1;";
	};
#echo $query."\n";
	$res = mysql_query($query) or die("Unable to query mysql : $query\n");
	$row = mysql_fetch_array($res);
	if ($row[0]) {
#echo " => ".$row[0]."\n";
		return($row[0]);
	} else {
#echo " => 1\n";
		return($unknown_value);
	};
};

function update_locations() {
	global $new_db;

	$singletargets[0] = array("db" => "switch", "id" => "ip", "office" => "location", "newid" => "id");
	$singletargets[1] = array("db" => "patchcable", "id" => "outlet", "office" => "office", "newid" => "id");
	$singletargets[2] = array("db" => "systems", "id" => "mac", "office" => "office", "newid" => "id");

	foreach ($singletargets as $key => $target) {
		foreach ($target as $varname => $value) { $$varname = $value; };
		$query = "SELECT olddb.$id as oldid, newdb.$newid as newid_value, olddb.$office as oldoffice FROM $db olddb LEFT JOIN $new_db.$db newdb ON newdb.$id = olddb.$id limit 1;";
	if ($db == 'patchcable') { $query = "SELECT olddb.von_dose as oldid, newdb.id as newid_value, olddb.von_office as oldoffice FROM patchcable olddb LEFT JOIN $new_db.patchcable newdb ON newdb.outlet = olddb.von_dose limit 1;"; };

#echo $query."\n";
		$res = mysql_query($query);
		if (mysql_affected_rows() > 0) {
			while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
				foreach ($row as $varname => $value) { $$varname = $value; };
#  				if ($oldoffice) {
					if ($newid_value) {
						$locationid = get_locationid('',$oldoffice);
						$upd = "UPDATE $new_db.$db SET $office=$locationid WHERE $newid=$newid_value;";
					};
#if ($db == 'switch') { echo "$upd\n"; };
					mysql_query($upd) or die("Unable to update mysql : $upd\n");
#				};
			};
		};
	};


#	$dualtarget[1] = array("db" => "systems", "building" => "building", "office" => "office", "id" => "mac", "newid" => "id");
	$dualtarget[0] = array("db" => "users", "building" => "HouseIdentifier", "office" => "PhysicalDeliveryOfficeName", "id" => "AssocNtAccount", "newid" => "id");

	foreach ($dualtarget as $target) {
		foreach ($target as $varname => $value) { $$varname = $value; };
		$query = "SELECT olddb.$id as oldid, newdb.$newid as newid_value, olddb.$building as oldbuilding, olddb.$office as oldoffice FROM $db olddb LEFT JOIN $new_db.$db newdb ON newdb.$id = olddb.$id;";

		if ($db == 'users') { $query = "SELECT olddb.AssocNtAccount as oldid, newdb.id as newid_value, olddb.HouseIdentifier as oldbuilding, olddb.PhysicalDeliveryOfficeName as oldoffice FROM users olddb LEFT JOIN $new_db.users newdb ON newdb.username = olddb.AssocNtAccount;"; };
#echo $query."\n";
		$res = mysql_query($query);
		if (mysql_affected_rows() > 0) {
			while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {	
				foreach ($row as $varname => $value) { $$varname = $value; };
#				if ($oldbuilding) {
					$locationid = get_locationid($oldbuilding,$oldoffice);
					if ($db='users') { $office = 'location'; };
					$upd = "UPDATE $new_db.$db SET $office='$locationid' WHERE $newid='$newid_value';";
					mysql_query($upd) or die("Unable to update mysql : $query\n $upd\n");
#				};
			};
		};
	};


};

function copy_merge($target,$select) {
	global $new_db;
	echo "- Copy & merge to $target : ";
	$i = 0;
	$res = mysql_query($select);

	if (mysql_affected_rows() > 0) {
		while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
			$i++;
			$fieldlist = '';
			$values = '';
			foreach ($row as $field => $value) {
				if (!stristr($field,'NOT')) {
					$fieldlist .= '`'.$field.'`,';
					$values .= "'".mysql_escape_string($value)."',";
				};
			};
			$fieldlist = rtrim($fieldlist,',');
			$values = rtrim($values,',');
			$ins = "INSERT INTO $target ($fieldlist) VALUES ($values);";
#if (stristr($target,'hostscanned')) { echo $ins."\n"; };
			mysql_query($ins) or die("Error while migration $target : $ins \n");
			$last_id= mysql_insert_id();

			if (stristr($target,'patchcable')) {
				update_patch($last_id,$row['NOTswip'],$row['NOTportname'],$row['NOTtype']);
			} elseif (stristr($target,'systems')) {
				update_systems_port($last_id,$row['NOTswip'],$row['NOTportname']);
				update_systems_fk($last_id,$row['mac']);
			} elseif (stristr($target,'openports')) {
				update_openport($last_id,$row['NOTport'],$row['NOTprotocol']);
			};
		};	
	};
	echo "$i\n"; // maybe make a select count(*) and check everything's ok

};

function copy_fields($table,$fields) {
	global $new_db;
	$select = "SELECT $fields FROM $table";
	$target = $new_db.'.'.$table;
	echo "- Copy to $table ($fields) : ";
	$i = 0;
	$skip = FALSE;
	$res = mysql_query($select);
	if (mysql_affected_rows() > 0) {
		while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
			$i++;
			$fieldlist = '';
			$values = '';
			foreach ($row as $field => $value) {
				$fieldlist .= '`'.$field.'`,';
				$values .= "'".mysql_escape_string($value)."',";
				if (($table == 'vlan') && ($value == '--NONE--')) { $skip = TRUE; };
			};
			$fieldlist = rtrim($fieldlist,',');
			$values = rtrim($values,',');
#if ($table == 'switch') { echo "$ins\n"; };
			$ins = "INSERT INTO $target ($fieldlist) VALUES ($values);";
			if (!$skip) {
				mysql_query($ins) or die("Error while migration $target : $ins \n");
			} else {
				$skip = FALSE;
			};
			$last_id= mysql_insert_id();
#			echo $ins."\n";
			
		};	
	};
	echo "$i\n"; // maybe make a select count(*) and check everything's ok
};

function get_serviceid($port, $protocol) {
	global $new_db;
	if ($protocol == 'udp') { $proto = 17; };
	if ($protocol == 'tcp') { $proto = 6; };

	$sel = "SELECT id FROM $new_db.services WHERE protocol=$proto AND port=$port;";
	$res = mysql_query($sel);
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		return($row[0]);
	} else {
		return(0);
	};
};

function get_portid($switchip, $portname) {
	global $new_db;
	$select = "SELECT port.id as id FROM $new_db.port LEFT JOIN $new_db.switch ON port.switch = switch.id WHERE switch.ip = '$switchip' AND port.name = '$portname';";
#echo $select."\n";
	$res = mysql_query($select);
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		return($row[0]);
	} else {
		return(0);
	};
};

function update_patch($patchid,$switchip,$portname,$patchtypename) {
	global $new_db;
	$portid = get_portid($switchip,$portname);
	switch($patchtypename) {
		case 'Tf.':
			$cabletypeid = 8;
			break;
		case 'Adsl':
			$cabletypeid = 4;
			break;
		case 'Statisch':
			$cabletypeid = 2;
			break;
		case 'Dynamisch':
			$cabletypeid = 3;
			break;
		case '0':
			$cabletypeid = 1;
			break;
		default:
			$cabletypeid = insert_cabletype($patchtypename);
			break;
	};
	$upd = "UPDATE $new_db.patchcable SET port = $portid, type = $cabletypeid WHERE id='$patchid';";
	mysql_query($upd);
	return(0);
};

function update_openport($id,$port,$protocol) {
	global $new_db;
	$serviceid = get_serviceid($port,$protocol);
	$upd = "UPDATE $new_db.nac_openports SET service = '$serviceid' WHERE id='$id';";
	mysql_query($upd);
	return(0);
};

function update_systems_port($id,$switchip,$portname) {
	global $new_db;
	$portid = get_portid($switchip,$portname);
	$upd = "UPDATE $new_db.systems SET LastPort = '$portid' WHERE id=$id;";
	mysql_query($upd);
	return(0);
};


function update_systems_fk($id,$mac) {
	global $new_db;

# ftable = Foreign table
# oldfid = The field that was previously used as primary key in the foreign table
#		\-> in the new db
# newfid = the field that will be used as a primary key
# fk = the field in the target datbase that will be updated.

# basically :
# $previous_value = SELECT $oldfid FROM $target WHERE mac=$mac
# $new_value = SELECT $newfid FROM $ftable WHERE $oldfid = $previous_value
# UDPATE $new_db.$target SET $newfid = $new_value WHERE id=$sid

#	$relations['description'] = array("ftable" => "users", "oldfid" => "username", "newfid" => "id", "fk" => "uid");

	$relations['vlan'] = array("ftable" => "vlan", "oldfid" => "default_id", "newfid" => "id", "fk" => "vlan");
	$relations['LastVlan'] = array("ftable" => "vlan", "oldfid" => "default_name", "newfid" => "id", "fk" => "LastVlan");
	$relations['ChangeUser'] = array("ftable" => "users", "oldfid" => "username", "newfid" => "id", "fk" => "ChangeUser");

	$relations['os1'] = array("ftable" => "sys_os1", "oldfid" => "value", "newfid" => "id", "fk" => "os1");
	$relations['os2'] = array("ftable" => "sys_os2", "oldfid" => "value", "newfid" => "id", "fk" => "os2");
	$relations['os3'] = array("ftable" => "sys_os3", "oldfid" => "value", "newfid" => "id", "fk" => "os3");

	$query_previousvalues = "SELECT vlan, LastVlan, ChangeUser FROM systems WHERE mac='$mac'";

	$sys = mysql_fetch_array(mysql_query($query_previousvalues),MYSQL_ASSOC);
	foreach ($sys as $what => $previous_value) {

		$relation = $relations[$what];
		foreach ($relation as $varname => $value) { $$varname = $value; };

		if ($previous_value != '') {
			$qry_newvalue = "SELECT $newfid as newfid FROM $new_db.$ftable WHERE $oldfid = '$previous_value';";

			$res_newvalue = mysql_query($qry_newvalue) or die ("Unable to $qry_newvalue\n");
	
			if (mysql_num_rows($res_newvalue) > 0) {
				# we already have a reference in the foreign table
				$row_newvalue = mysql_fetch_array($res_newvalue);
				$new_value = $row_newvalue[0];
			} elseif (stristr($what,'os') && ($previous_value != '')) {
				# we have to insert a new row in the foreign table
				$ins_newvalue = "INSERT INTO $new_db.$ftable (`value`) VALUES ('$previous_value');";
				mysql_query($ins_newvalue);
				$new_value= mysql_insert_id();
			} else {
				# nothing
				$new_value = 0;
			};
		} else {
			$new_value=NULL;
		};		
		$upd_fk = "UPDATE $new_db.systems SET $what = '$new_value' WHERE id=$id";

		mysql_query($upd_fk);
	};
};


echo "- Populate location/building\n";
create_locations();

$sametable = array('switch', 'auth_profile', 'dhcp_options','dhcp_subnets',
		'nac_wsuscomputertarget', 'nac_wsusosmap',
		'oper','stat_ports','stat_systems');
foreach ($sametable as $table) {
	copy_fields($table,'*');
};


copy_fields('class2','value');
copy_fields('users','AssocNtAccount as username, LastSeenDirex as LastSeenDirectory, Surname, GivenName, Department, rfc822mailbox, PhysicalDeliveryOfficeName, TelephoneNumber, Mobile, manual_direx_sync, comment, vmps_rights as nac_rights');

copy_fields('vlan','id as default_id, value as default_name, vlan_description, vlan_group');

copy_merge("$new_db.port","SELECT newsw.id as switch, p.name as name, p.comment as comment, restart_now, vl.id as default_vlan, vl.id as last_vlan FROM port as p JOIN $new_db.switch as newsw ON p.switch = newsw.ip LEFT JOIN $new_db.vlan as vl ON p.default_vlan = vl.default_id;");

copy_merge("$new_db.naclog","SELECT u.id as who, v.host as host, v.datetime as datetime, v.priority as priority, v.what as what FROM vmpslog as v JOIN $new_db.users as u ON v.who = u.username;");

copy_merge("$new_db.guilog","SELECT u.id as who, h.host as host, h.datetime as datetime, h.priority as priority, h.what as what FROM history as h JOIN $new_db.users as u ON h.who = u.username;");

copy_merge("$new_db.patchcable","SELECT p.von_geb_sch as rack, p.von_he_dosen as rack_location, von_dose as outlet, nach_other as other, nach_network as NOTtype, p.comment as comment, p.lastchange as lastchange, u.id as modifiedby, p.bis_wann as expiry, p.nach_switch as NOTswip, p.nach_port as NOTportname FROM patchcable as p LEFT JOIN $new_db.users as u ON p.visum = u.username;");
# LEFT JOIN $new_db.location as loc ON p.von_office = loc.name

copy_merge("$new_db.systems","SELECT u.id as uid, mac, status, name, s.comment, ChangeDate, LastSeen, history, r_ip, r_timestamp, r_ping_timestamp, inventar as inventory, scannow, os, os1 as os4, class, class2, switch as NOTswip, port as NOTportname FROM systems s LEFT JOIN $new_db.users u ON s.description = u.username;");

copy_merge("$new_db.nac_hostscanned","SELECT s.id as sid, h.id as id, h.ip as ip, h.hostname as hostname, h.os as os, h.timestamp as timestamp FROM nac_hostscanned as h LEFT JOIN $new_db.systems as s ON h.mac = s.mac;");

copy_merge("$new_db.nac_openports","SELECT h.sid as sid, op.banner as banner, op.timestamp as timestamp, op.port as NOTport, op.protocol as NOTprotocol FROM nac_openports as op LEFT JOIN $new_db.nac_hostscanned as h ON op.host = h.id;");

update_locations();

mysql_query("UPDATE $new_db.location SET building_id = 1 WHERE building_id = '';");
mysql_query("UPDATE $new_db.location SET building_id = 1 WHERE building_id = 0;");



?>
