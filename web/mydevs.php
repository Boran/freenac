<?php
/**
 *
 * mydevs.php
 *
 * Long description for file:
 * List End-devices attributed to my username (use logged on username)
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */

## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


### --------- main() -------------

if (!isset ($_SESSION['uid']) || !is_numeric($_SESSION['uid'])) {
  throw new InvalidLoginException('Unknown user ID: are you logged in correctly? ');
}
if (!isset ($_SESSION['username']) || !$_SESSION['username']) {
  throw new InvalidLoginException('Unknown username: are you logged in correctly? ');
}
  

// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
}
else if ($_SESSION['nac_rights']==1) {
  $action_menu=array('View');   // no options
  $action_confirm=array('');     // no confirmation popups
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu=array('View','Edit');   // 'buttons' in action column
  $action_confirm=array('', '');        // no confirmation popups
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu=array('View', 'Edit', 'Delete');   // 'buttons' in action column
  $action_confirm=array('', '', 'Really DELETE the record of this End-Device?');  // Confirm Deletes
}

$title="End-devices attributed to " .$_SESSION['login_data'] 
  ." (" .$_SESSION['username'] .")";
$sortlimit=50;
$sortby='sys.name';   
# WHERE sys.uid=$_SESSION['uid']
#$searchby='sys.uid';
#$searchstring=$_SESSION['uid'];
$searchby='usr.username';   // usr.username as Username,
$searchstring=$_SESSION['username'];
$order_dir='DESC';

$action_fieldname="Index";     $idx_fieldname="sys.id";
$q=<<<TXT
SELECT
  sys.mac AS 'MAC Addr.',
  sys.name as Systemname, 
  sys.r_ip AS 'LastSeen Layer3', sys.r_timestamp 'Time of Last Ip.',
  sys.lastseen AS 'LastSeen Layer2',
  status.value as Status,
  vlan.default_name as VlanName, lvlan.default_name as LastVlan,
  sys.inventory, sys.comment, 
  b.name as building, loc.name as office,
  p.name as port, pcloc.name as PortLocation, p.comment as PortComment, p.last_activity as PortLastActivity,
  swi.name as Switch, swloc.name as SwitchLocation,
  pc.outlet as PatchCableOutlet, pc.comment as PatchCableComment,
  sys.history,
  sos.value as OSName, sos1.value as OS1, sos2.value as OS2, sos3.value as OS3,
  sys.os4 as OS4,
  sys.class, sclass.value as ClassName, sys.class2, sclass2.value as ClassName2,
  usr.username as Username,
  $idx_fieldname AS '$action_fieldname'
  FROM systems as sys LEFT JOIN vlan as vlan ON vlan.id=sys.vlan
      LEFT JOIN vlan as lvlan ON lvlan.id=sys.lastvlan
      LEFT JOIN vstatus as status ON status.id=status
      LEFT JOIN users as usr ON usr.id=sys.uid
      LEFT JOIN users as cusr ON cusr.id=sys.changeuser
      LEFT JOIN location as loc ON loc.id=sys.office
      LEFT JOIN building as b ON b.id=loc.building_id
      LEFT JOIN port as p ON p.id=sys.lastport
      LEFT JOIN patchcable as pc ON pc.port=p.id
      LEFT JOIN location as pcloc ON pcloc.id=pc.office
      LEFT JOIN switch as swi ON swi.id=p.switch
      LEFT JOIN location as swloc ON swloc.id=swi.location
      LEFT JOIN location as usrloc ON usrloc.id=usr.location
      LEFT JOIN sys_os as sos ON sos.id=sys.os
      LEFT JOIN sys_os1 as sos1 ON sos1.id=sys.os1
      LEFT JOIN sys_os2 as sos2 ON sos2.id=sys.os2
      LEFT JOIN sys_os3 as sos3 ON sos3.id=sys.os3
      LEFT JOIN sys_class as sclass ON sclass.id=sys.class
      LEFT JOIN sys_class2 as sclass2 ON sclass2.id=sys.class2
TXT;

/*
  usr.surname, usr.givenname, usr.department, usr.rfc822mailbox as EMail,
  usrloc.name as UserLocation, usr.telephonenumber as UserTelephone, usr.mobile,
  usr.lastseendirectory as UserLastSeenDirectory,
  sys.changedate as 'Change Date',
  cusr.username as 'Change User',
*/

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

  $logger->debug("listall > GuiEditDevice idx=$action_idx, action=". $_REQUEST['action'], 1);
  $report=new GuiEditDevice($_REQUEST['action'], $action_idx, 2);  // last param=debug
  $report->handle_request();
  $logger->debug("after new GuiEditDevice", 3);

// Default & Actions handled by GuiList1 class: execute query
} else {
  require_once "GuiList1_control.php";
}

?>
