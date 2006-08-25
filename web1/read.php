<?php
#
# VMPS: read.php
#
#  2006.05.25/Sean Boran: Remove need for register_globals
#    Add debug1()
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 Swisscom
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
##########################

#$debug_flag1=true;
$debug_flag1=false;

include('config.inc');

$fields[$i] = array("name", "Hostname","or part of hostname"); $i++;
$fields[$i] = array("description", "NT Account<br>(of the owner)","option"); $i++;
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


// ----------- main () -------------------
echo '<head><title>VMPS Webquery</title></head><body>';
vmps_header();
db_connect();

#phpinfo();
debug1("Submit=".$_REQUEST['submit']);
#$submit = $_REQUEST['submit'];
$submit = validate_webinput($_REQUEST['submit']);

if ($submit) {
  foreach ($fields as $field) {
    $fieldname = $field[0];
    #$fieldvalue = validate_input($_REQUEST[$fieldname]);
    $fieldvalue = validate_webinput($_REQUEST[$fieldname]);
    debug1("$fieldname = $fieldvalue <br>");

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
    #$query = "SELECT * FROM systems WHERE $where ;";
    $query = "SELECT * FROM systems WHERE $where ORDER BY LastSeen DESC";

    debug1($query);
    $mysql_res = mysql_query($query);
    if (mysql_num_rows($mysql_res) > 0) {
      echo "<table cellspacing=0 cellpadding=5 border=1>\n";
      echo "<tr><th>OS<th>Hostname<th>Owner<th>MAC<th>Last IP<th>VLAN<th>Standard<br>location<th>LastSeen\n";

      while ($row = mysql_fetch_array($mysql_res,MYSQL_ASSOC)) {
        echo '<tr bgcolor="'.get_vlan_color($row['vlan']).'">';
        echo '<td align=center><img src="os/'.$row['os'].'.gif" border=0>';
        echo '<td><b>'.$row['name'].'</b>';
        echo '<td>';
        if ($row['description']) {
          echo '<a href="mailto:'.get_user_email($row['description']).'" ';
          echo 'title="'.user_tooltip($row['description']).'" ';
          //echo '>'.$row['description']."</a>\n";
          echo '>'.get_user_name($row['description'])."</a>\n";

        } else {
          echo '<i>Unknown</i>';
        };

        echo '<td>'.$row['mac'];
        echo '<td>'.$row['r_ip'];
        echo '<td>'.get_vlan_descr($row['vlan']);
        if (! $row['building']) { $row['building'] = '' ; };
        echo '<td>'.$row['building'].' '.$row['office'];
        echo '<td>'.$row['LastSeen'];
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
  $html .= "</table>\n</form>\n<i>Please fill at least one field<br>";

echo $html;


//echo '<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>';

vmps_footer();
echo '</body></html>'
?>
