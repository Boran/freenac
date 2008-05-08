<?php
/**
 *
 * stats.php
 *
 * Long description for file:
 * Generate a graphic (by linking to statgraph.php) and printing a table
 * of corresponding values. Provide a menu at the top.
 * Queries are listed in graphdefs.inc and include by both this file and statgraph.php.
 *
 * @package     FreeNAC
 * @author      Core team, Originally T.Dagonnier
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: find.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */

## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../lib:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(3);

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


### --------- main() -------------
include_once('graphdefs.inc');
#$stattypes = array("switch","vlan","vlan_group","dat", "class","class2","os","os1","os2","os3");
$stattypes = array("switch","vlan","dat", "wsus1", "class","class2","os","os1","os2","os3");
#TBD: create a 2D array with actions and title, to make titles prettier
#$stattypes = array("title" -> array ("Switch port usage")
#                   "action"-> array ("switch")
#);
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// TBD: Organisation unit, Vendor (dell, ...), active or not

// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_REQUEST=array_map('validate_webinput',$_REQUEST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);


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
} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters   fro gui_control.php
$title="End-Device statistics";
$sortlimit=200;
$sortby='';
$searchby='';
$searchstring='';
$action_fieldname="";     $idx_fieldname="";


// TODO need to put proper parsing
// TBD: maybe use the SESSION variable?
   if ( isset($_GET["type"]) )
     $type = $_GET["type"];
   else 
     $type = 'switch';

   if ( isset($_GET["graphtype"]) )
     $graphtype = $_GET["graphtype"];
   else 
     $graphtype = 'bar';

   if ( isset($_GET["order"]) )
     $order = $_GET["order"];
   else 
     $order = 'DESC';


// Do the work: generate a webpage

  $report=(new GuiList1("$title - $type", false));                //true=dynamic with filtering
  $report->logger->setDebugLevel(3);
  $conn=$report->getConnection();     //  make sure we have a DB connection

   // ugly temporary "menu bar" TODO nice
   echo "Report type: ";
   foreach ($stattypes as $sttp) {
     echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$graphtype&type=$sttp\">$sttp</a> - ";
     #echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$graphtype&type={$sttp->action}\">{$sttp->title}</a> - ";
   };
   echo "<br>Graph type: ";
   foreach ($graphtypes as $grtp) {
     #echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$graphtype&type=$grtp\">$grtp</a> - ";
     echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$grtp\">$grtp</a> - ";
   };
   echo '<hr>';

  echo "\n\n";
  echo '<img src="statgraph.php?stattype='.$type.'&order='.$order.'&graphtype='.$graphtype."\"><br>\n";
  echo '<br>';

  #$q= $sel[$type]['graph'] ." ORDER BY count(*) $order;";  // see graphdefs.inc
  switch ($type) {
   case 'wsus1':
      $q= $sel[$type]['table'] ;  // see graphdefs.inc
      echo $report->print_stats($q);
      break;
   case 'dat':
      $q= $sel[$type]['table'] ." ORDER BY count(*) $order;";  // see graphdefs.inc
      $report->debug($q, 3);
      #echo $report->print_dat_stats($q);
      echo $report->print_stats($q);
      break;
   default:
      $q= $sel[$type]['table'] ." ORDER BY count(*) $order;";  // see graphdefs.inc
      echo $report->print_stats($q);
      break;
  };

  echo $report->print_footer();

?>
