<?php
include_once('config.inc');
include_once('functions.inc');
$stattypes = array("class","os","switch","vlan","dat");
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// still todo
// - Skill area / kostenstelle
// - Vendor (dell, ...)
// - active or not

$sel['class']['table'] = "SELECT cl.value as class, c2.value as subclass, count(*) as count FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value, c2.value";
$sel['class']['graph'] = "SELECT cl.value as datax, count(*) as count FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value";


$sel['os']['table'] = "SELECT o.value, os1, os3, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value, os1, os3";
$sel['os']['graph'] = "SELECT o.value as datax, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value";


$sel['switch']['table'] = "SELECT sw.name,count(*) as count FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name";
$sel['switch']['graph'] = "SELECT sw.name as datax, count(*) as count FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name;";


$sel['vlan']['table'] = "SELECT s.vlan as ID, v.value as name, count(*) as count FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";
$sel['vlan']['graph'] = "SELECT v.value as datax, count(*) as count FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";


$sel['dat']['table'] = "SELECT DATversion, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";
$sel['dat']['graph'] = "SELECT DATversion as datax, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";



function print_dat_stats($query) {
	$readme_url='http://vil.nai.com/vil/DATReadme.aspx';

	$result = mysql_query($query);


	echo "<table cellspacing=0 cellpadding=5 border=1>\n";
	echo "<tr><th><a href=\"$readme_url\">DAT Version</a><th>count";
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$short_version = strip_datversion($row['DATversion']);
		if ($short_version > 0) {
			echo "<tr>";
			echo "<td>$short_version</a>";
			echo "<td>".$row['count'];
			echo "\n";
		} else {
			$unkown = $unknown + $row['count'];
		};
		$total = $total + $row['count'];
	};
	echo "<tr><td>Unknown<td>$unkown";
	echo "</table>\n<b>Total = $total\n";
};

function print_stats($query) {
	$result = mysql_query($query);
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
};


// TODO need to put proper parsing
$type = $_GET["type"];
$graphtype = $_GET["graphtype"];

if (!$type) { $type = 'os'; };
if (!$graphtype) { $graphtype = 'bar'; };
if (!$order) { $order = 'DESC'; };


// ugly temporary "menu bar" TODO nice
echo "Group by : ";
foreach ($stattypes as $sttp) {
	echo "<a href=\"$PHP_SELF?graphtype=$graphtype&type=$sttp\">$sttp</a> - ";
};
echo "<br>Graph : ";
foreach ($graphtypes as $grtp) {
	echo "<a href=\"$PHP_SELF?type=$type&graphtype=$grtp\">$grtp</a> - ";
};
echo '<hr>';

// show statistics
db_connect();

$query = $sel[$type]['table']." ORDER BY count(*) $order;";
//echo $query.'<hr>';

$queryg = $sel[$type]['graph']." ORDER BY count(*) $order;";
echo '<img src="statgraph.php?select='.$queryg.'&graphtype='.$graphtype."\"><br>\n";;

switch ($type) {
case 'dat':
   echo print_dat_stats($query);
   break;
default:
   echo print_stats($query);
   break;
};



vmps_footer();
?>
