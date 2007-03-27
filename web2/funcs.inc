<?php

/**
 * funcs.inc
 *
 * Long description for file:
 * Functions used by FreeNAC web2 GUI. 
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
 * @link			http://www.freenac.net
 *
 */


//
// Print page header (if not already done)
//
function print_header($entityname, $xls_output){
	if (!defined(HEADER)){
		$ret='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>FreeNAC @'.$entityname.'</title>
		<link href="bw.css" rel="stylesheet" type="text/css" />
		</head>

		<body>
		<table class="bw" width="1000" border="0">
		  <tr>
			<td height="50" class="right">FreeNAC @'.$entityname.' </td>
		  </tr>
		  <tr>
			<td class="center"><a href="'.$_SERVER['PHP_SELF'].'">List Unknowns (Last 24h)</a> | <a href="'.$_SERVER['PHP_SELF'].'?action=search">Search</a>';
		if ($xls_output){
			$ret.=' | <a href="'.$_SERVER['PHP_SELF'].'?action=export">Export</a>';
		}
		$ret.='</td>
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
function print_resultset($res,$server){
	$ret='';
	while ($row=mysql_fetch_array($res)){
		$ret.=($i%2==0)?'<tr class="light">':'<tr class="dark">';
		$ret.='<td><a href="'.$server['PHP_SELF'].'?action=edit&mac='.$row['mac'].'">'.stripslashes($row['name']).'</a></td>'."\n";
		$ret.='<td class="center">'.$row['mac'].'</td>'."\n";
		$ret.='<td class="center">'.ucfirst($row['status']).'</td>'."\n";
		$ret.='<td class="center" title="VLAN '.$row['vlan'].'">'.$row['vlanname'].'</td>'."\n";
		$ret.='<td class="center">'.($row['lastvlan']!=''?$row['lastvlan']:'&nbsp;').'</td>'."\n";
		$ret.='<td class="center" title="'.$row['surname'].' '.$row['givenname'].'">'.$row['user'].'</td>'."\n";
		$ret.='<td class="center">'.$row['port'].'</td>'."\n";
		$ret.='<td class="center">'.$row['lastseen'].'</td>'."\n";
		$ret.='<td class="center" title="'.$row['switchip'].'">'.$row['switch'].'</td>'."\n";
		$ret.='<td class="center">'.($row['lastip']!=''?$row['lastip']:'&nbsp;').'</td>'."\n";
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


//
// retrieves all users from the databases and returns them as a hmtl dropdownlist
//
function get_userdropdown($selected_user) {
	$ret='<select name="assocntaccount">';
	$sql='SELECT assocntaccount, surname, givenname FROM users ORDER BY surname'; // Get details for all users
	$res=mysql_query($sql) or die('Query failed: ' . mysql_error());
	if (mysql_num_rows($res)>0){
		while ($r=mysql_fetch_array($res)){
			$ret.='<option value="'.$r['assocntaccount'].'" '.(trim(strtolower($r['assocntaccount']))==trim(strtolower($selected_user))?'selected="selected"':'').'>'.$r['surname'].' '.$r['givenname'].'</option>'."\n";
		}
	}
	$ret.='</select></td></tr>'."\n";
	return $ret;
}

//
// creates an excel sheet and sends it to the browser
// input: $result (must be a mysql query result)
//
function create_xls($result){
	$fields = mysql_num_fields($result);
	// create a new workbook
	$xls = new Spreadsheet_Excel_Writer();
	
	// format head row
	$head =& $xls->addFormat();
	$head->setBold();
	$head->setAlign('center');

	// send HTTP headers
	$xls->send('freenac.xls');

	// create a worksheet
	$sheet =& $xls->addWorksheet('FreeNAC');

	// iterate trough the result set
	$r=0; // row
	$c=0; // column
	for ($c=0; $c < $fields; $c++) {
		$sheet->writeString(0, $c, ucfirst(mysql_field_name($result, $c)), $head);
	}
	while ($row=mysql_fetch_row($result)){
		$r++;
		for ($c=0; $c < $fields; $c++) {
			$sheet->write($r, $c, $row[$c]);
		}
	}
	// send the file
	$xls->close();
}


?>