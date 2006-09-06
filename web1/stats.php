<?php
include_once('config.inc');
include_once('functions.inc');
$stattypes = array("class","os","switch","vlan");
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// still todo
// - Skill area / kostenstelle
// - Vendor (dell, ...)
// - active or not

$sel['class']['table'] = "SELECT cl.value, c2.value, count(*) FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value, c2.value";
$sel['class']['graph'] = "SELECT cl.value, count(*) FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value";


$sel['os']['table'] = "SELECT os, o.value, os1, os3, count(*) FROM systems s, sys_os o WHERE s.os=o.id GROUP BY os, o.value, os1, os3";
$sel['os']['graph'] = "SELECT o.value, count(*) FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value";


$sel['switch']['table'] = "SELECT sw.name,count(*) FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name";
$sel['switch']['graph'] = "SELECT sw.name,count(*) FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name;";


$sel['vlan']['table'] = "SELECT s.vlan, v.value as vlanname, count(*) FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";
$sel['vlan']['graph'] = "SELECT v.value as vlanname, count(*) FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";



// TODO need to put proper parsing
$type = $_GET["type"];

if (!$type) { $type = 'os'; };
if (!$graphtype) { $graphtype = 'bar'; };
if (!$order) { $order = 'DESC'; };


// ugly temporary "menu bar" TODO nice
echo "Group by : ";
foreach ($stattypes as $sttp) {
	echo "<a href=\"$PHP_SELF?type=$sttp\">$sttp</a> - ";
};
echo '<hr>';

// show statistics
db_connect();

$query = $sel[$type]['table']." ORDER BY count(*) $order;";
$result = mysql_query($query);
//echo $query.'<hr>';

// TODO graph

echo "<table cellspacing=0 cellpadding=5 border=1>\n";
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if (!$th) {
		echo "<tr>";
		foreach ($row as $key => $value) {
			echo "<th>$key";
		};
		echo "\n";
		$th = TRUE;
	};
	echo "<tr>";
	foreach ($row as $value) {
		echo "<td>$value &nbsp;";
	};
	echo "\n";
	$total = $total + $value;
};

echo "</table>\n<b>Total = $total\n";

vmps_footer();
?>
