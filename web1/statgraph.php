<?php
include_once('config.inc');
include_once('functions.inc');

$select = $_GET["select"];
$graphtype = $_GET["graphtype"];

include_once($jpgraph_path.'/jpgraph.php');
include_once($jpgraph_path.'/jpgraph_'.$graphtype.'.php');

function cbFmtPercentage($aVal) {
    	return sprintf("%.0f",$aVal); // Convert to string
};

db_connect();
$result = mysql_query($select);

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
?> 
