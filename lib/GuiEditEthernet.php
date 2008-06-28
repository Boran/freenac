<?php
/**
 * 
 * GuiEditEthernet.php
 *
 * Long description for file:
 * Allow records to edited, deleted or inserted.
 * Specific to the FreeNAC DB schema.
 *
 * @package     FreeNAC
 * @author      Many: S.Boran, T.Dagonnier, P.Bizeau
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiEditEthernet extends WebCommon
{
  private $id, $action, $module;      // See also WebCommon and Common


  function __construct($action, $id=0, $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("__construct id=$id, debug=$debug_level, action=$action", 2);

    // 1. verify/clean 'id'
    #if ( !is_int($id) )     // must be a number
    //if ( !is_numeric($id) )     // must be a number
    //   throw new InvalidWebInputException("invalid index: <$id> is not an integer");
    //if ( $id===0 )              
    //   throw new InvalidWebInputException(""GuiEditDevice__construct invalid index: zero");

    #if (isset($_REQUEST['action_idx'])) $logger->debug("action_idx=" .$_REQUEST['action_idx'], 2);
    $this->id=$id;                   // remember the record number
    $this->module='ethernet';
    //$_SESSION['report1_index']=$id;  // for passing to other scripts: no longer used?
    
    // 2. verify/clean 'action'
    // Now, have we a REQUEST action to carry out?
    if ( !isset($action) ) {
       throw new InvalidWebInputException("No action ");
    }
    $this->action=validate_webinput($action);

  }


  protected function print_title($title)
  {
    echo $this->print_header();
    echo "<div id='GuiList1Title'>{$title}</div>";
    //$this->debug($_SESSION['login_data'] .":Id=$id:" , 1);
  }


  public function handle_request()
  {
    $action=$this->action;
    #global $_SESSION, $_REQUEST;
    #$_REQUEST=array_map('validate_webinput',$_REQUEST);
    $this->debug("handle_request() $action", 2);

    if (isset($action)) {
      if ($action==='Update') {
        $this->print_title("Update {$this->module} Details");
        #$logger->debug("action=$action, report1_index=" .$_SESSION['report1_index'], 1);
        echo $this->Update();
        echo $this->query();
        echo $this->print_footer();

      } else if ($action==='Edit') {
        $this->print_title("Edit {$this->module} Details");
        echo $this->query();
        echo $this->print_footer();

       
      } else if ($action==='Add') {
        if (isset($_REQUEST['vendor']) && isset($_REQUEST['mac']) ) {
          $this->print_title("New {$this->module} record");  // Add step2
          echo $this->UpdateNew();
        } else {        // Add Step1
          $this->print_title("Add new {$this->module}");
          echo $this->Add();
        }
        echo $this->print_footer();
       
      } else if ($action==='Delete') {
        $this->print_title("Delete {$this->module} ");
        $this->Delete();
       
      } else {
        // do nothing, action does not concern us.
      }
    }
  }




  public function Delete()
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    if ( $this->id===0 )              
      throw new InvalidWebInputException("Delete() invalid index: zero");

    $conn=$this->getConnection();     //  make sure we have a DB connection
    #var_dump($_REQUEST);
    $device=$this->id;    // rely on the constructor to clean & ensure a valid id
    $this->debug("Delete() index {$device}", 3);

    //$q="DELETE FROM ethernet WHERE id={$device} LIMIT 1";     // only this record
    $q="DELETE FROM ethernet WHERE mac='{$device}' LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);

      // Inform the user that is was OK
      $txt=<<<TXT
<p class='UpdateMsgOK'>Delete Successful</p>
 <br><p > Go back to the <a href="{$_SESSION['caller']}">{$this->module} list</a></p>
</div>
TXT;
      echo $txt;
      $this->logit("Deleted {$this->module} with Mac Prefix {$device}");
      $this->loggui("Deleted {$this->module} with Mac Prefix {$device}");

  }



  /**
   * Insert a newrecord
   */
  public function UpdateNew()
  {
    $this->debug("UpdateNew()", 3);
    #var_dump($_REQUEST);

    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

    try {
      // Read in request variables. Mac and name are set, others are optional
      // TBD: call validate_input?
      $name=trim($_REQUEST['vendor']);            // get rid of leading/trailing spaces
      $mac=strtolower($_REQUEST['mac']);        // lower case by convention
      $mac=$this->sqlescape($mac);     		// TBD: verify syntax/length etc.
      $name=$this->sqlescape($name);   		
      $q="INSERT INTO ethernet SET mac='$mac', vendor='$name' ";

      $this->debug("UpdateNew() $q", 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseInsertException($conn->error);

      echo "<p class='UpdateMsgOK'>Successful: new {$this->module} $name/$mac added</p>";

      // after inserting, locate that record, and show the Update() screen.
      $res = $conn->query("SELECT mac,vendor from ethernet where mac='" .$mac ."'");
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);
      while (($row = $res->fetch_assoc()) !== NULL) {
        //$this->id=$row['id'];
        $this->id=$row['mac'];
      }

      $this->loggui("new {$this->module} $name, mac=$mac added");

      // locate that record, and show the Update() screen.
      $ref=$this->calling_script. "?action=Edit&action_idx=$this->id";
      #echo $ref;
      #$this->debug($ref); 
      echo "<p class='UpdateMsgOK'>Now review/update the <a href='$ref'>{$this->module} details</a></p>";

    } catch (Exception $e) {
      throw $e;
    }

 }



  /**
   * Update a record
   */
  public function Update()
  {
    $this->debug("Update()", 3);
    #var_dump($_REQUEST);
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException('Update() ' .$_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

    // Clean inputs from the web, (security). Use _REQUEST to
    // allow both GET (automation) or POST (interactive GUIs)
    $_REQUEST=array_map('validate_webinput',$_REQUEST);
    if (!isset($_REQUEST['action_idx']) )
      throw new InvalidWebInputException("Update() action_idx not set");
    #if ( !is_numeric($_REQUEST['action_idx']) || $_REQUEST['action_idx']==0)     // must be a number>0
    #   throw new InvalidWebInputException("invalid index: is not an integer");

    $this->debug("Update() action_idx={$_REQUEST['action_idx']}", 3);
    $this->id=$_REQUEST['action_idx'];

    try {
      $q='';
        $q='UPDATE ethernet SET ';
        $q.=($_REQUEST['mac']!='' ? 'mac=\'' .$_REQUEST['mac'] .'\' ' : '');
        $q.=", vendor='" .$_REQUEST['vendor'] ."'";
;
        //$q.=" WHERE id={$this->id} LIMIT 1";     // only this record
        $q.=" WHERE mac='{$this->id}' LIMIT 1";     // only this record

      $this->debug("Update() " .$q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      echo "<p class='UpdateMsgOK'>Update Successful</p>";
      $this->loggui("{$this->module} " .$_REQUEST['vendor'] ."/" .$_REQUEST['mac'] ." updated");

    } catch (Exception $e) {
      throw $e;
    }
  }


  /**
   * Add a new 
   */
  public function add()
  {
    global $js1;
    if ($_SESSION['nac_rights']<2)    // must have edit rights
      throw new InsufficientRightsException($_SESSION['nac_rights']);

    $conn=$this->getConnection();     //  make sure we have a DB connection
    $this->debug("Add() ", 3);
    $output ='<form name="formadd" action="' .$_SERVER['PHP_SELF'] .'" method="POST">';
    $output.= "\n$js1\n <table id='GuiEditDeviceAdd'>";

    $vendor=''; $mac='0001'; 
    try {

        // Name, MAC
        $output.=<<<TXT
        <tr><td width="87" title="What name of the Vendor?">Vendor:</td>
            <td width="400"> <input name="vendor" type="text" value="{$vendor}" onBlur="checkLen(this,1)">
        </td></tr>
        <tr><td width="87"  title="Enter a valid 4 digit hex MAC prefix, in the format xxxx ">MAC prefix:</td>
            <td width="400"> <input name="mac" type="text" value="{$mac}" onBlur="checkLen(this,4) ">
        </td></tr>
TXT;
        // Status
        //$output.=  '<tr><td title="Is the new device to be allowed on the network">Status:</td><td>'."\n";
        //$output.=  $this->get_statusdropdown(1) . '</td></tr>'."\n";

      $output.=<<<TXT
        <tr><td></td><td>
        <input type="submit" class="bluebox" name="action" value="Add" onclick="return checkForm()"
		title="Click to add a new {$this->module} with the above details"/>
	</td>
        </form>
	</table>
TXT;

    } catch (Exception $e) {
      throw $e;
    }
    return($output);
  }                               // function




  /**
   * Display a record, allow changes.
   * Next Step is Either Update, Delete, or Restart Port
   */
  public function query()
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection
    #$output ='<form action="GuiEditDevice_control.php" method="POST">';
    $output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'; //debugging
    $output.="<table id='t3' width='760' border='0' class='text13'>";

$q=<<<TXT
SELECT mac,vendor from ethernet
  WHERE mac='{$this->id}'
  LIMIT 1
TXT;

    try {
      $this->debug("EditEthernet::query() $q", 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      // Title: Grab the list of field names
      $fields=$res->fetch_fields();
      while (($row = $res->fetch_assoc()) !== NULL) {
        #$this->debug(var_dump($row), 3);
        $output.= '<tr><td width="87">Vendor:</td><td width="400">' ."\n";
        $output.= '<input name="vendor" type="text" value="' .stripslashes($row['vendor']) .'"/>' ."\n";
        //$output.= '</td><td>Index:' .$row['id'] .'</td>' ."</tr>\n";
        // MAC
        $output.= '<tr><td>MAC:</td><td>' ."\n";
        $output.= $row['mac']  ."\n";
        $output.= '</td></tr>'."\n";
        $output.= '<input type="hidden" name="mac" value="' .$row['mac'] .'" />' ."\n";
        // Status
        //$output.=  '<tr><td>Status:</td><td>' ."\n";
        //$output.=  $this->get_statusdropdown($row['status']) . '</td></tr>'."\n";

        // Submit
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.=<<<TXT
          <tr><td>&nbsp;</td><td>
          <input type="submit" name="action" class="bluebox" value="Update" />&nbsp;
          <input type="submit" name="action" class="bluebox" value="Delete" 
            onClick="javascript:return confirm('Really DELETE this record?')"
            />
          </td></tr>'
TXT;
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";

      }
      // close the table
      $output.= '</table> ';

      #$output.= '<!input type="hidden" name="action" value="update" />'
      $output.= ''
        . '<input type="hidden" name="action_idx" value="' .$this->id .'" /></form>';
        #. '<input type="hidden" name="id" value="' .$row['id'] .'" /></form>';


    } catch (Exception $e) {
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    return($output);
  }                               // function




} // class



/////////// main() should never get here .. ///////////////////////////////////////
if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger=Logger::getInstance();
  $logger->debug("Edit__:action:". $_POST['action'], 1);
}

if ( isset($_POST['submit']) ) {             // form submit, check fields
## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);
  $logger->debug("Edit__ main -submit");
  #echo handle_submit();

} else {    
  # Do nothing, we've been included.
}

?>
