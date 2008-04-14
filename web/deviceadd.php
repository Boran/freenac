<?php
/**
 *
 * deviceadd.php
 *
 * Long description for file:
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
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(3);

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


### --------- main() -------------
$_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);

// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
}

## Custom actions, not handled by GuiList1

if (isset($_REQUEST['action']) ) {
  // we expect Add or Edit, action_idx is ignored for Add.

  if (isset($_REQUEST['action_idx']) )
    $action_idx=$_REQUEST['action_idx'];
  else 
    $action_idx=0;
  $logger->debug("deviceadd > GuiEditDevice idx=$action_idx, action=". $_REQUEST['action'], 1);
  $report=new GuiEditDevice($_REQUEST['action'], $action_idx, 3);  // last param=debug
  $report->handle_request();
}


?>
