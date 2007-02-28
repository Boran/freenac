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

include('config.inc');

echo '<head><title>NAC - all switches</title></head><body>';
vmps_header();
db_connect();

echo "<br>";
echo "List all ports used on all switches in the last $vmpsdot_querydays days, and which end-devices were seen on each port. For each end device, the node name and assocated user is shown.<br>";
echo "<br>";

$sel = "SELECT sw.id as id, sw.name as name, location.name as location, sw.ip as ip FROM switch sw LEFT JOIN location ON location.id = sw.location";
$res = mysql_query($sel) or die('Query failed: ' . mysql_error());

echo '<table cellspacing=0 cellpadding=5 border=1>';
while ($swi = mysql_fetch_array($res)) {
  if (($swi['ip'] == '0.0.0.0') || ($swi['ip'] == '') || (stristr($swi['ip'],'0.0.0'))) {
	echo '<!-- Not grpahed : '.$swi['name']. ' ('. $swi['location']. " / ". $swi['ip']." -->";
   } else {
    echo "<tr><th align=left>". 
       $swi['name']. ' ('. 
       $swi['location']. " / ".
       $swi['ip']." )\n";
    echo "<br><img src=\"vmpsdot.php?sw=". $swi['id']. "\" border=0>\n";
   };
};
echo '</table>';

vmps_footer();
?>
