<?php
chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('./config.inc');
// include functions
require_once('./funcs.inc');

include_once('defs.inc');
$stattypes = array("class","class2","os","os1","os2","os3","switch","vlan","vlan_group","dat");
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// still todo
// - Skill area / kostenstelle
// - Vendor (dell, ...)
// - active or not





function print_dat_stats($query) {
	$readme_url='http://vil.nai.com/vil/DATReadme.aspx';

	$result = mysql_query($query) or die ("Unable to query MySQL ($query)\n");

	if (mysql_num_rows($result) > 0) {
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
	} else {
		echo "Nothing to display";
	};
};

function print_stats($query) {
      $result = mysql_query($query) or die ("Unable to query MySQL ($query)\n");
	
        echo "<table cellspacing=0 cellpadding=5 border=1>\n";
	if (mysql_num_rows($result) > 0) {
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
	} else {
		echo "Nothing to display";
	};
};

function stats_stuff()
{
   global $dbuser,$dbpass,$stattypes,$graphtypes,$orders,$sel;
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
   db_connect($dbuser,$dbpass);

   $query = $sel[$type]['table']." ORDER BY count(*) $order;";
   //echo $query.'<hr>';

   $query = $sel[$type]['graph']." ORDER BY count(*) $order;";
   echo '<img src="statgraph.php?stattype='.$type.'&order='.$order.'&graphtype='.$graphtype."\"><br>\n";;

   switch ($type) {
   case 'dat':
      echo print_dat_stats($query);
      break;
   default:
      echo print_stats($query);
     break;
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
      stats_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   stats_stuff();
   echo read_footer();
}
?>
