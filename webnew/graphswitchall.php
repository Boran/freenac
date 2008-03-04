<?php
/**
 *
 * graphswitchall.php
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
 *    /graphswitchall.php
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
$title="Switch-port usage graphically for all Switches";
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
   echo "List all ports used on all switches in the last ".$conf->web_lastdays." days, and which end-devices were seen on each port. For each end device, the node name and associated user is shown.<br>";
   echo "<br>";


$q = "SELECT DISTINCT(switch.id) as id, switch.name as name, switch.ip as ip, CONCAT(building.name,' ',location.name) as location
 FROM systems
 LEFT JOIN port ON port.id = systems.LastPort
 LEFT JOIN switch ON port.switch = switch.id
 LEFT JOIN location ON location.id = switch.location
  LEFT JOIN building ON building.id = location.building_id
WHERE  (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-".$conf->web_lastdays.") AND port.switch != ''
ORDER BY switch.name;";

  $res = $conn->query($q);
  if ($res === FALSE)
    throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
     if (($row['ip'] == '0.0.0.0') || ($row['ip'] == '') || (stristr($row['ip'],'0.0.0'))) {
        echo '<!-- Not graphed : ' .$row['name']. ' (' . $row['location']. " / ". $row['ip']." -->";
     } else {
       echo "<tr><th align=left>". 
       $row['name']. ' ('. 
       $row['location']. " / ".
       $row['ip']." )\n";
       echo "<br><img src=\"graphdot.php?sw=". $swi['id']. "\" border=0>\n";
     }
  }
  echo '</table>';

  echo $report->print_footer();

?>
