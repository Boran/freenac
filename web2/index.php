<?php

/**
 * index.php
 *
 * Long description for file:
 * Simple FreeNAC browser. 
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package		FreeNAC
 * @author		Patrick Bizeau
 * @copyright	2006 FreeNAC
 * @license		http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version		CVS: $Id:$
 * @link		http://www.freenac.net
 *
 */


///////////////////////////////////////////
//     DO NOT EDIT BELOW THIS LINE       //
///////////////////////////////////////////

// include configuration
require_once('web2.conf.inc');
// include functions
require_once('funcs.inc');
// include pear module (if activated in config)
if ($xls_output){
	require_once "Spreadsheet/Excel/Writer.php";
}


//session setup
session_name('FreeNAC');
session_start();

// if not already set, set the $_SESSION vars
if (!isset($_SESSION['name'])){
	$_SESSION['name']=$unknown;
	$_SESSION['mac']='';
	$_SESSION['username']='';
}

// validate webinput
$_GET=array_map('validate_webinput',$_GET);
$_POST=array_map('validate_webinput',$_POST);
$_COOKIE=array_map('validate_webinput',$_COOKIE);


// setup db connectivity
$dblink=mysql_connect($dbhost, $dbuser, $dbpass)
	or die('DB Error. Unable to connect: ' . mysql_error());
// select database
mysql_select_db($dbname,$dblink) 
	or die('Could not select database '.$dbname);

// handle search requests
if ($_REQUEST['action']=='search'){
	// clear 
	if ($_REQUEST['submit']=='Clear'){
		$_SESSION['name']=$unknown;
		$_SESSION['mac']='';
		$_SESSION['vlan']='';
		$_SESSION['username']='';
		$_SESSION['switch']='';
	}
	if ($_REQUEST['submit']=='Submit'){
		$_SESSION['name']=$_REQUEST['name'];
		$_SESSION['mac']=$_REQUEST['mac'];
		$_SESSION['vlan']=$_REQUEST['vlan'];
		$_SESSION['username']=$_REQUEST['username'];
		$_SESSION['switch']=$_REQUEST['switch'];
	}
}

// if the ouput is a xls file we need to do it now (before returning anything to the browser... header issue)
if ($_REQUEST['action']=='xls' && $_REQUEST['type']!=''){
	// sql query
	if ($_REQUEST['type']=='12plus'){ // not seen in the last 12 month
		$sql='SELECT vlan, LastVlan,
		(SELECT value from vlan WHERE vlan.id=vlan) as vlanname,
		(SELECT vlan_group from vlan WHERE vlan.id=vlan) as vlangroup, status, 
		(SELECT value from vstatus WHERE vstatus.id=status) as statusname,name, inventar, description, comment, mac, ChangeDate, ChangeUser, LastSeen, building, office, port,
		(SELECT location from port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortLocation,
		(SELECT comment  from port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortComment,
		(SELECT IF(location, (SELECT GROUP_CONCAT(Surname) from users WHERE PhysicalDeliveryOfficeName=location), \' \') FROM port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortUserList, switch, 
		(SELECT name from switch WHERE systems.switch=switch.ip) as SwitchName,
		(SELECT location from switch WHERE systems.switch=switch.ip) as SwitchLocation,
		(SELECT GROUP_CONCAT(von_user,\', \',von_dose, \', \',von_office, \', \', comment) FROM patchcable pat WHERE systems.switch=pat.nach_switch AND systems.port=pat.nach_port) as PatchCable, history,
		(SELECT Surname         from users WHERE systems.description=users.AssocNtAccount) as UserSurname,
		(SELECT GivenName       from users WHERE systems.description=users.AssocNtAccount) as UserForename,
		(SELECT Department      from users WHERE systems.description=users.AssocNtAccount) as UserDept,
		(SELECT rfc822mailbox   from users WHERE systems.description=users.AssocNtAccount) as UserEmail,
		(SELECT HouseIdentifier from users WHERE systems.description=users.AssocNtAccount) as UserHouse,
		(SELECT PhysicalDeliveryOfficeName from users WHERE systems.description=users.AssocNtAccount) as UserOffice,
		(SELECT TelephoneNumber from users WHERE systems.description=users.AssocNtAccount) as UserTel,
		(SELECT Mobile          from users WHERE systems.description=users.AssocNtAccount) as UserMobileTel,
		(SELECT LastSeenDirex   from users WHERE systems.description=users.AssocNtAccount) as UserLastSeenDirex, os1, os2, os, 
		(SELECT value from sys_os WHERE id=os) as OsName, class, 
		(SELECT value from sys_class WHERE id=class) as ClassName, class2, 
		(SELECT value from sys_class2 WHERE id=class2) as Class2Name, scannow, os3, r_ip, r_timestamp, r_ping_timestamp
		FROM systems
		WHERE LastSeen < (NOW() - INTERVAL 1 YEAR)
		ORDER BY LastSeen DESC;';
	}
	else { // all systems
		$sql='SELECT vlan, LastVlan,
		(SELECT value from vlan WHERE vlan.id=vlan) as vlanname,
		(SELECT vlan_group from vlan WHERE vlan.id=vlan) as vlangroup, status, 
		(SELECT value from vstatus WHERE vstatus.id=status) as statusname,name, inventar, description, comment, mac, ChangeDate, ChangeUser, LastSeen, building, office, port,
		(SELECT location from port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortLocation,
		(SELECT comment  from port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortComment,
		(SELECT IF(location, (SELECT GROUP_CONCAT(Surname) from users WHERE PhysicalDeliveryOfficeName=location), \' \') FROM port p WHERE systems.switch=p.switch AND systems.port=p.name) as PortUserList, switch, 
		(SELECT name from switch WHERE systems.switch=switch.ip) as SwitchName,
		(SELECT location from switch WHERE systems.switch=switch.ip) as SwitchLocation,
		(SELECT GROUP_CONCAT(von_user,\', \',von_dose, \', \',von_office, \', \', comment) FROM patchcable pat WHERE systems.switch=pat.nach_switch AND systems.port=pat.nach_port) as PatchCable, history,
		(SELECT Surname         from users WHERE systems.description=users.AssocNtAccount) as UserSurname,
		(SELECT GivenName       from users WHERE systems.description=users.AssocNtAccount) as UserForename,
		(SELECT Department      from users WHERE systems.description=users.AssocNtAccount) as UserDept,
		(SELECT rfc822mailbox   from users WHERE systems.description=users.AssocNtAccount) as UserEmail,
		(SELECT HouseIdentifier from users WHERE systems.description=users.AssocNtAccount) as UserHouse,
		(SELECT PhysicalDeliveryOfficeName from users WHERE systems.description=users.AssocNtAccount) as UserOffice,
		(SELECT TelephoneNumber from users WHERE systems.description=users.AssocNtAccount) as UserTel,
		(SELECT Mobile          from users WHERE systems.description=users.AssocNtAccount) as UserMobileTel,
		(SELECT LastSeenDirex   from users WHERE systems.description=users.AssocNtAccount) as UserLastSeenDirex, os1, os2, os, 
		(SELECT value from sys_os WHERE id=os) as OsName, class, 
		(SELECT value from sys_class WHERE id=class) as ClassName, class2, 
		(SELECT value from sys_class2 WHERE id=class2) as Class2Name, scannow, os3, r_ip, r_timestamp, r_ping_timestamp
		FROM systems';
	}
	// query database
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	// Nothing found.
	if (mysql_num_rows($result)<1){
		die('Empty Data Set.');
	}
	// Found something
	else {
		// put the results in a neat excel file and send it to the browser
		create_xls($result);
		// work done, exit gracefully
		exit(0);
	}
}

// print the page header; so the user knows there's (much) more to come
echo print_header($entityname, $xls_output);

// let's find out what we're supposed to do
// edit the properties of a given system
if ($_REQUEST['action']=='edit'){
	// check that what we got is a mac in the system
	if (strlen($_REQUEST['mac'])==14){
		$sql=' SELECT sys.name, sys.mac, sys.status, sys.vlan, sys.lastvlan, sys.description as user, sys.office, sys.port, sys.lastseen, swi.location, swi.name as switch, sys.r_ip as lastip, sys.r_timestamp as lastipseen, sys.comment, eth.vendor 
			FROM systems as sys LEFT JOIN switch as swi ON sys.switch=swi.ip LEFT JOIN ethernet as eth ON (SUBSTR(sys.mac,1,4)=SUBSTR(eth.mac,1,4) AND SUBSTR(sys.mac,6,2)=SUBSTR(eth.mac,5,2))  
			WHERE sys.mac=\''.$_REQUEST['mac'].'\';';
		$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	}
	// Nothing or too much found.
	if (mysql_num_rows($result)!=1){
		echo ' Search returned '.mysql_num_rows($result).' rows.';
	}
	// Found something
	else {
		$row=mysql_fetch_array($result);
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		echo '<table width="500" border="0">'."\n";
		// Name
		echo '<tr><td width="87">Name:</td><td width="400">'."\n";
		echo '<input name="name" type="text" value="'.stripslashes($row['name']).'"/>'."\n";
		echo '</td></tr>'."\n";
		// MAC
		echo '<tr><td>MAC:</td><td>'."\n";
		echo $row['mac'].(!is_null($row['vendor'])?' ('.$row['vendor'].')':'')."\n";
		echo '</td></tr>'."\n";
		echo '<input type="hidden" name="mac" value="'.$row['mac'].'" />'."\n";
		// Status
		echo '<tr><td>Status:</td><td>'."\n";
		echo '<select name="status">';
    echo '<option value="1" '.($row['status']==1?'selected="selected"':'').'>active</option>'."\n";
		echo '<option value="0" '.($row['status']==0?'selected="selected"':'').'>inactive</option>'."\n";
		echo '</select></td></tr>'."\n";
		// VLAN
		echo '<tr><td>VLAN:</td><td>'."\n";
		echo '<select name="vlan">';
		$sql='SELECT id, value FROM vlan ORDER BY value;'; // Get details for all vlans
		$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_num_rows($res)>0){
			while ($r=mysql_fetch_array($res)){
				echo '<option value="'.$r['id'].'" '.($r['id']==$row['vlan']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
			}
		}
		echo '</select></td></tr>'."\n";
		// LastVLAN
		echo '<tr><td>LastVLAN:</td><td>'."\n";
		echo (is_null($row['lastvlan'])?'NONE':$row['lastvlan'])."\n";
		echo '</td></tr>'."\n";
		// User
		echo '<tr><td>User:</td><td>'."\n";
		echo get_userdropdown($row['user']);
		// Office
		echo '<tr><td>Office:</td><td>'."\n";
		echo '<input name="office" type="text" value="'.stripslashes($row['office']).'"/>'."\n";
		echo '</td></tr>'."\n";
		// Switch
		echo '<tr><td>Switch:</td><td>'."\n";
		echo $row['switch'].' -- '.$row['port'].' -- '.$row['location']."\n";
		echo '</td></tr>'."\n";
		// LastIP / LastIPseen
		echo '<tr><td>LastIP:</td><td>'."\n";
		echo (is_null($row['lastip'])?'NONE':$row['lastip'])."\n";
		echo ' -- ';
		echo (is_null($row['lastipseen'])?'NEVER':$row['lastipseen'])."\n";
		echo '</td></tr>'."\n";
		// LastSeen
		echo '<tr><td>LastSeen:</td><td>'."\n";
		echo (is_null($row['lastseen'])?'NEVER':$row['lastseen'])."\n";
		echo '</td></tr>'."\n";
		// Comment
		echo '<tr><td>Comment:</td><td>'."\n";
		echo '<input name="comment" type="text" value="'.stripslashes($row['comment']).'"/>'."\n";
		echo '</td></tr>'."\n";

		// Submit
		echo '<tr><td>&nbsp;</td><td>'."\n";
		echo '<input type="submit" name="submit" value="Submit" />'."\n";
		echo '</td></tr>'."\n";
		echo '</table><input type="hidden" name="action" value="update" /></form>';
	}
	
}
// parse request and update database
else if ($_REQUEST['action']=='update' && strlen($_REQUEST['mac'])==14 
			&& ($_REQUEST['status']==0 || $_REQUEST['status']==1) 
			&& is_numeric($_REQUEST['vlan'])) {
	// make sure we got a matching mac in systems, a vlan with this number and a useraccount
	$sql='SELECT sys.mac, sys.port, sys.switch, vl.id, users.assocntaccount FROM systems sys, vlan vl, users WHERE sys.mac=\''.$_REQUEST['mac'].'\' AND vl.id='.$_REQUEST['vlan'].' AND users.assocntaccount=\''.$_REQUEST['assocntaccount'].'\';';
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($result)!=1){
		echo 'MAC, VLAN or User missmatch.';
	}
	// Got it, prepare statment and insert changes into DB
	else {
		$row=mysql_fetch_array($result);
		$sql='UPDATE systems SET ';
		// got name?
		$sql.=($_REQUEST['name']!=''?'name=\''.$_REQUEST['name'].'\', ':'');
		// status, vlan
		$sql.='status='.$_REQUEST['status'].', vlan='.$_REQUEST['vlan'];
		// ntaccount
		$sql.=($_REQUEST['assocntaccount']!=''?', description=\''.$_REQUEST['assocntaccount'].'\' ':'');
		// got office?
		$sql.=($_REQUEST['office']!=''?', office=\''.$_REQUEST['office'].'\'':'');
		// got comment?
		$sql.=($_REQUEST['comment']!=''?', comment=\''.$_REQUEST['comment'].'\'':'');
		// set what we know for sure (changedate, changeuser,...)
		$sql.=', changedate=NOW(), changeuser=\'WEBGUI\'';
		// where?
		$sql.=' WHERE mac=\''.$_REQUEST['mac'].'\';';
		// update the given data set
		mysql_query($sql) or die('Query failed: ' . mysql_error());
		// Update OK
		// log what we have done
		$sql='INSERT INTO history (who, host, datetime, priority, what) VALUES (\'WEBGUI\',\'WEBGUI\',NOW(),\'info\',\'Updated system: '.$_REQUEST['name'].', '.$_REQUEST['mac'].', WEBGUI, '.$_REQUEST['comment'].', '.$_REQUEST['office'].', '.$row['port'].', '.$row['switch'].', vlan'.$_REQUEST['vlan'].'\');';
		mysql_query($sql) or die('Query failed: ' . mysql_error());
		// Update successful
		echo '<br />Update successful.<br />';
		// Ask the user if he want's to restart the associated port
		echo '<br />To restart Port '.$row['port'].' on Switch '.$row['switch'].' click <a href="'.$_SERVER['PHP_SELF'].'?action=restartport&switch='.$row['switch'].'&port='.$row['port'].'">here</a>.'; 
	}
}
// mark switchport for restart
else if ($_REQUEST['action']=='restartport' && $_REQUEST['switch']!='' && $_REQUEST['port']!=''){
	// make sure this switchport exists
	$sql='SELECT p.switch, p.name as port, p.location, p.comment FROM port p WHERE p.switch=\''.$_REQUEST['switch'].'\' AND p.name=\''.$_REQUEST['port'].'\';';
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($result)!=1){
		echo 'Switch/Port missmatch.';
	}
	// Got it, mark port for restart
	else {
		$sql='UPDATE port SET restart_now=1 WHERE switch=\''.$_REQUEST['switch'].'\' AND name=\''.$_REQUEST['port'].'\';';
		mysql_query($sql) or die('Query failed: ' . mysql_error());
		// Mark OK
		// Port marked for restart
		echo '<br />Port '.$_REQUEST['port'].' will be restarted whithin the next minute.';
	}
}
// show export choices
else if ($_REQUEST['action']=='export'){
	echo '<table width="1000" border="0"><tr><td>Excel Export<br /><br />'."\n";
	echo '<a href="'.$_SERVER['PHP_SELF'].'?action=xls&type=all">All systems</a><br />'."\n";
	echo '<a href="'.$_SERVER['PHP_SELF'].'?action=xls&type=12plus">Not seen during last 12 month</a><br />'."\n";
	echo '</td></tr></table>'."\n";
}
// display (all) systems
else {
	// get the systems
	$sql=' SELECT sys.name, sys.mac, stat.value as status, sys.vlan, vlan.value as vlanname, sys.lastvlan, sys.description as user, us.surname, us.givenname, sys.port, sys.lastseen, swi.location, swi.name as switch, sys.switch as switchip, sys.r_ip as lastip
		FROM systems as sys LEFT JOIN vstatus as stat ON sys.status=stat.id LEFT JOIN vlan as vlan ON sys.vlan=vlan.id LEFT JOIN switch as swi ON sys.switch=swi.ip LEFT JOIN users AS us ON us.assocntaccount=sys.description';

	// if its a search adjust the where...
	if ($_REQUEST['action']=='search'){
		$sql.=' WHERE (1=1)';
		// looking for a certain system?
		if ($_SESSION['name']!=''){
			$sql.=' AND sys.name LIKE \''.$_SESSION['name'].'\'';
		}
		// looking for a mac address?
		if ($_SESSION['mac']!=''){
			$sql.=' AND sys.mac LIKE \''.$_SESSION['mac'].'\'';
		}
		// looking for aVLAN?
		if ($_SESSION['vlan']!=''){
			$sql.=' AND sys.vlan LIKE \''.$_SESSION['vlan'].'\'';
		}
		// looking for a user?
		if ($_SESSION['username']!=''){
			$sql.=' AND sys.description LIKE \''.$_SESSION['username'].'\'';
		}
		// looking for a switch'
		if ($_SESSION['switch']!=''){
			$sql.=' AND swi.name LIKE \''.$_SESSION['switch'].'\'';
		}
		$sql.=' ORDER BY sys.name ASC;';
	}
	
	// ... if not get today's unknowns
	else{
		$sql.=' WHERE sys.name=\'unknown\' AND sys.LastSeen > (NOW() - INTERVAL 1 DAY)';
		$sql.=' ORDER BY sys.LastSeen DESC;';
	}
	
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	// echo table head
	echo '<table width="1000" border="0">
  <tr>
    <td width="140" class="center">Name</td>
    <td width="101" class="center">MAC</td>
    <td width="54" class="center">Status</td>
    <td width="60" class="center">Vlan</td>
    <td width="60" class="center">Last Vlan </td>
    <td width="112" class="center">Username</td>
    <td width="49" class="center">Port</td>
    <td width="163" class="center">Last Seen </td>
    <td width="105" class="center">Switch</td>
    <td width="112" class="center">Last IP </td>
  </tr>
	';

	// if it is a search print the search area
	if ($_REQUEST['action']=='search'){
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
		echo '<tr align="center">'."\n";
		// Name
		echo '<td><input name="name" type="text" size="20" value="'.$_SESSION['name'].'" /></td>'."\n";
		// MAC
		echo '<td><input name="mac" type="text" size="14" value="'.$_SESSION['mac'].'" /></td>'."\n";
		// Status
		echo '<td>&nbsp;</td>'."\n";
		// VLAN
		echo '<td><select name="vlan">';
		$sql='SELECT id, value FROM vlan ORDER BY value;'; // Get details for all vlans
		$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_num_rows($res)>0){
			echo '<option value=""></option>'."\n";
			while ($r=mysql_fetch_array($res)){
				if ($r['value']!=''){ // only those with actual values
					echo '<option value="'.$r['id'].'" '.($r['id']==$_SESSION['vlan']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
				}
			}
		}
		echo '</select></td>'."\n";
		// Last VLAN
		echo '<td>&nbsp;</td>'."\n";
		// Username
		echo '<td><select name="username">';
		$sql='SELECT DISTINCT(sys.description) AS username FROM systems AS sys ORDER BY sys.description ASC;'; // Get details for all active users
		$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_num_rows($res)>0){
			echo '<option value=""></option>'."\n";
			while ($r=mysql_fetch_array($res)){
				if ($r['username']!=''){ // only those with actual values
					echo '<option value="'.$r['username'].'" '.($r['username']==$_SESSION['username']?'selected="selected"':'').'>'.$r['username'].'</option>'."\n";
				}
			}
		}
		echo '</select></td>'."\n";
		// Port
		echo '<td>&nbsp;</td>'."\n";
		// Last seen
		echo '<td>&nbsp;</td>'."\n";
		// Switch
		echo '<td><select name="switch">';
		$sql='select distinct(swi.name) as switch  from systems as sys left join switch as swi on sys.switch=swi.ip order by switch asc;'; // Get details for all active switches
		$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_num_rows($res)>0){
			echo '<option value=""></option>'."\n";
			while ($r=mysql_fetch_array($res)){
				if ($r['switch']!=''){ // only those with actual values
					echo '<option value="'.$r['switch'].'" '.($r['switch']==$_SESSION['switch']?'selected="selected"':'').'>'.$r['switch'].'</option>'."\n";
				}
			}
		}
		echo '</select></td>'."\n";
		// Last IP
		echo '<td>&nbsp;</td>'."\n";
		echo '</tr>'."\n";
		// Clear/Submit
		echo '<tr align="right">
		  <td colspan="10"><input type="submit" name="submit" value="Submit" />
			<input type="submit" name="submit" value="Clear" /></td>
		</tr>
		<input type="hidden" name="action" value="search" /></form>
		';
	}
	// Nothing found.
	if (mysql_num_rows($result)<1){
		echo ' <td colspan="7">No entries found.</td></td>';
	}
	// Found something
	else {
		// Iterate trough the result set
		$i=0;
		echo print_resultset($result,$_SERVER);
	}
	echo '</table>';
}

// we're done. and as all tags need to be closed, print the footer now!
echo print_footer();

?>