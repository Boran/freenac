<?php
/**
 *
 * report_listports22.php
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
else if ($_SESSION['nac_rights']>1) {
  $action_menu='View';   // no options
} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters  for gui_control.php
$title="Discovered SSH servers (port 22: last 20 days)";
$sortlimit=200;
#$sortby='SwitchName, port.name';
$sortby='nac_hostscanned.timestamp';
$order_dir='ASC';
$searchby='hostname';
$searchstring='';

$action_fieldname="ip";     $idx_fieldname="ip";

$q=<<<TXT
select hostname,banner,nac_openports.timestamp,mac,comment,nac_hostscanned.os,
  $idx_fieldname AS '$action_fieldname'

  from nac_hostscanned 
  JOIN systems on systems.id=nac_hostscanned.sid 
  JOIN nac_openports on nac_openports.sid=nac_hostscanned.sid

  WHERE TO_DAYS(nac_hostscanned.timestamp) > to_days(NOW())-20 
    AND (service=9211 OR service=13961 OR service=15 OR service=4765)

TXT;


require_once "GuiList1_control.php";



?>
