<?php
/**
 *
 * port.php
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
  $action_menu=array('');   // no options
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu=array('');   // no options
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu=array('');   // no options
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}

// set parameters   fro gui_control.php
$title="Switch-Port configuration";
$sortlimit=200;
$sortby='SwitchName, port.name';
$searchby='';
$searchstring='';

$action_fieldname="Port Index";     $idx_fieldname="port.id";

$q=<<<TXT
SELECT DISTINCT 
  CONCAT(switch.name, ' ', port.name) as switchport,
  default_vlan, 
  v1.default_name as LastVlanName,  last_activity AS 'Last used',
  port.comment, 
  ap1.method as VlanAuth,
  staticvlan, 
  port.last_monitored, port.up, 
  switch.name as SwitchName, 
  switch.ip as 'Switch IP Addr.', 
  port.name,  
  $idx_fieldname AS '$action_fieldname' 
  FROM port 
  INNER JOIN switch     ON port.switch = switch.id 
  LEFT  JOIN patchcable ON patchcable.port = port.id 
  LEFT  JOIN location   ON patchcable.office = location.id   
  LEFT  JOIN auth_profile ap1 ON ap1.id = port.last_auth_profile
  LEFT  JOIN vlan v1    ON port.last_vlan = v1.id
TXT;

# restart_now, auth_profile, staticvlan, port.shutdown,

require_once "GuiList1_control.php";


?>
