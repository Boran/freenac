<?php
#
# vmps.php
#
#  2006.05.25/Sean Boran: Production
#    Remove need for register_globals
#    Add debug1(), validate_webinput
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 FreeNAC
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
##########################

$debug_flag1=false;
#$debug_flag1=true;

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('./webfuncs.inc');

define_syslog_variables();
openlog("nac.web.read", LOG_PID, LOG_LOCAL5);


$sw='';

function print_switch_sel() {
  global $db;
  global $sw;
  global $vmpsdot_querydays;
$sel = "SELECT DISTINCT(switch.id) as id, switch.name as name, switch.ip as ip, CONCAT(building.name,' ',location.name) as location
 FROM systems
 LEFT JOIN port ON port.id = systems.LastPort
 LEFT JOIN switch ON port.switch = switch.id
 LEFT JOIN location ON location.id = switch.location
  LEFT JOIN building ON building.id = location.building_id
WHERE  (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$vmpsdot_querydays) AND port.switch != '' 
ORDER BY switch.name;";
  $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)\n");
  $html = "<select name=sw>\n";

  if (mysql_num_rows($res) > 0) {
	  while ($swi = mysql_fetch_array($res)) {
	    $html .= '<option value="'.$swi['id'].'"';
	    if ($sw == $swi['id']) {
	      $html .= ' selected ';
	    };
	    $html .='>'.$swi['name'].' ('.$swi['location'].")</option>\n";
	  };
	  $html .= "</select>\n";
  } else {
  	  $html .= '';
  };
  return($html);
};


function print_dot_sel() {
  global $dottype;
  $types = array('dot','neato','fdp','twopi','circo');
  $html = "<select name=\"dottype\">\n";

  foreach ($types as $mytype) {
    $html .= '<option value="'.$mytype.'"';
    if ($dottype == $mytype) {
      $html .= ' selected ';
    };
    $html .= ">$mytype</option>\n";
  };
  $html .= "</select>\n";
  return($html);
};
	

function vmps_stuff()
{
   global $dbuser,$dbpass,$sw,$vmpsdot_querydays;
   //-------------- main () -------------------
   db_connect($dbuser,$dbpass);
   echo "<br>";
   echo "List all ports used on the specified switch in the last $vmpsdot_querydays days, and which end-devices were seen on each port. For each end device, the node name and associated user is shown.<br>";
   echo "<br>";
   echo "<form method=get action=\"$PHP_SELF\">\n";
   echo "Select a switch from the list:<br>";
   echo print_switch_sel();
   //echo print_dot_sel();
   echo "<input type=\"submit\" name=\"submit\" value=\"View\">\n</form>\n";
   
   #$sw=$_REQUEST['sw'];
   $sw=validate_webinput($_REQUEST['sw']);
   if ($sw) {
     debug1("Calling vmpsdot.php?sw=$sw");
     echo "<img src=\"vmpsdot.php?sw=$sw\" border=0>";
   };
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo "<hr /><br />";
      vmps_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   vmps_stuff();
   echo read_footer();
}
?>
