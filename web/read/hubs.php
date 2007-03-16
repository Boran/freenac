<?php
#
# hubs.php
#
#  2006.05.25/Sean Boran: Remove need for register_globals
#    Add debug1()
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 FreeNAC
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
##########################

$debug_flag1=false;
$debug_flag1=false;

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');


function get_hosts($port) {
	$sel = "SELECT s.name, CONCAT(users.Surname, ' ',users.GivenName, ', ',users.Department) as owner FROM systems as s LEFT JOIN users ON users.id = s.uid WHERE LastPort='$port';";
  #debug1($sel);
  $res = mysql_query($sel) or die("Unable to query the database");
  if (mysql_num_rows($res) > 0) {
	  while ($port = mysql_fetch_array($res)) {
    $out .= $port['name'].' ('.$port['owner'].')<br>';
	  };
	  return($out);
  } else {
	  return(FALSE);
  } 
};


function hubs_stuff()
{
   // ----------- main () -------------------
   global $readuser,$readpass,$hubs_queryday;
   db_connect($readuser,$readpass);
  
  echo("The following ports may have a hub, i.e.  with more than one end-device see in the last $hubs_querydays days:<br>");
   $sel = "SELECT port.name,port.switch,port.location,count(*) ".
	"FROM systems, port ".
	"WHERE systems.port = port.name AND systems.switch=port.switch ".
         " AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$hubs_querydays)".
	"GROUP BY port.name,port.switch;";

   $hubs_querydays = 99;
   $sel = "SELECT switch.name, port.name, LastPort as portid,count(*) FROM systems 
	LEFT JOIN port ON systems.LastPort = port.id
	LEFT JOIN switch ON port.switch = switch.id
	 WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$hubs_querydays) GROUP BY LastPort";

   debug1($sel);
   $res = mysql_query($sel) or die("Unable to query the database");

   if (mysql_num_rows($res) > 0) {
	echo '<br>';
	echo '<b>Switch IP ---- Port -- Location -- PC Name (User name)</b><br>';
	echo '<br>';
	echo '<table border=1 cellspacing=0 cellpadding=5>';
	while ($port = mysql_fetch_array($res)) {
	  if (($port['count(*)'] > 1) && ($port['portid'])) {
	    echo '<tr valign=top>';
	    echo '<td>'.$port['switch'];
	    echo '<td>'.$port['name'];
	    echo '<td>'.get_location($port['portid']);
	    echo '<td>'.get_hosts($port['portid']);
	  };
	};
	echo '</table>';
   } else {
	echo "<hr><i>Error - Nothing to display</i>";
   };
//vmps_footer();
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_SAMACCOUNTNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo "<hr /><br />";
      hubs_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   hubs_stuff();
   echo read_footer();
}

?>
