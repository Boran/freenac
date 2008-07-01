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
  // TBD: testingDA only:
  $action_menu=array('View', 'Edit', 'Delete');   // 'buttons' in action column
  //$action_menu=array('View','Edit');   // 'buttons' in action column
  $action_confirm=array('', '', 'Really DELETE the record for this IP?');  // Confirm Deletes

}
else if ($_SESSION['nac_rights']==4) {
  $action_menu='';
  $action_menu=array('View','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('View', 'Edit', 'Delete', 'Add');   // 'buttons' in action column
  $action_menu=array('View', 'Edit', 'Delete');   // 'buttons' in action column
  $action_confirm=array('', '', 'Really DELETE the record for this IP?');  // Confirm Deletes

} else {
  throw new InsufficientRightsException("Unknown nac_rights: ".$_SESSION['nac_rights']);
}

// set parameters   fro gui_control.php
$title="IP Address management - for DNS";
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
  dstatus.value as 'Status',
  s.name AS 'Sys DNS Name', 
  s.dns_alias AS 'DNS Aliases', 
  s.r_ip AS 'Sys Last IP', s.r_timestamp as 'Sys Timstamp',
  subnets.ip_address AS 'Subnet',
  subnets.ip_netmask AS 'Mask',
  ip.comment as 'IP Comment',
  ip.source AS 'IP Source', 
  ip.system AS 'Sys Idx', 
  ip.lastchange,
  ip.lastupdate,
  ip.dns_update AS 'Dns update', 
  ip.status AS 'Status Idx', 
  $idx_fieldname AS '$action_fieldname' 
  FROM ip
  LEFT JOIN systems s on ip.system=s.id
  LEFT JOIN subnets on ip.subnet=subnets.id
  LEFT JOIN dstatus on ip.status=dstatus.id
TXT;
/*
  CASE ip.status WHEN 0 THEN 'free' WHEN 1 THEN 'active' WHEN 2 then 'reserved' END As Status,
  IF(ip.status='0','free', IF(ip.status=1,'fixed','')) AS Status,
  ip.subnet AS 'Subnet Idx', 
  WHERE ip.system != 0 
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

  $logger->debug("Ip > GuiEditIp idx=$action_idx, action=". $_REQUEST['action'], 1);
  $report=new GuiEditIp($_REQUEST['action'], $action_idx, 3);  // last param=debug
  $report->handle_request();
  $logger->debug("after new GuiEditIp", 3);


} else if (isset($_REQUEST['action']) && (
       ($_REQUEST['action'] == 'UpdateDNS')) ) {

  $logger->debug("Ip > GuiUpdateDns action=". $_REQUEST['action'], 1);
  $report=new GuiUpdateDns("Update changed DNS records", 3);  // last param=debug
  $report->UpdateDns();
  $logger->debug("after new GuiUpdateDns", 3);

} else if (isset($_REQUEST['action']) && (
       ($_REQUEST['action'] == 'UpdateDNS-All')) ) {
  $report=new GuiUpdateDns("Update ALL entries in the DNS zone", 3);  // last param=debug
  $report->UpdateDnsAll();

} else if (isset($_REQUEST['action']) && (
       ($_REQUEST['action'] == 'ViewDNS')) ) {
  $report=new GuiUpdateDns("View all records in the domain domain", 3);  // last param=debug
  $report->ViewDNS();


} else {
  // Default Actions 

    $report=new GuiList1($title, true, 1);                //true=dynamic with filtering, debug level
    $add="<form name='Add' action='ip.php' method='post'> <input class='bluebox' type='submit' name='action' value='Add' />";
    $dns="<form name='UpdateDNS' action='ip.php' method='post'> <input class='bluebox' type='submit' name='action' value='UpdateDNS' />";
    $dns2="<form name='UpdateDNS-All' action='ip.php' method='post'> <input class='bluebox' type='submit' name='action' value='UpdateDNS-All' />";
    $dns3="<form name='ViewDNS' action='ip.php' method='post'> <input class='bluebox' type='submit' name='action' value='ViewDNS' />";
    echo "<div align='center'>$add $dns $dns2 $dns3<hr></div>";
    echo $report->query($q, $sortlimit, $sortby,
       $action_menu, $action_fieldname, $idx_fieldname,
       $searchstring, $searchby, $action_confirm, $order_dir);   // run query, generate report

    echo $report->print_footer();

}


?>
