<?php
/**
 *
 * vlan.php
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
$title="Vlan configuration";
$sortlimit=200;
$sortby='default_name';
$order_dir='ASC';
$searchby='';
$searchstring='';

$action_fieldname="Index Number";     $idx_fieldname="id";

$q=<<<TXT
SELECT
  default_name AS 'Vlan Name',   
  default_id AS 'Vlan number', 
  vlan_description as 'GUI Description', 
  vlan_group AS 'Group',
  $idx_fieldname AS '$action_fieldname' 
  FROM vlan
TXT;


require_once "GuiList1_control.php";


?>
