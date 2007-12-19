<?php
#
#  allvmps.php
#
#  2006.05.25/Sean Boran: Production
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 FreeNAC
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
####################################

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('./webfuncs.inc');

function allvmps_stuff()
{
   global $dbuser,$dbpass,$vmpsdot_querydays;
   db_connect($dbuser,$dbpass);
   echo "<br>";
   echo "List all ports used on all switches in the last $vmpsdot_querydays days, and which end-devices were seen on each port. For each end device, the node name and associated user is shown.<br>";
   echo "<br>";

$sel = "SELECT DISTINCT(switch.id) as id, switch.name as name, switch.ip as ip, CONCAT(building.name,' ',location.name) as location
 FROM systems
 LEFT JOIN port ON port.id = systems.LastPort
 LEFT JOIN switch ON port.switch = switch.id
 LEFT JOIN location ON location.id = switch.location
  LEFT JOIN building ON building.id = location.building_id
WHERE  (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$vmpsdot_querydays) AND port.switch != '' 
ORDER BY switch.name;";

$res = mysql_query($sel) or die('Query failed: ' . mysql_error());

   echo '<table cellspacing=0 cellpadding=5 border=1>';
   while ($swi = mysql_fetch_array($res)) {
     if (($swi['ip'] == '0.0.0.0') || ($swi['ip'] == '') || (stristr($swi['ip'],'0.0.0'))) {
	echo '<!-- Not graphed : '.$swi['name']. ' ('. $swi['location']. " / ". $swi['ip']." -->";
     } else {
       echo "<tr><th align=left>". 
       $swi['name']. ' ('. 
       $swi['location']. " / ".
       $swi['ip']." )\n";
       echo "<br><img src=\"vmpsdot.php?sw=". $swi['id']. "\" border=0>\n";
     };
   };
   echo '</table>';
}


if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo "<hr /><br />";
      allvmps_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   allvmps_stuff();
   echo read_footer();
}

?>
