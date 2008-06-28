<?php
/**
 *
 * ethernet.php
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
  $action_menu=array('View');   // no options
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu=array('View','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==4) {
  $action_menu=array('View','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu=array('View', 'Edit', 'Delete');   // 'buttons' in action column
} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters   fro gui_control.php
$title="Ethernet mac prefixes per vendor ";
$sortlimit=200;
$sortby='vendor';
$order_dir='ASC';
$searchby='';
$searchstring='';

$_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
$action_fieldname="Mac";     $idx_fieldname="mac";

$q=<<<TXT
SELECT
  vendor,
  $idx_fieldname AS '$action_fieldname' 
  FROM ethernet
TXT;


// Actions handled by GuiEditDevice class
if (isset($_REQUEST['action']) && (
       ($_REQUEST['action'] == 'Update')
    || ($_REQUEST['action'] == 'Delete')
    || ($_REQUEST['action'] == 'Edit')
    || ($_REQUEST['action'] == 'Add')
  ) ) {

  if (isset($_REQUEST['action_idx']) )
    $action_idx=$_REQUEST['action_idx'];
  else
    $action_idx=0;

  $logger->debug("Ip > GuiEditEthernet idx=$action_idx, action=". $_REQUEST['action'], 1);
  $report=new GuiEditEthernet($_REQUEST['action'], $action_idx, 3);  // last param=debug
  $report->handle_request();
  $logger->debug("after new GuiEditEthernet", 3);

// Default & Actions handled by GuiList1 class: execute query
} else {
  require_once "GuiList1_control.php";
}


?>
