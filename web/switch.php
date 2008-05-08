<?php
/**
 *
 * switch.php
 *
 * Long description for file:
 *
 * @package     FreeNAC
 * @author      Sean Boran
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
  $logger->setDebugLevel(1);

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


### --------- main() -------------



// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
} 
else if ($_SESSION['nac_rights']==1) {
  $action_menu='';
}
else if ($_SESSION['nac_rights']==2) {
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
  $action_menu='';
}
else if ($_SESSION['nac_rights']==4) {
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
  $action_menu='';
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters   fro gui_control.php
$title="Switch configuration";
$sortlimit=200;
$sortby='switch.name';
$searchby='';
$searchstring='';

$action_fieldname="Switch Index";     $idx_fieldname="switch.id";

$q=<<<TXT
SELECT 
  switch.name AS 'Switch name', 
  comment AS 'Comment', 
  building.name as Building, 
  location.name as Location,
  scan AS 'Layer 2 passive scan?', 
TXT;
if ($conf->enable_layer3_switches==true)  
  $q.= " scan3 AS 'Layer 3 scan?', ";

$q.=<<<TXT
  last_monitored AS 'Last Monitored',
  up AS 'Up?',
  swgroup, 
  hw AS Hardware, sw AS Firmware,
  notify AS 'Emails to notify when unknowns detected', 
  v1.default_name AS 'Restricted vlan?',
  ip AS 'IP Address', 
  $idx_fieldname AS '$action_fieldname' 
  FROM switch 
  LEFT JOIN location ON switch.location = location.id 
  LEFT JOIN building ON location.building_id = building.id
  LEFT JOIN vlan v1  ON switch.vlan_id = v1.id
TXT;

# location

require_once "GuiList1_control.php";


?>
