<?php
/**
 *
 * unknowns.php
 *
 * Long description for file:
 * List End-devices, sort, call the editing screen
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
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


// set parameters   fro gui_control.php
$title="List of Unknown End-devices";
$sortlimit=50;
$sortby='sys.LastSeen';
$searchby='Status';
$searchstring='0';

// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
} 
else if ($_SESSION['nac_rights']==1) {
  $action_menu=array('Print');   // no options
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}

## A smaller and quicker query:
$action_fieldname="Index";     $idx_fieldname="sys.id";
$q=<<<TXT
SELECT 
  sys.name as Systemname, 
  sys.mac as 'MAC Address', 
  status.value as Status, 
  sys.lastseen, 
  $idx_fieldname AS '$action_fieldname', 
  vlan.default_name as Vlan, lvlan.default_name as LastVlan, 
  sys.inventory, sys.description, sys.comment, 
  b.name as building, loc.name as office, 
  p.name as port, pcloc.name as PortLocation, p.comment as PortComment,
  swi.name as Switch, swloc.name as SwitchLocation,
  usr.username as Username, 
  usr.surname AS Firstname, usr.givenname AS FamilyName, usr.department, 
  usr.telephonenumber as UserTelephone,
  sys.os4 as OS4,
  sys.r_ip AS 'Last IP Address', sys.r_timestamp AS 'Last time IP seen'
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


require_once "GuiList1_control.php";


?>
