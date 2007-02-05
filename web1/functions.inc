<?php
#
#  functions.inc
#
#  2006.05.25/Sean Boran: Production
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 Swisscom
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
####################################


$big_sel = "SELECT s.id as sid, inventory as inventory, s.mac as mac, v.default_id as vlan, v.default_name as vlanname, vstatus.value as status,
	s.name as ComputerName, u.username as user, s.comment, s.ChangeDate, LastSeen, sysbd.name as building, 
	sysloc.name as officeSoll, patchloc.name as officeIst,
	pat.outlet as PatchSocket, patchloc.name as PatchCable,
	p.name as port, sw.name as switch,
	s.class as class, c1.value as classname,
	s.class2 as class2, c2.value as class2name,
	os.value as os, os.value as osname, os1.value as os1, os2.value as os2, os3.value as os3
FROM systems s
LEFT JOIN vstatus ON vstatus.id = s.status
LEFT JOIN vlan v ON v.id = s.LastVlan
LEFT JOIN port p ON p.id = s.LastPort
LEFT JOIN switch sw ON sw.id = p.switch
LEFT JOIN patchcable pat ON pat.port = p.id
	LEFT JOIN location as patchloc ON patchloc.id = pat.office
LEFT JOIN users u ON u.id = s.uid
LEFT JOIN sys_class c1 ON c1.id = s.class
LEFT JOIN sys_class2 c2 ON c2.id = s.class2
LEFT JOIN sys_os os ON os.id = s.os
LEFT JOIN sys_os1 os1 ON os1.id = s.os1
LEFT JOIN sys_os2 os2 ON os2.id = s.os2
LEFT JOIN sys_os3 os3 ON os3.id = s.os3
LEFT JOIN location sysloc ON sysloc.id = s.office
	LEFT JOIN building sysbd ON sysbd.id = sysloc.building_id";


function db_connect()
{
  global $connect, $dbhost, $dbuser, $dbpass, $dbname;
  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}


function vmps_header()
{
  echo '<p ALIGN=CENTER><a href="./index.html">NAC Menu</a></p>';
}

function vmps_footer()
{
  echo '<p ALIGN=CENTER><a href="./index.html">NAC Menu</a></p>';
}



function debug1($msg) {
  global $debug_flag1, $debug_to_syslog;
  $msg=rtrim($msg);
  if (($debug_flag1==TRUE) && (strlen($msg)>0) ) {
    if ($debug_to_syslog===TRUE) {
      syslog(LOG_INFO, "Debug1: $msg");
    } else {
      echo "Debug1: $msg<br>";
    }
  }
}
function debug2($msg) {
  global $debug_flag2, $debug_to_syslog;
  $msg=rtrim($msg);
  if (($debug_flag2==TRUE) && (strlen($msg)>0) ) {
    if ($debug_to_syslog===TRUE) {
      syslog(LOG_INFO, "Debug2: $msg");
    } else {
      echo "Debug2: $msg<br>";
    }
  }
}

function get_nmap_id($sid) {
  db_connect();
        $sel = "SELECT id FROM nac_hostscanned WHERE sid='".$sid."';";
        $res = mysql_query($sel)  or die ("Unable to query MySQL ($sel)");
        $num = mysql_num_rows($res);

        if ($num > 0) {
                $mac = mysql_fetch_array($res);
                return($mac[0]);
        } else {
                return(FALSE);
        };
};

function get_nmap_os($nmap_ip) {
  db_connect();
        $sel = "SELECT os FROM nac_hostscanned WHERE id='$nmap_ip';";
        $res = mysql_query($sel)  or die ("Unable to query MySQL ($sel)");
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


// not used
function validate_input($string) {
  rtrim($string,' ');
  if (stristr($string, ' ') OR stristr($string,';')) {
    return(-1);
  } else {
    $input = mysql_real_escape_string($string);
    return($input);
  };
};

function validate_webinput($string)
{
  rtrim($string,' ');
  if (get_magic_quotes_gpc()) {
    $value = stripslashes($string);
  }

  // Remove dodgy characters by escaping them
  if (!is_numeric($string)) {
    $string = mysql_real_escape_string($string);
    // Quote as well?
    #$string = "'" . mysql_real_escape_string($string) . "'";
  }
  return $string;
}

function display_os_select($selected) {
  global $connect;
  db_connect();

  $query = "SELECT * FROM sys_os ORDER BY value ASC;";
  $res = mysql_query($query, $connect)  or die ("Unable to query MySQL ($query)");

  $html .= '<option value="">(any)</option>';

  if (mysql_num_rows($res) > 0) {
	  while ($os = mysql_fetch_array($res)) {
	    if ($os['value'] != '') {
	      $html .= "<option ";
		  if (($selected == $os['id']) && $selected != '') { $html .= "selected "; };
	      $html .= "value=\"". $os['id']. '">';
	      $html .= $os['value']. '</option>';
	    };
	  };
  };
  return($html);
};

function display_class_select($selected) {
  global $connect;
  db_connect();

  $query = "SELECT * FROM sys_class ORDER BY id ASC;";
  $res = mysql_query($query, $connect)  or die ("Unable to query MySQL ($query)");

  $html .= '<option value="">(any)</option>';
  if (mysql_num_rows($res) > 0) {
	  while ($os = mysql_fetch_array($res)) {
	    if ($os['value'] != '') {
	      $html .= "<option ";
		  if (($selected == $os['id']) && $selected != '') { $html .= "selected "; };
	      $html .= "value=\"". $os['id']. '">';
	      $html .= $os['value']. '</option>';
	    };
	  };
  };
  return($html);
};

  
function display_class2_select($selected) {
  global $connect;
  db_connect();

  $query = "SELECT * FROM sys_class2 ORDER BY id ASC;";
  $res = mysql_query($query, $connect)  or die ("Unable to query MySQL ($query)");

  $html .= '<option value="">(any)</option>';
  if (mysql_num_rows($res) > 0) {
  while ($os = mysql_fetch_array($res)) {
    if ($os['value'] != '') {
      $html .= "<option ";
          if (($selected == $os['id']) && $selected != '') { $html .= "selected "; };
      $html .= "value=\"". $os['id']. '">';
      $html .= $os['value']. '</option>';
    };
  };
  };
  return($html);
};  


function display_vlan_select($selected) {
  global $connect;
  db_connect();

  $query = "SELECT * FROM vlan ORDER BY id ASC;";
  $res = mysql_query($query, $connect)  or die ("Unable to query MySQL ($query)");

  $html .= '<option value="">(any)</option>';
  if (mysql_num_rows($res) > 0) {
  while ($os = mysql_fetch_array($res)) {
    if ($os['value'] != '') {
      $html .= "<option ";
	  if (($selected == $os['id']) && $selected != '') { $html .= "selected "; };
      $html .= "value=\"". $os['vlan_description']. '">';
      $html .= $os['value']. '</option>';
    };
  };
  };
  return($html);
};

function display_uid_select($selected) {
  global $connect;
  db_connect();

  $query = "SELECT * FROM users ORDER BY Surname ASC;";
  $res = mysql_query($query, $connect)  or die ("Unable to query MySQL ($query)");

  $html .= '<option value="">(any)</option>';
  if (mysql_num_rows($res) > 0) {
  while ($user = mysql_fetch_array($res)) {
    if ($user['Surname'] != '') {
      $html .= "<option ";
	  if (($selected == $user['id']) && $selected != '') { $html .= "selected "; };
	  $html .= "value=\"". $user['id']. '">';
      $html .= $user['Surname']. ' '. $user['GivenName'].', ';
      $html .= $user['Department']. '</option>';
    };
  };
  };
  return($html);
};


function get_user_email($username) {
  db_connect();
  $query = "SELECT rfc822mailbox FROM users WHERE id = '$username'; ";
  $res = mysql_query($query)  or die ("Unable to query MySQL ($query)");
  $user = mysql_fetch_array($res);

  return($user[0]);
};

function get_user_name($username) {
  db_connect();
  $query = "SELECT * FROM users WHERE id = '$username'; ";
  $res = mysql_query($query)  or die ("Unable to query MySQL ($query)");
  if (mysql_num_rows($res) > 0) {
  $user = mysql_fetch_array($res);
	  $name = $user['Surname'].' '.$user['GivenName'].', '.$user['Department'];
	  return($name);
  } else {
	  return($username);
  };
};

function get_vlan_color($vlan) {
/*
  if (($vlan < 2) || ($vlan > 900)) { return('#CCCCCC'); };
  if ($vlan < 500)                    { return('#FFCC66'); };
  if ($vlan == 501)                       { return ('#CC33FF'); };
  if ($vlan == 520 )                       { return ('#C0C0C0');};
  if ($vlan == 521 )                       { return ('#99CCFF');};
  if ($vlan == 522 )                       { return ('#99FF99');};
  if ($vlan == 523 )                       { return ('#FFFF99');};
  if (($vlan == 524) || ($vlan == 525)) { return('#9966CC'); };
  if ($vlan == 526)                     { return ('#99CC66');};
  if ($vlan > 580)                    	{ return('#FF9933'); };
// if ($vlan == )			{ return ('#');
*/
  return('#FFFFFF');
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

function reformat_mac($macdot) {
  $numbers = explode('.',$macdot);

  $value = $numbers[0].$numbers[1].$numbers[2];
  for ($i=0; $i <= 6; $i++) {
	$mac .= substr($value,$i*2,2).':';
  };

  $mac = rtrim($mac,':.');
  return($mac);


};

function get_vlan_descr($vlan) {
  if ($vlan) {
    db_connect();
    $query = "SELECT vlan_description FROM vlan WHERE id = $vlan";
    //echo $query;
    $descr = mysql_fetch_array(mysql_query($query));
    return($descr[0]);

  } else {
    return('N/A');
  };
};

function get_location($port) {
  db_connect();
        $query = "SELECT location.name as office FROM port LEFT JOIN patchcable ON patchcable.port = port.id LEFT JOIN location ON location.id = patchcable.office WHERE port.id = '$port';";
        $res = mysql_query($query);
        if (mysql_num_rows($res) > 0) {
                $port = mysql_fetch_array($res);
                return($port['office']);
        } else {
			$query2 = "SELECT location.name as office FROM port LEFT JOIN switch ON switch.id = port.switch LEFT JOIN location ON location.id = switch.location WHERE port.id = '$port';";
			$res2 =  mysql_query($query2);
			if (mysql_num_rows($res2) > 0) {
				$switch = mysql_fetch_array($res2);
				return($switch['location']);
			} else {
	                return(FALSE);
			};
        };
};

function get_switch_name($switch_ip) {
  db_connect();
        $query = "SELECT name FROM switch WHERE ip = '$switch_ip';";
        $res = mysql_query($query);
        if (mysql_num_rows($res) > 0) {
                $port = mysql_fetch_array($res);
                return($port[0]);
        } else {
                return(FALSE);
        };
};

function strip_datversion($long_version) {
	$list=explode('.',$long_version);
	$short_version = $list[2];
	return($short_version);
};

function get_datversions() {
	$query = "SELECT DISTINCT(DATVersion) FROM EpoComputerProperties ORDER BY DATVersion DESC;";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0) {
		while ($row = mysql_fetch_array($res)) {
			$short_version = strip_datversion($row[0]);
			if ($short_version > 0) {
				$datversions[$i] = $short_version;
				$i++;
			};
		};
		return($datversions);
	} else {
		return(FALSE);
	};
};


function get_latestdat() {
	$query = "SELECT MAX(DATVersion) FROM EpoComputerProperties;";
	$res = mysql_query($query);
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		$short_version = strip_datversion($row[0]);
		return($short_version);
	} else {
		return(0);
	};
};

function display_dat_select() {
  global $connect;
  db_connect();

  $datversions = get_datversions();
  $latestdat = get_latestdat();

  $html .= '<option value="">(any)</option>';

  foreach ($datversions as $key => $mversion) {
	  if ($key == 1) { $html .= '<option value="outdated">(Older than '.$latestdat.')</option>'; };
      $html .= "<option ";
	  if (($selected == $mversion) && $selected != '') { $html .= "selected "; };
      $html .= "value=\"". $mversion. '">';
      $html .= $mversion. '</option>';
  };
  return($html);

};

function get_actual_value($mac,$fieldname) {
	global $connect;
	db_connect();
	$sel = "SELECT $fieldname FROM systems WHERE mac = '$mac';";
	$res = mysql_query($sel);
	$list = mysql_fetch_array($res);
	return($list[0]);

};
function get_lastseen($mac) {
	global $big_sel;
	$switch = get_switch_name(get_actual_value($mac,'switch'));
	$port = get_actual_value($mac,'port');
	
	$sel = $big_sel ." WHERE s.mac='$mac'";

	$system = mysql_fetch_array(mysql_query($sel));

	$users = get_users($system['officeIst']);

	$lastseen = array("time" => $system['LastSeen'],
					  "switch" => $system['switch'].' '.$system['port'].' ('.$system['swlocation'].')', 
					  "patch" => $system['officeIst'].' ('.$system['PatchSocket'].')',
					  "users" => $users );

	return($lastseen);

};

function get_users($office) {
	$sel = "SELECT Surname, Givenname, Department FROM users WHERE PhysicalDeliveryOfficeName = '$office';";
	$res = mysql_query($sel);
	while ($usr = mysql_fetch_array($res)) {
		$users[] = $usr['Surname'].' '.$usr['Givenname'].', '.$usr['Department'];

	};
	return($users);
};
?>
