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
  $logger->setDebugLevel(1); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
# --- end of standard header ------

// Clean inputs from the web, (security)
// Use _REQUEST to allow data to flow via GET (automation) 
// or POST (interactive GUIs)
   $_COOKIE=array_map('validate_webinput',$_COOKIE);
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_REQUEST=array_map('validate_webinput',$_REQUEST);


if (isset($_REQUEST['action']) && $_REQUEST['action']=='Update') {
  $logger->debug("action=". $_REQUEST['action'] 
    .", report1_index=" .$_SESSION['report1_index'] .", action_idx=" .$_REQUEST['action_idx'], 1);

  if (is_numeric($_SESSION['report1_index']) && ($_SESSION['report1_index']>0) ) {
    #$report=new CallWrapper(new GuiEditDevice($_SESSION['report1_index']));
    $report=new GuiEditDevice($_SESSION['report1_index']);

  } else if (is_numeric($_REQUEST['action_idx']) && ($_REQUEST['action_idx']>0) ){
    $report=new GuiEditDevice($_REQUEST['action_idx']);

  } else {
    throw new InvalidWebInputException("Invalid record index: cannot edit");
  }
  echo $report->Update();
  echo $report->query();
  echo $report->print_footer();
}


else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Delete') {
  $logger->debug("action ". $_REQUEST['action'], 1);
  if (is_numeric($_SESSION['report1_index']) && ($_SESSION['report1_index']>0) ) {
    $report=new GuiEditDevice($_SESSION['report1_index']);
  } else {
    throw new InvalidWebInputException("Invalid record index: cannot delete");
  }
  echo $report->Delete();
} 


###### CUSTOM: Edit button  ############
else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Edit') {
  $logger->debug("action: ". $_REQUEST['action'], 1);

  #°if ( !isset($_REQUEST['action_fieldname']) || !isset($_REQUEST['action_idxname']) || !isset($_REQUEST['action_idx']) ) {
  if ( !isset($_REQUEST['action_idx']) ) {
    throw new InvalidWebInputException("Report has no valid action parameters");
  }
  $action_fieldname=$_REQUEST['action_fieldname'];
  $action_idxname  =$_REQUEST['action_idxname'];
  $action_idx      =$_REQUEST['action_idx'];
  $logger->debug("GuiList1_control action_idx=$action_idx fieldname=$action_fieldname action_idxname=$action_idxname", 2);

/*
  // Security: is the index really a value that was shown in report1,
  //   or perhaps a value injected by a bad guy?
  //if (! isset($_SESSION['report1_index']))
  //  throw new InvalidWebInputException("Session no longer valid - index invalid");
  //if (! is_numeric($_SESSION['report1_index']))
  //  throw new InvalidWebInputException("Session no longer valid - index not numeric");
  $indices=unserialize($_SESSION['report1_indices']);  // recover index values
  if (!is_array($indices)) {
    throw new InvalidWebInputException("Invalid indices: not an array");
  } 
  else {
    $count=array_search($action_idx, $indices);
    #$logger->logit("count=$count");
    if ( !is_numeric($count)  )
      throw new InvalidWebInputException("Invalid index: not from previous report, idx=$count.");
  }
*/
  $_SESSION['report1_index']=$action_idx; // we're pretty confident, store the index

  #$report=new CallWrapper(new GuiEditDevice($_SESSION['report1_index']));
  $report=(new GuiEditDevice($_SESSION['report1_index']));
  echo $report->query();
  echo $report->print_footer();

} /////// end of custom buttons


else if (isset($_REQUEST['action']) && $_REQUEST['action']=='Add') {
  $logger->debug("action ". $_REQUEST['action'], 1);
  if (isset($_REQUEST['name']) && isset($_REQUEST['mac']) ) {
    // send request to verify and add new device
    $report=new GuiEditDevice(0, "Update new End-Device " .$_REQUEST['name']);
    echo $report->UpdateNew();
    #echo $report->query();
    echo $report->print_footer();

  }
  else {
    $logger->debug("GuiEditDevice_control: Add, but no name/mac, so display Add()", 1);
    $report=new GuiEditDevice(0, "Add new End-Device");
    echo $report->Add();
    echo $report->print_footer();
  }
} 

###### Default page: menu ############
else {             // this is where we start, first time
  if ( isset($_REQUEST['action']) ) {
    $logger->debug("GuiEditDevice_control: ignoring default action: ". $_REQUEST['action'], 1);
  }
  else {
    $logger->debug("GuiEditDevice_control: default REQUEST, but no action: assuming ADD ", 1);
    $report=new GuiEditDevice(0, "Add new End-Device");
    echo $report->Add();
    echo $report->print_footer();
  }
}

?>
