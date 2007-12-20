<?php
/**
 * /opt/nac/bin/funcs.inc
 *
 * Long description for file:
 * common PHP functions used by several scripts
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./");
require_once "../etc/config.inc";

function db_connect($username,$password)
{
  global $connect, $dbhost, $dbname;

  $connect=mysql_connect($dbhost, $username, $password)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}

function ismemberof($username,$group)
{
   ## This function says whether a username in the form user@domain.com is belongs to the AD group specified
   global $ad_server,$ad_user,$ad_password,$ad_port,$ad_base;
   $counter=0;
   $ad=ldap_connect($ad_server,$ad_port);
   if ($ad)
   {
      if (ldap_bind($ad,$ad_user,$ad_password))
      {
         if ($ad_port==3268) 		## Global catalog is more flexible. 
                             		## In the event that we have more than one domain, 
                             		## the username gives us the domain name 
                             		## where we should look at.
         {
            ## Build the DN out of the username
            $dn=explode('@',$username);
            if (empty($dn))
               return false;
            $temp_dn=str_replace('.',',DC=',$dn[1]);
            $dn='DC='.$temp_dn;
         }
         else
            ## We are using a domain-level lookup, so in this case we need to know where the user information is 
            $dn=$ad_base;
         $attributes=array('memberOf');  ## We are interested only in the memberOf attribute
         $filter='(&(userPrincipalName='.$username.')(objectClass=person))'; ## Retrieve info only for this specific username
         $result=ldap_search($ad,$dn,$filter,$attributes);   ## Do the search
         if ($result)
         {
            $entries=ldap_get_entries($ad,$result);
            if ($entries['count']==1)    ## We should have only one result
            {
               $groups=$entries[0]['memberof']['count'];
               if ($groups>0)
               {
                  for ($i=0;$i<$groups;$i++)
                  {
                     if (strcasecmp($entries[0]['memberof'][$i],$group) == 0)
                        $counter++;  	## Username is a member of the group
                  }
               }
            }
         }
      }
      else
      {
         echo 'Unable to bind to LDAP server with given credentials';
      }
      ldap_close($ad);
   }
   else
   {
      echo 'Unable to connect to LDAP server';
   }
   if ($counter>0)
      return true;
   else
      return false;
}

function user_rights($username)
#This function outputs the rights associated to the username
{
   global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);
   $query='select * from guirights order by code desc;';
   $res=mysql_query($query);
   $rights=-1;
   while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
   {
      if ( ! empty($result['ad_group']))
         if (ismemberof($username,$result['ad_group']))
         {
            $rights<$result['code'] ? $rights=$result['code'] : $result['code']=$rights;
            break;
         }
   }
   return $rights;
}

function print_header($entityname, $xls_output){
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
		<table class="bw" width="1000" border="0">
		  <tr>
			<td><a href="./index.html"><img src="./images/logo_small.png" border="0" /></a></td>
                        <td align="right">
                           <a href="http://www.freenac.net">FreeNAC website</a><br />
                           <a href="http://www.freenac.net/en/installguide">Documentation</a><br />
                           <a href="http://www.freenac.net/phpBB2">Support</a>
                        </td>
		  </tr>
		  <tr>
			<td class="center" colspan="2"><a href="'.$_SERVER['PHP_SELF'].'">List Unknowns</a> | <a href="'.$_SERVER['PHP_SELF'].'?action=search">Search</a>';
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

function header_read()
{
   global $entityname;
   if (!defined(HEADER))
   {
      $ret='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
               <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
               <title>FreeNAC @'.$entityname.'</title>
               <link href="bw.css" rel="stylesheet" type="text/css" />
            </head>
            <body>';
      define('HEADER',true);
      return $ret;
   }
}

function main_stuff()
{
   $text=<<<EOF
   <table class="bw" border="0" align="center" width="500">
   <tr height="80">
      <td valign="center"><a href="./index.html"><img src="./images/logo_small.png" border="0" /></a></td>
      <td align="right">
         <a href="http://www.freenac.net">FreeNAC website</a><br />
         <a href="http://www.freenac.net/en/installguide">Documentation</a><br />
         <a href="http://www.freenac.net/phpBB2">Support</a>
      </td>
   </tr>
   <tr valign="top">
      <td colspan="2">

EOF;
   return $text;
}

function read_footer()
{
   $text=<<<EOF
      <p ALIGN=CENTER><a href="./index.php">NAC Menu</a></p>
      </td>
   </tr>
   </table>
   </body>
   </html>
EOF;
   return $text;
}

function user_tooltip($username) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
  $query = "SELECT * FROM users WHERE username = '$username'; ";
  $res = mysql_query($query)  or die ("Unable to query MySQL");

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

//
// Print the lookup results
//
function print_resultset($res,$server){
        $ret='';
        while ($row=mysql_fetch_array($res)){
                $ret.=($i%2==0)?'<tr class="light">':'<tr class="dark">';
                $ret.='<td><a href="'.$server['PHP_SELF'].'?action=edit&id='.$row['id'].'">';
                // if we have a name, echo the name, else just the id
                $ret.=($row['name']!=''?stripslashes($row['name']):$row['id']);
                $ret.='</a></td>'."\n";
                $ret.='<td class="center">'.$row['mac'].'</td>'."\n";
                $ret.='<td class="center">'.ucfirst($row['status']).'</td>'."\n";
                $ret.='<td class="center" title="VLAN '.$row['vlan'].'">'.$row['vlanname'].'</td>'."\n";
                $ret.='<td class="center">'.($row['lastvlan']!=''?$row['lastvlan']:'&nbsp;').'</td>'."\n";
                $ret.='<td class="center" title="'.$row['surname'].' '.$row['givenname'].'">'.$row['user'].'</td>'."\n";
                $ret.='<td class="center">'.$row['port'].'</td>'."\n";
                $ret.='<td class="center">'.$row['lastseen'].'</td>'."\n";
                $ret.='<td class="center" title="'.$row['switchip'].'">'.$row['switch'].'</td>'."\n";
                $ret.='<td class="center">'.($row['lastip']!=''?$row['lastip']:'&nbsp;').'</td>'."\n";
		$ret.='<td class="center">'.($row['scan']!=''?$row['scan']:'&nbsp;').'</td>'."\n";
                $ret.='<td class="center">'.($row['epo']!=''?$row['epo']:'&nbsp;').'</td>'."\n";
                $ret.='<td class="center">'.($row['wsus']!=''?$row['wsus']:'&nbsp;').'</td>'."\n";
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
                        $value = @stripslashes($value);
                }
                if (!is_numeric($value)){
                        $value = @mysql_real_escape_string($value);
                }
        }
        return trim($value);
}



//
// retrieves all users from the databases and returns them as a hmtl dropdownlist
//
function get_userdropdown($selected_user) {
        global $rights;
        if ($rights>=2)
        {
           $ret='<select name="username">';
           $sql='SELECT id, username, surname, givenname FROM users ORDER BY surname'; // Get details for all users
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($r['id']==$selected_user?'selected="selected"':'').'>'.$r['surname'].' '.$r['givenname'].'</option>'."\n";
                }
           }
           $ret.='</select>'."\n";
        }
        else
        {
           $sql="select username from users where id='$selected_user';";
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0)
                while ($r=mysql_fetch_array($res))
                   $ret=$r['username'];
        }
        return $ret;
}

//
// retrieves all offices from the database and returns them as a html dropdownlist
//
function get_officedropdown($oid){
        $ret='<select name="office">';
        $sql='SELECT loc.id, loc.name as office, b.name as building FROM location as loc LEFT JOIN building as b on loc.building_id=b.id ORDER BY building, office'; // Get details for all offices
        $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
        if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($r['id']==$oid?'selected="selected"':'').'>'.$r['building'].' -  '.$r['office'].'</option>'."\n";
             }
        }
        $ret.='</select>'."\n";
        return $ret;
}

//
// retrieves the different status and returns them as a html dropdownlist
//
function get_status($s)
{
        global $rights;
        if ($rights>=2)
        {
           $ret='<select name="status">';
           $sql='SELECT id, value FROM vstatus ORDER BY value ASC;';
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($s==$r['id']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
                }
           }
           $ret.='</select>'."\n";
        }
        else
        {
          $sql="select value from vstatus where id='$s';";
          $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
          if (mysql_num_rows($res)>0)
                while ($r=mysql_fetch_array($res))
                   $ret=$r['value'];
        }
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


$big_sel = "SELECT s.id as sid, inventory as inventory, s.mac as mac, v.default_id as vlan, v.default_name as vlanname, vstatus.value as status,
	s.name as ComputerName, u.username as user, s.comment, s.ChangeDate, LastSeen, LastPort, sysbd.name as building, 
	CONCAT(sysbd.name,' ',sysloc.name) as officeSoll, patchloc.name as officeIst,
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
   global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);
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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
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

function display_os_select($selected) {
  global $connect;
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass); 

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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);

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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);

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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);

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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);

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
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
  $query = "SELECT rfc822mailbox FROM users WHERE id = '$username'; ";
  $res = mysql_query($query)  or die ("Unable to query MySQL ($query)");
  $user = mysql_fetch_array($res);

  return($user[0]);
};

function get_user_name($username) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
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

$netmask[32] = '255.255.255.255';
$netmask[31] = '255.255.255.254';
$netmask[30] = '255.255.255.252';
$netmask[29] = '255.255.255.248';
$netmask[28] = '255.255.255.240';
$netmask[27] = '255.255.255.224';
$netmask[26] = '255.255.255.192';
$netmask[25] = '255.255.255.128';
$netmask[24] = '255.255.255.0';
$netmask[23] = '255.255.254.0';
$netmask[22] = '255.255.252.0';
$netmask[21] = '255.255.248.0';
$netmask[20] = '255.255.240.0';
$netmask[19] = '255.255.224.0';
$netmask[18] = '255.255.192.0';
$netmask[17] = '255.255.128.0';
$netmask[16] = '255.255.0.0';
$netmask[15] = '255.254.0.0';
$netmask[14] = '255.252.0.0';
$netmask[13] = '255.248.0.0';
$netmask[12] = '255.240.0.0';
$netmask[11] = '255.224.0.0';
$netmask[10] = '255.192.0.0';
$netmask[9] = '255.128.0.0';
$netmask[8] = '255.0.0.0';
$netmask[7] = '254.0.0.0';
$netmask[6] = '252.0.0.0';
$netmask[5] = '248.0.0.0';
$netmask[4] = '240.0.0.0';
$netmask[3] = '224.0.0.0';
$netmask[2] = '192.0.0.0';
$netmask[1] = '128.0.0.0';

/**
 * Format mac from aa:bb:cc:dd:ee:ff to aabb.ccdd.eeff
 * @param string $macdouble MAC (aa:bb:cc:dd:ee:ff)
 * @return string $mac MAC (aabb.ccdd.eeff)
 */
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

/**
 * Format mac from aabb.ccdd.eeff to aa:bb:cc:dd:ee:ff
 * @param string $macdot MAC (aa:bb:cc:dd:ee:ff)
 * @return string $mac MAC (aabb.ccdd.eeff)
 */
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
   global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);
    $query = "SELECT vlan_description FROM vlan WHERE id = '$vlan'";
    echo $query;
    //echo $query;
    $descr = mysql_fetch_array(mysql_query($query));
    return($descr[0]);

  } else {
    return('N/A');
  };
};

function get_locationid($port) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
  $query = "SELECT patchloc.id as patchlocid, switchloc.id as switchlocid FROM port
                  LEFT JOIN patchcable ON patchcable.port = port.id 
                   LEFT JOIN location patchloc ON patchloc.id = patchcable.office 
                  LEFT JOIN switch ON switch.id = port.switch 
                   LEFT JOIN location switchloc ON switchloc.id = switch.location WHERE port.id = '$port';";
  $res = mysql_query($query);
  if (mysql_num_rows($res) > 0) {
	$loc = mysql_fetch_array($res);
		if (($loc['patchlocid'] = '') || (!$loc['patchlocid'])) {
			return($loc['switchlocid']);
		} else {
			return($loc['patchlocid']);
		};
	} else {
		return(0);
	};
};

function get_location($port) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
  $query = "SELECT patchloc.id as patchlocid, CONCAT(patchbd.name,' ',patchloc.name) as patchloc,
                   switchloc.id as switchlocid, CONCAT(switchbd.name,' ',switchloc.name) as switchloc
            FROM port
                  LEFT JOIN patchcable ON patchcable.port = port.id 
                   LEFT JOIN location patchloc ON patchloc.id = patchcable.office 
                   LEFT JOIN building as patchbd ON patchbd.id = patchloc.building_id
                  LEFT JOIN switch ON switch.id = port.switch 
                   LEFT JOIN location switchloc ON switchloc.id = switch.location
                   LEFT JOIN building as switchbd ON switchbd.id = switchloc.building_id
            WHERE port.id = '$port';";
  $res = mysql_query($query);
  if (mysql_num_rows($res) > 0) {
	$loc = mysql_fetch_array($res);
		if (($loc['patchloc'] = '') || (!$loc['patchloc'])) {
			return($loc['switchloc']);
		} else {
			return($loc['patchloc']);
		};
	} else {
		return(0);
	};
};

function print_location($locationid) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
  $query = "SELECT building.name as building, location.name as office FROM location
                LEFT JOIN building on building.id = location.building_id
            WHERE location.id = '$locationid';";
  $res = mysql_query($query);
  if (mysql_num_rows($res) > 0) {
	$loc = mysql_fetch_array($res);
	return($loc['building'].' '.$loc['office']);
  } else {
	return('?');
  };
};


function get_switch_name($switch_ip) {
  global $dbuser,$dbpass;
  db_connect($dbuser,$dbpass);
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
  global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);

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
	global $dbuser,$dbpass;
        db_connect($dbuser,$dbpass);
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
