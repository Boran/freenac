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



## MySQL DB settings for all scripts
  $dbhost="localhost";
  $dbname="inventory";
  $dbuser="inventwrite";
  $dbpass="invent99";              # keep this secret!

// Variable setup
$entityname='MyCompany';


///////////////////////////////////////////
//     DO NOT EDIT BELOW THIS LINE       //
///////////////////////////////////////////

//session setup
//session_name('FreeNAC');
//session_start();

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


// print the page header; so the user knows there's (much) more to come
echo print_header();

// let's find out what we're supposed to do
// edit the properties of a given system
if ($_REQUEST['action']=='edit'){
	// check that what we got is a mac in the system
	if (strlen($_REQUEST['mac'])==14){
		$sql=' SELECT sys.name, sys.mac, sys.status, sys.vlan, sys.lastvlan, sys.description as user, sys.port, sys.lastseen, swi.location, swi.name as switch, sys.r_ip as lastip, sys.r_timestamp as lastipseen, sys.comment, eth.vendor 
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
		echo '<input type="hidden" name="action" value="update">';
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
        echo '<option value="1" '.($row['status']=='active'?'selected="selected"':'').'>active</option>'."\n";
		echo '<option value="0" '.($row['status']!='active'?'selected="selected"':'').'>inactive</option>'."\n";
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
		echo '<select name="assocntacccount">';
		$sql='SELECT assocntaccount, surname, givenname FROM users ORDER BY surname'; // Get details for all users
		$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_num_rows($res)>0){
			while ($r=mysql_fetch_array($res)){
				echo '<option value="'.$r['assocntaccount'].'" '.(trim(strtolower($r['assocntaccount']))==trim(strtolower($row['user']))?'selected="selected"':'').'>'.$r['surname'].' '.$r['givenname'].'</option>'."\n";
			}
		}
		echo '</select></td></tr>'."\n";
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
		echo '</table></form>';
	}
	
}
// parse request and update database
else if ($_REQUEST['action']=='update' && strlen($_REQUEST['mac'])==14 
			&& ($_REQUEST['status']==0 || $_REQUEST['status']==1) 
			&& is_numeric($_REQUEST['vlan'])) {
	print_r($_REQUEST);
	//Array ( [name] => unknown [mac] => 00e0.00f5.c585 [status] => inactive [vlan] => active [comment] => s [action] => Submit )
	// make sure we got a matching mac in systems, a vlan with this number and a useraccount
	$sql='SELECT sys.mac, vl.id, users.assocntaccount FROM systems sys, vlan vl, users WHERE sys.mac=\''.$_REQUEST['mac'].'\' AND vl.id='.$_REQUEST['vlan'].' AND users.assocntaccount=\''.$_REQUEST['assocntaccount'].'\';';
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($result)!=1){
		echo 'MAC, VLAN or User missmatch.';
	}
	// Got it, prepare statment and insert changes into DB
	else {
		$sql='UPDATE systems SET ';
		// got name?
		$sql.=($_REQUEST['name']!=''?'name=\''.$_REQUEST['name'].'\', ':'');
		// status, vlan
		$sql.='status='.$_REQUEST['status'].', vlan='.$_REQUEST['vlan'];
		// ntaccount
		$sql.=($_REQUEST['assocntacccount']!=''?', description=\''.$_REQUEST['assocntacccount'].'\' ':'');
		// got comment?
		$sql.=($_REQUEST['comment']!=', comment=\''.$_REQUEST['comment'].'\''?'':'');
		// set what we know for sure (changedate, changeuser,...)
		$sql.=', changedate=\'NOW()\', changeuser=\'WEBGUI\'';
		// where?
		$sql.=' WHERE mac=\''.$_REQUEST['mac'].'\';';
		echo $sql;
		// update the given data set
		//$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
		if (mysql_affected_rows($result)==1){ // Update OK
			// log what we have done
			// somelog($somemessg, $somprio);
			echo '<br />Update successfull.';
		}
		else { // Update failed
			echo '<br />Update failed.';
		}
	}


}
// no special action, so display all today's unknowns
else {
	// get all the unknown systems
	$sql='SELECT sys.name, sys.mac, stat.value as status, sys.vlan, vlan.value as vlanname, sys.description as user, sys.port, swi.name as switch
			FROM systems as sys LEFT JOIN status as stat ON sys.status=stat.id LEFT JOIN vlan as vlan ON sys.vlan=vlan.id LEFT JOIN switch as swi ON sys.switch=swi.ip
			WHERE sys.name=\'unknown\' AND sys.LastSeen > (NOW() - INTERVAL 1 DAY)
			ORDER BY sys.LastSeen;';
	$result=mysql_query($sql) or die('Query failed: ' . mysql_error());
	// echo table head
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET"><table width="500" border="0">
		  <tr>
			<td width="124" class="center">Name</td>
			<td width="99" class="center">MAC</td>
			<td width="23" class="center">S</td>
			<td width="39" class="center">Vlan</td>
			<td width="62" class="center">Username</td>
			<td width="55" class="center">Port</td>
			<td width="66" class="center">Switch</td>
		  </tr>
	';
	// Nothing found.
	if (mysql_num_rows($result)<1){
		echo ' <td colspan="7">No entries found.</td></td>';
	}
	// Found something
	else {
		// Iterate trough the result set
		$i=0;
		echo print_resultset($result);
	}
	echo '</table></form>';
}




// we're done. and as all tags needs to be closed, print the footer now!
echo print_footer();

///////////////////////////////////////////
// Functions
///////////////////////////////////////////
//
// Print page header (if not already done)
//
function print_header(){
	global $entityname;
	if (!defined(HEADER)){
		$ret='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>FreeNAC @'.$entityname.'</title>
		<link href="bw.css" rel="stylesheet" type="text/css" />
		</head>

		<body>
		<table class="bw" width="500" border="0">
		  <tr>
			<td height="50" class="right">FreeNAC @'.$entityname.' </td>
		  </tr>
		  <tr>
			<td class="center"><a href="'.$SERVER['PHP_SELF'].'index.php">List Unknowns</a></td>
		  </tr>
		</table>
		';
		define('HEADER',true); // The header is out
		return $ret;
	}

}

//
// Print page footer (if not already done)
//
function print_footer(){
	if(!defined(FOOTER)){
		$ret='</table></body></html>';
		define('FOOTER',true);
		return $ret;
	}
}

//
// Print the lookup results
//
function print_resultset($res){
	$ret='';
	while ($row=mysql_fetch_array($res)){
		$ret.=($i%2==0)?'<tr class="light">':'<tr class="dark">';
		$ret.='<td><a href="'.$SERVER['PHP_SELF'].'?action=edit&mac='.$row['mac'].'">'.stripslashes($row['name']).'</a></td>'."\n";
		$ret.='<td class="center">'.$row['mac'].'</td>'."\n";
		$ret.='<td class="center">'.ucfirst($row['status']{0}).'</td>'."\n";
		$ret.='<td class="center" title="'.$row['vlanname'].'">'.$row['vlan'].'</td>'."\n";
		$ret.='<td class="center">'.$row['user'].'</td>'."\n";
		$ret.='<td class="center">'.$row['port'].'</td>'."\n";
		$ret.='<td class="center">'.$row['switch'].'</td>'."\n";
		$ret.='</tr>'."\n";
		$i++;
	}
	return $ret;
}

//
// validates webinput
// if the variable is an array recursevly call the 
// function for each value
//
function validate_webinput($value){
	if (is_array($value)){
		array_map('validate_webinput',$value);
	}
	else {
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		if (!is_numeric($value)){
			mysql_real_escape_string($value);
		}
	}
	return trim($value);
}

?>