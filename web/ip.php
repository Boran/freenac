<?php
/**
 *
 * ip.php
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
  $action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==4) {
  $action_menu='';
  $action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  $action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters   fro gui_control.php
$title="IP Address management (beta: Test data from the Seclab)";
$sortlimit=200;
$sortby='address';
$order_dir='ASC';
$searchby='';
$searchstring='';

$_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
$action_fieldname="IP Idx";     $idx_fieldname="ip.id";

$q=<<<TXT
SELECT
  INET_NTOA(ip.address) AS 'IP Address',
  ip.status, 
  ip.source AS 'IP Source', 
  ip.comment as 'IP Comment',
  s.name AS 'Sys Name', s.r_ip AS 'Sys Last IP',
  ip.system AS 'Sys Idx', 
  subnets.ip_address AS 'Subnet',
  subnets.ip_netmask AS 'Mask',
  ip.subnet AS 'Subnet Idx', 
  $idx_fieldname AS '$action_fieldname' 
  FROM ip
  LEFT JOIN systems s on ip.system=s.id
  LEFT JOIN subnets on ip.subnet=subnets.id
TXT;
#  WHERE ip.system != 0 


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

  $logger->debug("Ip > GuiEditIp idx=$action_idx, action=". $_REQUEST['action'], 1);
  $report=new GuiEditIp($_REQUEST['action'], $action_idx, 2);  // last param=debug
  $report->handle_request();
  $logger->debug("after new GuiEditDevice", 3);

// Default & Actions handled by GuiList1 class: execute query
} else {
  require_once "GuiList1_control.php";
}


?>
