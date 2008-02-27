<?php
/**
 *
 * config.php
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
$title="Global config";
$sortlimit=200;
$sortby='name';
$searchby='';
$searchstring='';

$action_fieldname="Setting name";     $idx_fieldname="name";

$q=<<<TXT
SELECT
  $idx_fieldname AS '$action_fieldname', 
  config.type, config.value, 
  config.comment, config.LastChange, 
  CONCAT(users.GivenName, ' ', users.Surname) as user
  FROM config
  LEFT JOIN users on config.who=users.id
TXT;


require_once "GuiList1_control.php";


?>
