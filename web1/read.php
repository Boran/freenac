<?php

include_once('config.inc');
include_once('functions.inc');
include_once('print.inc');

$fields[$i] = array("name", "Hostname","INOSSMsean1"); $i++;
$fields[$i] = array("description", "NT Account<br>(of the owner)","option"); $i++;
$fields[$i] = array("inventar", "Inventory #","8361226 or <br><i>(12345679</i>"); $i++;
$fields[$i] = array("mac", "MAC Adress<br>(ethernet)","0020.e068.dfb1 or <br> 00:20:e0:68:df:b1"); $i++;
$fields[$i] = array("r_ip", "Last IP Address","193.5.227.123"); $i++;
$fields[$i] = array("os", "Operating System","option"); $i++;

function display_forms() {
	global $fields;

	$html .= "<form method=get action=$PHP_SELF>\n<table cellspacing=0 cellpadding=5 border=1>";
	foreach ($fields as $field) {
		$fieldname = $field[0];
		$html .= '<tr><th>'.$field[1];
		$html .= '<td><input type="text" name ="'.$field[0].'" value="'.$$fieldname.'">';
		$html .= '<td><i>'.$field[2]."\n";
	};
	$html .= "<tr><td colspan=3 align=center><input type=submit name=submit value=submit>";
	$html .= "</table>\n</form>\n<i>Please fill at least one field";

	return($html);
};

db_connect();

foreach ($fields as $field) {
	$varname = $field[0];
	$$varname = $_GET[$varname];
	$$varname = mysql_real_escape_string($$varname);
};
$submit = $_GET['submit'];

if ($submit) {
	echo '<head><title>VMPS Webquery</title></head><body>';

	foreach ($fields as $field) {
		$fieldname = $field[0];
		$fieldvalue = validate_input($$fieldname);
		if (($fieldvalue != '') && ($fieldvalue != -1)) {
			$notnull = TRUE;
			if ($fieldname == 'description') { $fieldvalue = strtoupper($fieldvalue); };
			if ($fieldname == 'mac') {
				if (stristr($fieldvalue,':')) {
					$fieldvalue = format_mac($fieldvalue);
				};
				$where .= "($fieldname = '$fieldvalue') AND ";	
			} else {
				$where .= "($fieldname LIKE '%$fieldvalue%') AND ";
			};
		};
	};

	if ($notnull) {
		$where .= '(1 = 1)';
		$query = "SELECT * FROM systems WHERE $where ;";
		//echo $query.'<br>';
		$mysql_res = mysql_query($query);
		if (mysql_num_rows($mysql_res) > 0) {
			echo "<table cellspacing=0 cellpadding=5 border=1>\n";
			echo "<tr><th>OS<td>Nmap<td>ePO<th>Hostname<th>Owner<th>Inventar<th>MAC<th>Last IP<th>VLAN<th>Standard<br>location<th colspan=2>LastSeen\n";
			while ($row = mysql_fetch_array($mysql_res,MYSQL_ASSOC)) {
				echo '<tr bgcolor="'.get_vlan_color($row['vlan']).'">';
				echo '<td align=center><img src="os/'.$row['os'].'.gif" border=0>';
// extra details
				$mac = $row['mac'];
				echo '<td align=center>';
				if (get_nmap_id($mac)) { echo 'X'; };

				echo '<td align=center>';
				if (mysql_num_rows(mysql_query("SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'")) > 0) { echo 'X'; };
				
// name => print details link
				echo '<td><b><a href="display.php?single_host='.$row['mac'].'">';
				echo $row['name'].'</a></b>';
// owner => email link
				echo '<td>';
				  if ($row['description']) {
					echo '<a href="mailto:'.get_user_email($row['description']).'" ';
					echo 'title="'.user_tooltip($row['description']).'" ';
					//echo '>'.$row['description']."</a>\n";
					echo '>'.get_user_name($row['description'])."</a>\n";
				  } else {
					echo '<i>Unknown</i>';
				  };
				echo '<td>'.$row['inventar'];
				echo '<td>'.$row['mac'];
				echo '<td>'.$row['r_ip'];
				echo '<td>'.get_vlan_descr($row['vlan']); // get_vlan_short(get_vlan_descr
					if (! $row['building']) { $row['building'] = 'Ber-Omu93' ; };
				echo '<td>'.$row['building'].' '.$row['office'];
				echo '<td>'.get_location($row['switch'],$row['port']).'<td>'.$row['LastSeen'].'</font>';
				echo "\n";
			};
			echo "</table>\n";
		} else {
			echo '<i>No record found</i>';
		};
	};
	echo "\n<p><hr><p>\n";
  };

//echo display_forms();

	$html .= "<form method=get action=$PHP_SELF>\n<table cellspacing=0 cellpadding=5 border=1>";
	foreach ($fields as $field) {
		$fieldname = $field[0];
		$html .= '<tr><th>'.$field[1];
		$html .= '<td>';
		if ($field[2] == 'option') {
			$html .= "<select name=\"$fieldname\">";
			$funcname = 'display_'.$fieldname.'_select';
			$html .= $funcname();
			$html .= "</select>\n";
		} else {
			$html .= '<input type="text" name ="'.$field[0].'" value="'.$$fieldname.'">';
			$html .= '<td><i>'.$field[2]."\n";
		};
	};
	$html .= "<tr><td colspan=3 align=center><input type=reset name=reset value=reset>&nbsp;<input type=submit name=submit value=submit>";
	$html .= "</table>\n</form>\n<i>Please fill at least one field";

echo $html;



/*
foreach ($fields as $field) {
	$allnull = TRUE;
	$fieldname = $field[0]
	if ($$fieldname != '' {
		$searchfields[$i] = $fieldname;
		$allnull = FALSE;
	};
};

if ((! $submit) || ($allnull)) {
	echo display_forms();
} else {
	echo 'Search for ';
	foreach ($searchfields as $field) {
		$fieldname = $field[0];
		$value = $$fieldname;
		echo "<li>$fieldname = $value";
	};
};
*/

//echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';
echo '</body></html>'

?>
