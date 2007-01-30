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
$debug_flag1=true;

include_once('config.inc');

function get_hosts($sw,$port) {
  $sel = "SELECT name,description FROM systems WHERE switch='$sw' AND port='$port';";
  #debug1($sel);
  $res = mysql_query($sel) or die("Unable to query the database");
  if (mysql_num_rows($res) > 0) {
	  while ($port = mysql_fetch_array($res)) {
    $out .= $port['name'].' ('.$port['description'].')<br>';
	  };
	  return($out);
  } else {
	  return(FALSE);
  } 
};


// ----------- main () -------------------
db_connect();
echo '<head><title>Hub finder</title></head><body>';
vmps_header();

echo("The following ports may have a hub, i.e.  with more than one end-device see in the last $hubs_querydays days:<br>");
$sel = "SELECT port.name,port.switch,port.location,count(*) ".
	"FROM systems, port ".
	"WHERE systems.port = port.name AND systems.switch=port.switch ".
         " AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$hubs_querydays)".
	"GROUP BY port.name,port.switch;";

debug1($sel);
$res = mysql_query($sel) or die("Unable to query the database");

if (mysql_num_rows($res) > 0) {
	echo '<br>';
	echo '<b>Switch IP ---- Port -- Location -- PC Name (User name)</b><br>';
	echo '<br>';
	echo '<table border=1 cellspacing=0 cellpadding=5>';
	while ($port = mysql_fetch_array($res)) {
	  if ($port['count(*)'] > 1) {
	    echo '<tr valign=top>';
	    echo '<td>'.$port['switch'];
	    echo '<td>'.$port['name'];
	    echo '<td>'.$port['location'];
	    echo '<td>'.get_hosts($port['switch'],$port['name']);
	  };
	};
	echo '</table>';
} else {
	echo "<hr><i>Error - Nothing to display</i>";
};
vmps_footer();
?>
