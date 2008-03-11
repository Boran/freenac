<?php
/**
 *
 * statgraph.php
 *
 * Long description for file:
 * Generate a grphic given a query.
 * This script generates a webpage with no text, and graphics must be generated before text.
 *
 * @package     FreeNAC
 * @author      Core team, Originally T.Dagonnier
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: find.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 * To test call from a browser with parameters like this:
 * http://SERVERNAME/nac/statgraph.php?stattype=os&order=DESC&graphtype=bar
 *
 */

## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../lib:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(0);     // set to 0 to see graphs

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
}
else if ($_SESSION['nac_rights']==1) {
  $action_menu='';
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu='';
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}



function cbFmtPercentage($aVal) {
    	return sprintf("%.0f",$aVal); // Convert to string
};


// ------------ main () ----------------

// Build a query object
  $report= new WebCommon(false);      // new webpage, no header
  $report->logger->setDebugLevel(0);  // set to 0 to see graphs
  $conn=$report->getConnection();     //  make sure we have a DB connection


// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_REQUEST=array_map('validate_webinput',$_REQUEST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);

   if ( isset($_GET["stattype"]) )
     $stattype = $_GET["stattype"];
   else
     $stattype = 'os';

   if ( isset($_GET["graphtype"]) )
     $graphtype = $_GET["graphtype"];
   else
     $graphtype = 'bar';

   if ( isset($_GET["order"]) )
     $order = $_GET["order"];
   else
     $order = 'DESC';

  $report->debug("stattype=$stattype, graphtype=$graphtype, order=$order", 2);

  include_once('graphdefs.inc');                      // generic queries
  $incs=array($conf->web_jpgraph.'/jpgraph.php', $conf->web_jpgraph.'/jpgraph_'.$graphtype.'.php');
  foreach ($incs as $f) {
    if (is_readable($f)) {
      $logger->debug("include $f", 3);
      include_once($f);       
    } else {
      throw new FileMissingException("ERROR: The file $f cannot be opened");
    } 
  } 



  // build and run the query
  switch ($stattype) {
   case 'wsus1':
      $q = $sel[$stattype]['graph'] ;
      break;
   default:
      $q = $sel[$stattype]['graph'] ." ORDER BY count(*) $order";
      break;
  };

  $report->debug($q, 3);
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
      $data[] = $row["count"];
      $data_names[] = $row["datax"]; //." (%.0f%%)";
  }
		
  // create the graph
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

	} 
        else if ($graphtype == 'pie') {
	  	   $graph = new Graph(500,500);
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
