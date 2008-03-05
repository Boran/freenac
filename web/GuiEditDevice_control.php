<?php
/**
 *
 * GuiList1_control.php
 *
 * Main, generic logic for instantiating the Report1 class
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
  include '/opt/nac/lib/session.inc.php';

  $logger=Logger::getInstance();
  $logger->setDebugLevel(3); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
# --- end of standard header ------

// Clean inputs from the web, (security)
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);


if (isset($_POST['action']) && $_POST['action']=='Update') {
  $logger->debug("action ". $_POST['action'], 1);
  #$report=new CallWrapper(new GuiEditDevice($_SESSION['report1_index']));
  $report=new GuiEditDevice($_SESSION['report1_index']);
  echo $report->Update();
  echo $report->query();
  echo $report->print_footer();
}
else if (isset($_POST['action']) && $_POST['action']=='Delete') {
  $logger->debug("action ". $_POST['action'], 1);
  $report=new GuiEditDevice($_SESSION['report1_index']);
  echo $report->Delete();


###### CUSTOM: Edit button  ############
} else if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger->debug("action: ". $_POST['action'], 1);


  if ( !isset($_POST['action_fieldname']) || !isset($_POST['action_idxname']) || !isset($_POST['action_idx']) ) {
    throw new InvalidWebInputException("Report has no valid action parameters");
  }
  $action_fieldname=$_POST['action_fieldname'];
  $action_idxname  =$_POST['action_idxname'];
  $action_idx      =$_POST['action_idx'];
  $logger->debug("GuiList1_control action_idx=$action_idx fieldname=$action_fieldname action_idxname=$action_idxname", 2);

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

  $_SESSION['report1_index']=$action_idx; // we're pretty confident, store the index



###### Update button  ############

  $q="{$_SESSION['report1_query']} AND {$_POST['action_idxname']}={$_SESSION['report1_index']} LIMIT 1";
  #$report=new CallWrapper(new GuiEditDevice($_SESSION['report1_index']));
  $report=(new GuiEditDevice($_SESSION['report1_index']));
  echo $report->query();
  echo $report->print_footer();




###### Default page: menu ############
} else {             // this is where we start, first time
  $logger->debug("Report1_control:default action: ". $_POST['action'], 1);
  #$report=new CallWrapper(new GuiList1($title, true));                  //true=dynamic with filtering
  #echo $report->query($q, 10, '', $action_menu, $action_fieldname, $idx_fieldname);      // query, limit, no order
  #echo $report->print_footer();
}

?>
