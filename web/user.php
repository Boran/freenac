<?php
/**
 *
 * user.php
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
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}

// set parameters   fro gui_control.php
$title="User List";
$sortlimit=200;
$sortby='Surname';
$order_dir='ASC';
$searchby='';
$searchstring='';

$action_fieldname="Index";     $idx_fieldname="users.id";

$q=<<<TXT
SELECT 
  CONCAT(Surname, ', ', GivenName, ', ', username, ', ', Department) as 'Full Name',
  username AS Username, 
  comment AS Comment, 
  guirights.value AS 'Gui: rights',
  GuiVlanRights AS 'Gui: vlan restrictions',
  TelephoneNumber AS Telephone, Mobile, 
  CONCAT(building.name, ', ', location.name) as Location,
  LastSeenDirectory, 
  GivenName AS 'First Name', Surname AS 'Second Name', 
  Department, rfc822mailbox, 
  manual_direx_sync, 
  $idx_fieldname AS '$action_fieldname' 
  FROM users
  LEFT JOIN guirights ON users.nac_rights = guirights.code 
  LEFT JOIN location ON users.location = location.id 
  LEFT JOIN building ON location.building_id = building.id
TXT;

/*
 TBD: location joins
  location,
  HouseIdentifier AS Location1, 
  PhysicalDeliveryOfficeName AS Location2, 
*/


require_once "GuiList1_control.php";


?>
