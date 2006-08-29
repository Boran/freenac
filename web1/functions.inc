<?php

function get_nmap_id($mac) {
	$sel = "SELECT id FROM nac_hostscanned WHERE mac='".$mac."';";
	$res = mysql_query($sel);
	$num = mysql_num_rows($res);

	if ($num > 0) {
		$mac = mysql_fetch_array($res);
		return($mac[0]);
	} else {
		return(FALSE);
	};
};

function get_nmap_os($nmap_ip) {
	$sel = "SELECT os FROM nac_hostscanned WHERE id='$nmap_ip';";
	$res = mysql_query($sel);
	$num = mysql_num_rows($res);

	if ($num > 0) {
		$os = mysql_fetch_array($res);
		return($os[0]);
	} else {
		return(FALSE);
	};
};

function get_nmap_mac($id) {

};

function validate_input($string) {
	rtrim($string,' ');
	if (stristr($string, ' ') OR stristr($string,';')) {
		return(-1);
	} else {
		$input = mysql_real_escape_string($string);
		return($input);
	};
};
function display_os_select() {
	$query = "SELECT * FROM sys_os ORDER BY value ASC;";
	$res = mysql_query($query);

	$html .= '<option value="">(any)</option>';
	while ($os = mysql_fetch_array($res)) {
		if ($os['value'] != '') {
			$html .= "<option value=\"".$os['id'].'">';
			$html .= $os['value'].'</option>';
		};
	};
	return($html);
};

function display_description_select() {
	$query = "SELECT * FROM users ORDER BY Surname ASC;";
	$res = mysql_query($query);

	$html .= '<option value="">(any)</option>';
	while ($user = mysql_fetch_array($res)) {
		if ($user['Surname'] != '') {
		$html .= "<option value=\"".$user['AssocNtAccount'].'">';
		$html .= $user['Surname'].' '.$user['GivenName'].', '.$user['Department'].'</option>';
		};
	};
	return($html);

};


function get_user_email($username) {
	$query = "SELECT rfc822mailbox FROM users WHERE AssocNtAccount = '$username'; ";
	$res = mysql_query($query);
	$user = mysql_fetch_array($res);

	return($user[0]);
};

function get_user_name($username) {
	$query = "SELECT * FROM users WHERE AssocNtAccount = '$username'; ";
	$res = mysql_query($query);
	$user = mysql_fetch_array($res);
	$name = $user['Surname'].' '.$user['GivenName'].', '.$user['Department'];

	return($name);
};

function user_tooltip($username) {
	$query = "SELECT * FROM users WHERE AssocNtAccount = '$username'; ";
	$res = mysql_query($query);
	$user = mysql_fetch_array($res);

//	$text = $user['GivenName'].' '.$user['Surname'].', '.$user['Department']." (";
	$text .= $user['HouseIdentifier'].' '.$user['PhysicalDeliveryOfficeName']." / ";
	if (stristr($user['TelephoneNumber'],'31-342')) {
		$user['TelephoneNumber'] = str_replace(' ','',substr($user['TelephoneNumber'],strlen($user['TelephoneNumber'])-7,7));
	};
	$text .= $user['TelephoneNumber']. " / ";
	$text .= $user['Mobile'];
//."\n";

	return($text);
};

function get_vlan_color($vlan) {
	if (($vlan == 0) || ($vlan == 900)) {
		return('#CCCCCC');
	};
	if ($vlan < 9) {
		return('#FFCC66');
	};
	if ($vlan < 500) {
		return('#FFFFCC');
	};
	return('#CCFFFF');
};

function format_mac($macdouble) {
        $numbers = explode(':',$macdouble);

        $dot=1;
        foreach($numbers as $value) {
                $mac .= $value;
                if ($dot == 1) {
                        $dot = 0;
                } else {
                        $dot = 1;
                        $mac .= '.';
                }
        };
        $mac = rtrim($mac,'.');
        return($mac);

};

function get_vlan_descr($vlan) {
	if ($vlan) {
		$query = "SELECT value FROM vlan WHERE id = $vlan"; // use SELECT vlan_description for longer
//		echo $query;
		$descr = mysql_fetch_array(mysql_query($query));
		return($descr[0]);
	} else {
		return('N/A');
	};
};

function get_location($switch_ip,$port) {
	$query = "SELECT * FROM patchcable WHERE nach_switch = '$switch_ip' AND nach_port = '$port';";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0) {
		$port = mysql_fetch_array($res);
		return($port['von_office']);
	} else {
		return(FALSE);
	};
};

function get_switch_name($switch_ip) {
	$query = "SELECT name FROM switch WHERE ip = '$switch_ip';";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0) {
		$port = mysql_fetch_array($res);
		return($port[0]);
	} else {
		return(FALSE);
	};
};


?>
