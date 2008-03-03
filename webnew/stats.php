<?php
/**
 *
 * stats.php
 *
 * Long description for file:
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
$stattypes = array("class","class2","os","os1","os2","os3","switch","vlan","vlan_group","dat");
$graphtypes = array("pie","bar");
$orders = array("DESC","ASC");
// TBD: Organisation unit, Vendor (dell, ...), active or not

// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
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
}

// set parameters   fro gui_control.php
$title="End-Device statistics";
$sortlimit=200;
$sortby='';
$searchby='';
$searchstring='';
$action_fieldname="";     $idx_fieldname="";


$q=<<<TXT
SELECT switch.name AS Switch, port.name, LastPort as portid, count(*) 
    FROM systems
    LEFT JOIN port ON systems.LastPort = port.id
    LEFT JOIN switch ON port.switch = switch.id
    WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-{$conf->web_lastdays}) GROUP BY LastPort
TXT;

// TODO need to put proper parsing
// TBD: maybe use the SESSION variable?
   $type = $_GET["type"];
   $graphtype = $_GET["graphtype"];

   if (!isset($type))      { $type = 'os'; };
   if (!isset($graphtype)) { $graphtype = 'bar'; };
   if (!isset($order))     { $order = 'DESC'; };
// TODO need to put proper parsing
   $type = $_GET["type"];
   $graphtype = $_GET["graphtype"];

   if (!$type) { $type = 'os'; };
   if (!$graphtype) { $graphtype = 'bar'; };
   if (!$order) { $order = 'DESC'; };


// Do the work: generate a webpage

  $report=(new GuiList1($title, false));                //true=dynamic with filtering

   // ugly temporary "menu bar" TODO nice
   echo "Group by : ";
   foreach ($stattypes as $sttp) {
     echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$graphtype&type=$sttp\">$sttp</a> - ";
   };
   echo "<br>Graph : ";
   foreach ($graphtypes as $grtp) {
     echo "<a href=\"" .$_SERVER['PHP_SELF'] ."?graphtype=$graphtype&type=$grtp\">$grtp</a> - ";
   };
   echo '<hr>';


  // we don't use the standrad query() to build the grid, and our needs are a bit more complex.
  $conn=$report->getConnection();     //  make sure we have a DB connection

  $q= $sel[$type]['graph'] ." ORDER BY count(*) $order;";  // see graphdefs.inc
  $logger->debug($q, 3);
  // TBD: nasty: send via the session variable?
  echo '<img src="statgraph.php?stattype='.$type.'&order='.$order.'&graphtype='.$graphtype."\"><br>\n";

  switch ($type) {
   case 'dat':
      echo $report->print_dat_stats($q);
      break;
   default:
      echo $report->print_stats($q);
     break;
  };

  echo $report->print_footer();

?>
