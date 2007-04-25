<?php


chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');

include_once('print.inc');

$fields[$i] = array("name", "Hostname","INOSSMsean1"); $i++;
$fields[$i] = array("uid", "Username<br>(of the owner)","option"); $i++;
$fields[$i] = array("inventar", "Inventory #","8361226 or <br><i>(12345679</i>"); $i++;
$fields[$i] = array("mac", "MAC Adress<br>(ethernet)","0020.e068.dfb1 or <br> 00:20:e0:68:df:b1"); $i++;
$fields[$i] = array("r_ip", "Last IP Address","193.5.227.123"); $i++;
$fields[$i] = array("os", "Operating System","option"); $i++;


function display_forms() 
{
        global $fields;

        $html .= "<form method=get action=$PHP_SELF>\n<table cellspacing=0 cellpadding=5 border=1>";
        foreach ($fields as $field) {
                $fieldname = $field[0];
                $html .= '<tr><th>'.$field[1];
                $html .= '<td><input type="text" name ="'.$field[0].'" value="'.$fieldname.'">';
                $html .= '<td><i>'.$field[2]."\n";
        };
        $html .= "<tr><td colspan=3 align=center><input type=submit name=submit value=submit>";
        $html .= "</table>\n</form>\n<i>Please fill at least one field";

        return($html);
}


function read_stuff()
{
   global $readuser,$readpass,$fields;
   db_connect($readuser,$readpass);

   foreach ($fields as $field) {
	$varname = $field[0];
	$varname = $_GET[$varname];
	$varname = mysql_real_escape_string($varname);
   };
   $submit = $_GET['submit'];


   // if the user submitted something, process and display
   if ($submit) {
   // make the query
	foreach ($fields as $field) {
		$fieldname = $field[0];
		$fieldvalue = validate_input($_GET[$fieldname]);
		if (($fieldvalue != '') && ($fieldvalue != -1)) {
			$notnull = TRUE;
   #			if ($fieldname == 'description') { $fieldvalue = strtoupper($fieldvalue); };
			if (($fieldname == 'mac') || ($fieldname == 'uid')) {
				if (stristr($fieldvalue,':')) {
					$fieldvalue = format_mac($fieldvalue);
				};
				$where .= "($fieldname = '$fieldvalue') AND ";	
			} else {
				$where .= "($fieldname LIKE '%$fieldvalue%') AND ";
			};
		};
	};

   // if there is an actual query, display the host
	if ($notnull) {
		$where .= '(1 = 1)';
		echo print_host_table($where);
                echo "\n<p><hr><p>\n";
    } else {
		echo "<i>You cannot display the entire database. Please limit your query.</i>\n";
	};
   };


   //echo display_forms();

	$html .= "<form method=get action=".$_SERVER['PHP_SELF'].">\n<table cellspacing=0 cellpadding=5 border=1 align=\"center\">";
	foreach ($fields as $field) {
		$fieldname = $field[0];
		$html .= '<tr><th>'.$field[1];
		$html .= '<td>';
		if ($field[2] == 'option') {
			$html .= "<select name=\"$fieldname\">";
			$funcname = 'display_'.$fieldname.'_select';
			$html .= $funcname($$fieldname);
			$html .= "</select>\n";
		} else {
			$html .= '<input type="text" name ="'.$field[0].'" value="'.$$fieldname.'">';
			$html .= '<td><i>'.$field[2]."\n";
		};
	};
	$html .= "<tr><td colspan=3 align=center><input type=reset name=reset value=reset>&nbsp;<input type=submit name=submit value=submit>";
	$html .= "</table>\n</form>\n<i>Please fill at least one field";

   echo $html;

//vmps_footer(); 
//echo '</body></html>';
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo "<hr /><br />";
      read_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   read_stuff();
   echo read_footer();
}

?>
