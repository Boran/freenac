<?php
/**
 *
 * hubs.php
 *
 * Long description for file:
 *
 * @package     FreeNAC
 * @author      Core team
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
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}

// set parameters   fro gui_control.php
$title="The following ports might have a hub: i.e. more than one end-device in the last $conf->web_lastdays days";
$sortlimit=200;
$sortby='';
$searchby='';
$searchstring='';
$action_fieldname="";     $idx_fieldname="";
$q=<<<TXT
SELECT switch.name AS Switch, port.name, LastPort as portid, count(*) 
    FROM systems
    LEFT JOIN port ON systems.LastPort = port.id
    LEFT JOIN switch ON port.switch = switch.id
    WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-{$conf->web_lastdays}) GROUP BY LastPort
TXT;




  $report=(new GuiList1($title, false));                //true=dynamic with filtering
  // we don't use the standrad query() to build the grid, and our needs are a bit more complex.
    #echo $report->query($q, $sortlimit, $sortby,
    #   $action_menu, $action_fieldname, $idx_fieldname,
    #   $searchstring, $searchby);   // run query, generate report
   $conn=$report->getConnection();     //  make sure we have a DB connection
   $report->debug($q, 3);
   $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

   // title:
   // TBD: use a css class, make titles in bold, maybe sortable?
     echo '<table border=1 cellspacing=0 cellpadding=5>';
     echo '<br><tr><td>Switch<td>Port<td>Location<td>PC Name (User name)';
     while (($row = $res->fetch_assoc()) !== NULL) {
          if (($row['count(*)'] > 1) && ($row['portid'])) {
            echo '<tr valign=top>';
            echo '<td>' .$row['Switch'];
            echo '<td>' .$row['name'];
            //echo '<td>' .$row['portid'];
            echo '<td>' .$report->get_location($row['portid']);
            echo '<td>' .$report->get_hosts($row['portid']);
          };
     }
     echo '</table>';


  echo $report->print_footer();


?>
