<?php
include_once('config.inc');
include_once('functions.inc');
include_once('defs.inc');
$stattypes = array("class","os","switch","vlan","dat");
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// still todo
// - Skill area / kostenstelle
// - Vendor (dell, ...)
// - active or not





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
			$unknown = $unknown + $row['count'];
		};
		$total = $total + $row['count'];
	};
	echo "<tr><td>Unknown<td>$unknown";
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
echo '<img src="statgraph.php?stattype='.$type.'&order='.$order.'&graphtype='.$graphtype."\"><br>\n";;

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
