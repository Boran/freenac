<?php
#
#  functions.inc
#
#  2006.05.25/Sean Boran: Production
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 FreeNAC
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
####################################


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
  echo '<p ALIGN=CENTER><a href="./index.html">VMPS Menu</a></p>';
}

function vmps_footer()
{
  echo '<p ALIGN=CENTER><a href="./index.html">VMPS Menu</a></p>';
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

function display_os_select() {
  $query = "SELECT * FROM sys_os ORDER BY value ASC;";
  $res = mysql_query($query);

  $html .= '<option value="">(any)</option>';
  while ($os = mysql_fetch_array($res)) {
    if ($os['value'] != '') {
      $html .= "<option value=\"". $os['id']. '">';
      $html .= $os['value']. '</option>';
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
      $html .= "<option value=\"". $user['AssocNtAccount']. '">';
      $html .= $user['Surname']. ' '. $user['GivenName'].', ';
      $html .= $user['Department']. '</option>';
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

  //$text = $user['GivenName'].' '.$user['Surname'].', '.$user['Department']." (";
  $text .= $user['HouseIdentifier'].' '.$user['PhysicalDeliveryOfficeName']." / ";

  // Shorter tel number for INO
  if (stristr($user['TelephoneNumber'],'31-342')) {
    $user['TelephoneNumber'] = str_replace(' ','',substr($user['TelephoneNumber'],strlen($user['TelephoneNumber'])-7,7));
  };

  $text .= $user['TelephoneNumber']. " / ";
  $text .= $user['Mobile'];
  //."\n";

  return($text);
};

function get_vlan_color($vlan) {
  if (($vlan == 0) || ($vlan == 900)) { return('#CCCCCC'); };
  if ($vlan < 9)                      { return('#FFCC66'); };
  if ($vlan < 500)                    { return('#FFFFCC'); };

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
    $query = "SELECT vlan_description FROM vlan WHERE id = $vlan";
    //echo $query;
    $descr = mysql_fetch_array(mysql_query($query));
    return($descr[0]);

  } else {
    return('N/A');
  };
};

?>
