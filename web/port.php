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
  $action_menu='';   // no options
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu=array('Restart');   // Allow port restart
  $action_confirm=array('');       // no confirmation popups

}
else if ($_SESSION['nac_rights']==99) {
  $action_menu=array('Restart');   // Allow port restart
  $action_confirm=array('');       // no confirmation popups
}

// set parameters   fro gui_control.php
$title="Switch-Port configuration";
$sortlimit=100;
#$sortby='SwitchName, port.name';
$sortby='SwitchName';
$searchby='SwitchName';
$searchstring='';

$action_fieldname="Port Index";     $idx_fieldname="port.id";

$q=<<<TXT
SELECT DISTINCT 
  switch.name as SwitchName, 
  port.name as Port  ,
  v1.default_name as LastVlan,  
  last_activity AS 'Last used',
  port.comment AS 'Comment', 
  ap1.method as VlanAuth,
  v3.default_name as 'Static Vlan', 
  v2.default_name AS 'Default Vlan', 
  port.last_monitored AS 'Last Monitored', 
  port.up AS 'Port is up', 
  switch.ip as 'Switch IP Addr.', 
  CONCAT(switch.name, ' ', port.name) as switchport,
  $idx_fieldname AS '$action_fieldname' 
  FROM port 
  INNER JOIN switch     ON port.switch = switch.id 
  LEFT  JOIN patchcable ON patchcable.port = port.id 
  LEFT  JOIN location   ON patchcable.office = location.id   
  LEFT  JOIN auth_profile ap1 ON ap1.id = port.last_auth_profile
  LEFT  JOIN vlan v1    ON port.last_vlan = v1.id
  LEFT  JOIN vlan v2    ON port.default_vlan = v2.id
  LEFT  JOIN vlan v3    ON port.staticvlan = v3.id
TXT;

# restart_now, auth_profile, staticvlan, port.shutdown,

require_once "GuiList1_control.php";

if (isset($_REQUEST['action']) && $_REQUEST['action']=='Restart') {
  $logger->debug("Port action: ". $_REQUEST['action'] ." idx=" .$_REQUEST['action_idx'], 1);
  if ($_SESSION['nac_rights']<2)   // must have edit rights
    throw new InsufficientRightsException($_SESSION['nac_rights']);

  $logger->setDebugLevel(1);
  // have we a valid port index to restart?
  if (isset($_REQUEST['action_idx']) && is_numeric($_REQUEST['action_idx'])
    && $_REQUEST['action_idx']>1 ) {

    // TBD: could really look up the port/switch name for nicer logging?
    $logger->debug("Port restart: " .$_REQUEST['action_idx'], 1);
    $report2=new WebCommon(false); // no title
    $conn=$report2->getConnection();     //  make sure we have a DB connection
    $q2="UPDATE port set restart_now=1 WHERE id=" .$_REQUEST['action_idx'] ." LIMIT 1";
      $logger->debug($q2, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);
      $report2->loggui("Port index " .$_REQUEST['action_idx'] ." restart requested");

      #echo "<p class='UpdateMsgOK'>Port will be restarted within one minute</p>";
      echo jalert('The Switch Port will be restarted within one minute');
  }

}


?>
