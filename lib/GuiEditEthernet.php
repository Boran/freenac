<?php
/**
 * 
 * GuiEditEthernet.php
 *
 * Long description for file: ethernet table
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
  private $id, $action;      // See also WebCommon and Common

  function __construct($action, $id=0, $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("__construct id=$id, debug=$debug_level, action=$action", 2);

    // The Ethernet table has no 'id' column, unfortunately. But we 
    // leave the relevant code here for this to be fixed later..
    // 1. verify/clean 'id'
    //if ( !is_numeric($id) )     // must be a number
    //   throw new InvalidWebInputException("invalid index: <$id> is not an integer");
    //if ( $id===0 )              
    //   throw new InvalidWebInputException(""GuiEditDevice__construct invalid index: zero");

    $this->id=$id;                   // remember the record number
    $this->module='Ethernet';              // identify module, in Webcommon
    $this->table='ethernet';               // identify SQL table, in Webcommon

    // 2. verify/clean 'action'
    // Now, have we a REQUEST action to carry out?
    if ( !isset($action) ) {
       throw new InvalidWebInputException("No action ");
    }
    $this->action=validate_webinput($action);

  }


  public function handle_request()
  {
    $action=$this->action;
    #global $_SESSION, $_REQUEST;
    #$_REQUEST=array_map('validate_webinput',$_REQUEST);
    $this->debug("handle_request() $action", 2);

    if (isset($action)) {
      if ($action==='Update') {
        echo $this->print_title("Update {$this->module} Details");
        #$logger->debug("action=$action, report1_index=" .$_SESSION['report1_index'], 1);
        echo $this->InsertOrUpdate();
        echo $this->edit_record();          // Show update form
        echo $this->print_footer();

      } else if ($action==='Edit') {
        echo $this->print_title("Edit {$this->module} Details");
        echo $this->edit_record();          // Show update form
        echo $this->print_footer();
       
      } else if ($action==='Add') {

        if (isset($_REQUEST['vendor']) && isset($_REQUEST['mac']) ) {
          echo $this->print_title("New {$this->module} record");  // Add step2
          echo $this->InsertOrUpdate(FALSE);  // Add mode
        } else {        // Add Step1
          echo $this->print_title("New {$this->module} record");
          echo $this->edit_record(false);    // Show Add form: update_mode=false
        }
        echo $this->print_footer();
       
      } else if ($action==='Delete') {
        echo $this->Delete();
       
      } else {
        // do nothing, action does not concern us.
      }
    }
  }


  /**
   * Delete()  // Override the Webcommon functiion since we do not have
   * an id column.
   */
  public function Delete()  
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    if ( $this->id===0 )              
      throw new InvalidWebInputException("Delete() invalid index: zero");

    $ret = $this->print_title("Delete {$this->module} record");
    $conn=$this->getConnection();     //  make sure we have a DB connection
    #var_dump($_REQUEST);
    $id=$this->id;
    $this->debug("Delete() index {$id}", 3);

    $q="DELETE FROM {$this->table} WHERE mac='{$id}' LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);

    // Inform the user that is was OK
    $txt=<<<TXT
<p class='UpdateMsgOK'>Delete Successful</p>
 <br><p>Go back to the <a href="{$this->calling_href}">{$this->module} list</a></p>
</div>
TXT;
      $ret.= $txt;
      $this->logit("Deleted {$this->module} with Mac Prefix {$id}");
      $this->loggui("Deleted {$this->module} with Mac Prefix {$id}");
    return $ret;
  }


  /**
   * Update a record
   */
  public function InsertOrUpdate($update_mode=TRUE)
  {
    $this->debug("InsertOrUpdate() update_mode=$update_mode", 2);

    #var_dump($_REQUEST);
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException('Update() ' .$_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

    // Clean inputs from the web, (security). Use _REQUEST to
    // allow both GET (automation) or POST (interactive GUIs)
    $_REQUEST=array_map('validate_webinput',$_REQUEST);
    if (!isset($_REQUEST['action_idx']) )
      throw new InvalidWebInputException("InsertOrUpdate() action_idx not set");
    #if ( !is_numeric($_REQUEST['action_idx']) || $_REQUEST['action_idx']==0)     // must be a number>0
    #   throw new InvalidWebInputException("invalid index: is not an integer");
    #$mac=$this->sqlescape($name);

    $q='';
    if ($update_mode==TRUE) {
      $this->debug("Update() action_idx={$_REQUEST['action_idx']}", 3);
      $this->id=$_REQUEST['action_idx'];
      $q="UPDATE {$this->table} SET ";

    } else {
      $q="INSERT INTO {$this->table} SET ";
    }

    try {
      $q.=($_REQUEST['mac']!='' ? 'mac=\'' .strtoupper($_REQUEST['mac']) .'\' ' : '');
      if (isset($_REQUEST['vendor'])) $q.=", vendor='{$_REQUEST['vendor']}' "; //string

      if ($update_mode==TRUE) {
        //$q.=" WHERE id={$this->id} LIMIT 1";     // only this record
        $q.=" WHERE mac='{$this->id}' LIMIT 1";     // only this record
      }

      $this->debug("InsertOrUpdate() Query=" .$q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      echo "<p class='UpdateMsgOK'>Update Successful</p>";
      if ($update_mode==TRUE) {
        $this->loggui("{$this->module} " .$_REQUEST['vendor'] ."/" .$_REQUEST['mac'] ." updated");

      } else {
        // after inserting, locate that record, and show the Update() screen.
        #$res = $conn->query("SELECT mac,vendor from ethernet where mac='" .$mac ."'");
        #if ($res === FALSE)
        #  throw new DatabaseErrorException($conn->error);
        #while (($row = $res->fetch_assoc()) !== NULL) {
        #  //$this->id=$row['id'];
        #}
        $this->id=$_REQUEST['mac'];

        $this->loggui("{$this->module} " .$_REQUEST['vendor'] ."/" .$_REQUEST['mac'] ." added");

      }

      // locate that record, and show the Update() screen.
        $ref0=$this->calling_script;
        $ref1=$ref0. "?action=Edit&action_idx=$this->id";
        echo "<br><p>Now review/update the <a href='{$ref1}'>{$this->module} details</a> or go back to the <a href='{$ref0}'>{$this->module} list</a></p>";


    } catch (Exception $e) {
      throw $e;
    }
  }


  /**
   * Display a record, allow changes.
   * Next Step is Either Update, Delete, or Restart Port
   */
  public function edit_record($update_mode=TRUE)
  {
    global $js1;

    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

    $output ='<form name="formadd" action="' .$_SERVER['PHP_SELF'] .'" method="POST">';
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'; //debugging
    $output.= "\n$js1\n <table id='GuiEditDeviceAdd'>";

    try {
      if ($update_mode) {
        $q=<<<TXT
SELECT mac,vendor from ethernet
  WHERE mac='{$this->id}'
  LIMIT 1
TXT;
        $this->debug("edit_record() update_mode=query, $q", 3);
        $res = $conn->query($q);
        if ($res === FALSE)
          throw new DatabaseErrorException($conn->error);

        // Title: Grab the list of field names
        #$fields=$res->fetch_fields();
        $row = $res->fetch_assoc();

      } else {
        $this->debug("edit_record() update_mode=add, $q", 3); // add mode, only show defaults
        $row['mac']='F00001';    // a default not used?
        $row['vendor']='';       // TBD: default?
      }

      # Display the record:
        #$this->debug(var_dump($row), 3);
        $output.= '<tr><td width="87" title="What is name of the Vendor?">Vendor:</td><td width="400">' ."\n";
        $output.= '<input name="vendor" type="text" value="' .stripslashes($row['vendor']) .'"/>' ."\n";
        //$output.= '</td><td>Index:' .$row['id'] .'</td>' ."</tr>\n";
        // MAC
        $output.= '<tr><td title="Enter a valid 6 digit hex MAC prefix, in the format xxxxxx ">MAC prefix:</td><td>' ."\n";
        $output.= '<input name="mac"  type="text" value="' .$row['mac'] .'" />' ."\n";
        $output.= '</td></tr>'."\n";

        // Submit
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td>' ."\n";
        if ($update_mode) {
          $output.=<<<TXT
          <input type="submit" name="action" class="bluebox" value="Update" />&nbsp;
          <input type="submit" name="action" class="bluebox" value="Delete"
            onClick="javascript:return confirm('Really DELETE this record?')"
            />
TXT;
        } else {
          // add mode, only show defaults
          $output.=<<<TXT
        <input type="submit" class="bluebox" name="action" value="Add" onclick="return checkForm()"
                title="Click to add a new {$this->module} with the above details"/>
TXT;
        }

        $output.= '</td></tr> <tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";

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
