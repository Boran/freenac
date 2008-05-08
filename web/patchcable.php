<?php
/**
 *
 * patchcable.php
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
  $logger->setDebugLevel(3);

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
  $action_menu='';
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==4) {
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
$title="Patch cable wiring";
$sortlimit=200;
$sortby='outlet';
$searchby='';
$searchstring='';

$action_fieldname="Patch Index";     $idx_fieldname="p.id";

$q=<<<TXT
SELECT 
  rack as Rack, 
  rack_location As 'R-location', 
  other as 'R-outlet',
  outlet as 'Outlet/Stecker', 
  location.name as 'Office Location', 
  cabletype.name as 'Cable type',
  switch.name AS Switch,
  port.name AS PortName,
  port.comment AS 'Port Comment',
  p.comment AS 'Patch Comment', 
  expiry, lastchange, 
  CONCAT(users.GivenName, ' ', users.Surname) as ChangedBy,
  port as 'Port index',
  office as 'Location index',
  $idx_fieldname AS '$action_fieldname' 
  FROM patchcable p
  LEFT JOIN cabletype ON p.type = cabletype.id    
  LEFT JOIN users    ON p.modifiedby = users.id
  LEFT JOIN location ON p.office = location.id
  LEFT JOIN port     ON p.port=port.id
  LEFT JOIN switch   ON port.switch=switch.id
TXT;
/*
  CONCAT(switch.name, ' ', port.name) as SwitchPort,
*/

require_once "GuiList1_control.php";


?>
