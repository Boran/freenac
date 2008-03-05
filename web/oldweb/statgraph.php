<?php
chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('./webfuncs.inc');
//require_once('../bin/funcs.inc.php');


include_once('defs.inc');

$graphtype = $_GET["graphtype"];
$stattype =  $_GET["stattype"];
$order =  $_GET["order"];

include_once($conf->web_jpgraph.'/jpgraph.php');
include_once($conf->web_jpgraph.'/jpgraph_'.$graphtype.'.php');

function cbFmtPercentage($aVal) {
    	return sprintf("%.0f",$aVal); // Convert to string
};

db_connect($dbuser,$dbpass);

$select = $sel[$stattype]['graph']." ORDER BY count(*) $order;";
$result = mysql_query($select) or die ("Unable to query MySQL ($select) \n");

if (mysql_num_rows($result) > 0) {

	while ( $row = mysql_fetch_array($result)) {
	      $data[] = $row["count"];
	      $data_names[] = $row["datax"]; //." (%.0f%%)";
	}

		

	if ($graphtype == 'bar') {
		$graph = new Graph(800,400);

		// Create the graph. 
		

		$bar1 = new BarPlot($data);

		$graph->SetScale("textlin");

		$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
		$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);



		$graph->xaxis->SetTickLabels($data_names);
		$graph->xaxis->SetLabelAngle(45);

		$bar1->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);
		$bar1->value->SetFont(FF_VERDANA,FS_NORMAL,8);

		$bar1->value->SetFormatCallback("cbFmtPercentage");
		$bar1->value->Show();

		// Add the plot to the graph
		$graph->Add($bar1);



	} else if ($graphtype == 'pie') {
	  	   $graph = new Graph(500,500);
		// Create the Pie Graph.
		   $graph = new PieGraph(800,400);//,$filename,60);
		   $graph->SetShadow();

	//	   $graph->SetSize(0.4);

		// Set A title for the plot
	//	   $graph->title->Set($PIE_TITLE);
	//	   $graph->title->SetFont(FF_FONT1,FS_BOLD);

		// Create
		   $p1 = new PiePlot($data);
		   $p1->SetCenter(0.35,0.5);
	//	   $p1->SetLegends($data_names);
	//	   $p1->SetLabelType(PIE_VALUE_PER);
	 	   $p1->SetLabels($data_names);
		   $p1->SetTheme("sand");
		   $p1->value->SetFont(FF_VERDANA,FS_NORMAL,8);
	 
		   $graph->Add($p1);
	};

	$graph->Stroke();

} else {
		echo "Error - nothing to graph";
};
?> 
