<?php
/**
 *
 * graphswitch.php
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
 * Parametetrs:
 *    /graphswitch.php?sw=7&submit=View
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
$title="Switch-port usage graphically";
$sortlimit=200;
$sortby='';
$searchby='';
$searchstring='';
$action_fieldname="";     $idx_fieldname="";

$sw=validate_webinput($_REQUEST['sw']);


// Do the work: generate a webpage

  $report=(new GuiList1($title, false));                //true=dynamic with filtering
  $report->logger->setDebugLevel(3);
  $conn=$report->getConnection();     //  make sure we have a DB connection

   echo "<br>";
   echo "List all ports used on the specified switch in the last ".$conf->web_lastdays." days, and which end-devices were seen on each port. For each end device, the node name and associated user is shown.<br>";
   echo "<br>";
   echo "<form method=get action=\"{$_SERVER['PHP_SELF']}\">\n";
   echo "Select a switch from the list:<br>";
   echo $report->print_switch_sel($sw);
   //echo print_dot_sel();
   echo "<input type=\"submit\" name=\"submit\" value=\"View\">\n</form>\n";

   if ($sw) {
     $report->debug("Calling graphdot.php?sw=$sw");
     echo "<img src=\"graphdot.php?sw=$sw\" border=0>";
   };


  echo $report->print_footer();

?>
