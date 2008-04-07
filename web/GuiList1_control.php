<?php
/**
 *
 * GuiList1_control.php
 *
 * Main, generic logic for instantiating the GuiList1 class
 * and controlling form submission.
 * 
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 */

## ----- Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../lib/:../");
  require_once('webfuncs.inc');
  include 'session.inc.php';

  $logger=Logger::getInstance();
  $logger->setDebugLevel(2); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
# --- end of standard header ------

// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_REQUEST=array_map('validate_webinput',$_REQUEST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);


## The following MUST be set in the file that includes us
## session must also be set there
if ( !isset($title) || !isset($action_menu) || !isset($q) ) {
    // TBD: use an exception
    $logger->debug('Report has invalid title, query or action_menu parameters', 1);
    $report=new GuiList1("Report"); 
    echo "<hr><font class=text16red><p>Report has no valid parameters.</p></font>";
    echo $report->print_footer();
}


global $logger, $q;

// ensure defaults are set by the calling script
$action_fieldname = isset($action_fieldname) ? $action_fieldname : '';
$action_confirm   = isset($action_confirm) ? $action_confirm : array(''); 
$idx_fieldname    = isset($idx_fieldname) ? $idx_fieldname : $action_fieldname;
$order_dir        = isset($order_dir) ? $order_dir : 'DESC';
#$searchstring     = isset($searchstring) ? $searchstring : '';


###### Standard CHANGE (limit|sort) button ############
if ( isset($_REQUEST['change']) ) {      
  $logger->debug("REQUEST: change", 1);
  #$logger->debug(var_dump($_REQUEST), 3);

    if ( isset($_REQUEST['sortby'])  )
      $sortby   =$_REQUEST['sortby'];    // inputs validated in Report1->query
    if ( isset($_REQUEST['order_dir'])  )
      $order_dir=$_REQUEST['order_dir'];
    if ( isset($_REQUEST['sortlimit'])  )
      $sortlimit=$_REQUEST['sortlimit'];
    if ( isset($_REQUEST['searchby']) && isset($_REQUEST['searchstring']) ) {
      $searchby   =$_REQUEST['searchby'];    // Column title, but what about name?
      $searchstring=$_REQUEST['searchstring'];
    } 
    $logger->debug("GuiList1_control.php: sortby=$sortby, sortlimit=$sortlimit, "
      ."searchby=$searchby, searchstring=$searchstring order_dir=$order_dir", 2);

    #$report=new CallWrapper(new GuiList1($title, true));                //true=dynamic with filtering
    $report=new GuiList1($title, true, 1);                //true=dynamic with filtering, debug level
    echo $report->query($q, $sortlimit, $sortby, 
       $action_menu, $action_fieldname, $idx_fieldname,
       $searchstring, $searchby, $action_confirm, $order_dir);   // run query, generate report

    echo $report->print_footer();


###### CUSTOM: Print button  ############
} else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Print') { 
  $logger->debug("GuiList1_control action: ". $_REQUEST['action'], 1);

  if ( !isset($_REQUEST['action_fieldname']) || !isset($_REQUEST['action_idxname']) || !isset($_REQUEST['action_idx']) ) {
    throw new InvalidWebInputException("Report has no valid action parameters");
  }
  $action_fieldname=$_REQUEST['action_fieldname'];
  $action_idxname  =$_REQUEST['action_idxname'];
  $action_idx      =$_REQUEST['action_idx'];
  $logger->debug("GuiList1_control action_idx=$action_idx fieldname=$action_fieldname action_idxname=$action_idxname", 2);
  $title="$action_fieldname $action_idx";

  // Security: is the index really a value that was shown in GuiList1,
  //   or perhaps a value injected by a bad guy?
  $indices=unserialize($_SESSION['report1_indices']);  // recover index values
  if (!is_array($indices)) {
    throw new InvalidWebInputException("Invalid indices: not an array");
  } else {
    $count=array_search($action_idx, $indices);
    #$logger->logit("count=$count");
    if ( !is_numeric($count)  )
      throw new InvalidWebInputException("Invalid index: not from previous report, idx=$count.");
  }
  $q=$_SESSION['report1_query'] . " WHERE {$action_idxname}={$action_idx} LIMIT 1";

  #$report=new CallWrapper(new GuiPrint($title));
  $report=(new GuiPrint($title));
  echo $report->query($q);
  echo $report->print_footer();     



###### CUSTOM buttons  ############
} else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Edit') {
  $logger->debug("action: ". $_REQUEST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";

} else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Update') {
  $logger->debug("action: ". $_REQUEST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";

} else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Delete') {
  $logger->debug("action: ". $_REQUEST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";


###### Default page: menu ############
} else {             // this is where we start, first time
  if (isset($_REQUEST['action']) )
     $logger->debug("GuiList1_control.php:default action: " .$_REQUEST['action'], 1);
  else
     $logger->debug("GuiList1_control.php:default action ", 3);

  // Use the default setting in our calling script, or what we get via REQUEST:
    if ( isset($_REQUEST['sortby'])  )
      $sortby   =$_REQUEST['sortby'];    // inputs validated in Report1->query
    if ( isset($_REQUEST['sortlimit'])  )
      $sortlimit=$_REQUEST['sortlimit'];
    if ( isset($_REQUEST['order_dir'])  )
      $order_dir=$_REQUEST['order_dir'];
    if ( isset($_REQUEST['searchby']) && isset($_REQUEST['searchstring']) ) {
      $searchby   =$_REQUEST['searchby'];    // Column title, but what about name?
      $searchstring=$_REQUEST['searchstring'];
    } 
    $logger->debug("GuiList1_control.php: sortby=$sortby, sortlimit=$sortlimit, "
      ."searchby=$searchby, searchstring=$searchstring", 1);
    #$report=new CallWrapper(new GuiList1($title, true));                //true=dynamic with filtering
    $report=new GuiList1($title, true, 3);                //true=dynamic with filtering, debug level
    echo $report->query($q, $sortlimit, $sortby, 
       $action_menu, $action_fieldname, $idx_fieldname,
       $searchstring, $searchby, $action_confirm, $order_dir);   // run query, generate report

    echo $report->print_footer();
}

?>
