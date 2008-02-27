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
  include '/opt/nac/lib/session.inc.php'; // TBD: get rid of absolute path?

  $logger=Logger::getInstance();
  $logger->setDebugLevel(3); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
# --- end of standard header ------

// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);


## The following MUST be set in the file that includes us
## session must also be set there
if ( !isset($title) || !isset($action_menu) || !isset($q) ) {
    // TBD: use an exception
    $logger->debug('Report has no valid parameters', 1);
    $report=new GuiList1("Report", false); 
    echo "<hr><font class=text16red><p>Report has no valid parameters.</p></font>";
    echo $report->print_footer();
}


## $action_fieldname should also be set if needed
$action_fieldname = isset($action_fieldname) ? $action_fieldname : '';
$idx_fieldname    = isset($idx_fieldname) ? $idx_fieldname : $action_fieldname;


###### Standard CHANGE (limit|sort) button ############
if ( isset($_POST['change']) ) {      
  $logger->debug("POST: change", 1);
  #$logger->debug(var_dump($_POST), 3);

  global $logger, $q;

  if ( ( !isset($_POST['sortby']) || !isset($_POST['sortlimit']) )
       || empty($_POST['sortby']) || empty($_POST['sortlimit'])
       )  { // show an error message

    $logger->debug('No change submitted: order or limit invalid', 1);
    #$report=new CallWrapper(GuiList1($title, false));   // very verbose!
    $report=(GuiList1($title, false)); 
    echo "<hr><font class=text16red><p>No change selected: order or limit invalid.</p></font>";
    echo $report->print_footer();

  } else {
    if ( isset($_POST['sortby'])  )
      $sortby   =$_POST['sortby'];    // inputs validated in Report1->query
    if ( isset($_POST['sortlimit'])  )
      $sortlimit=$_POST['sortlimit'];
    if ( isset($_POST['searchby']) && isset($_POST['searchstring']) ) {
      $searchby   =$_POST['searchby'];    // Column title, but what about name?
      $searchstring=$_POST['searchstring'];
    } 
    $logger->debug("Report1_control.php: sortby=$sortby, sortlimit=$sortlimit, "
      ."searchby=$searchby, searchstring=$searchstring", 1);

    #$report=new CallWrapper(new GuiList1($title, true));                //true=dynamic with filtering
    $report=(new GuiList1($title, true));                //true=dynamic with filtering
    echo $report->query($q, $sortlimit, $sortby, 
       $action_menu, $action_fieldname, $idx_fieldname,
       $searchstring, $searchby);   // run query, generate report

    echo $report->print_footer();
  }


###### CUSTOM: Print button  ############
} else if (isset($_POST['action']) && $_POST['action']=='Print') { 
  $logger->debug("action: ". $_POST['action'], 1);

  if ( !isset($_POST['action_fieldname']) || !isset($_POST['action_idxname']) || !isset($_POST['action_idx']) ) {
    throw new InvalidWebInputException("Report has no valid action parameters");
  }
  $action_fieldname=$_POST['action_fieldname'];
  $action_idxname  =$_POST['action_idxname'];
  $action_idx      =$_POST['action_idx'];
  $logger->debug("action_idx=$action_idx fieldname=$action_fieldname action_idxname=$action_idxname", 2);
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
} else if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger->debug("action: ". $_POST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";

} else if (isset($_POST['action']) && $_POST['action']=='Update') {
  $logger->debug("action: ". $_POST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";

} else if (isset($_POST['action']) && $_POST['action']=='Delete') {
  $logger->debug("action: ". $_POST['action'], 1);
  $_SESSION['caller']=basename($_SERVER['SCRIPT_FILENAME']);
  include "GuiEditDevice_control.php";


###### Default page: menu ############
} else {             // this is where we start, first time
  if (isset($_POST['action']) )
     $logger->debug("Report1_control:default action: " .$_POST['action'], 1);
  else
     $logger->debug("Report1_control:default action ", 3);
  #$report=new CallWrapper(new GuiList1($title, true));                  //true=dynamic with filtering
  #echo $report->query($q, 10, '', $action_menu, $action_fieldname, $idx_fieldname);      // query, limit, no order
  #echo $report->print_footer();

  // Use the default setting in our calling script, or what we get via POST:
    if ( isset($_POST['sortby'])  )
      $sortby   =$_POST['sortby'];    // inputs validated in Report1->query
    if ( isset($_POST['sortlimit'])  )
      $sortlimit=$_POST['sortlimit'];
    if ( isset($_POST['searchby']) && isset($_POST['searchstring']) ) {
      $searchby   =$_POST['searchby'];    // Column title, but what about name?
      $searchstring=$_POST['searchstring'];
    } 
    $logger->debug("Report1_control.php: sortby=$sortby, sortlimit=$sortlimit, "
      ."searchby=$searchby, searchstring=$searchstring", 1);
    #$report=new CallWrapper(new GuiList1($title, true));                //true=dynamic with filtering
    $report=(new GuiList1($title, true));                //true=dynamic with filtering
    echo $report->query($q, $sortlimit, $sortby, 
       $action_menu, $action_fieldname, $idx_fieldname,
       $searchstring, $searchby);   // run query, generate report

    echo $report->print_footer();
}

?>
