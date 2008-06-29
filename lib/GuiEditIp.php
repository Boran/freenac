<?php
/**
 * 
 * GuiEditIp.php
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


class GuiEditIp extends WebCommon
{
  private $id, $action, $module;      // See also WebCommon and Common


  function __construct($action, $id=0, $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("__construct id=$id, debug=$debug_level, action=$action", 2);

    // 1. verify/clean 'id'
    if ( !is_numeric($id) )     // must be a number
       throw new InvalidWebInputException("invalid index: <$id> is not an integer");
    //if ( $id===0 )              
    //   throw new InvalidWebInputException(""GuiEditDevice__construct invalid index: zero");

    #if (isset($_REQUEST['action_idx'])) $logger->debug("action_idx=" .$_REQUEST['action_idx'], 2);
    $this->id=$id;                   // remember the record number
    $this->module='ip';
    
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
        if (isset($_REQUEST['address']) && isset($_REQUEST['comment']) ) {
	  // Add step2
          $this->print_title("New {$this->module} record");  
          echo $this->UpdateNew();

        } else {        // Add Step1
          $this->print_title("Add new {$this->module}");
          #echo $this->Add();
          echo $this->query(false);    // update_mode=false
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

    //$q="DELETE FROM ip WHERE id={$device} LIMIT 1";     // only this record
    $q="DELETE FROM ip WHERE id='{$device}' LIMIT 1";     // only this record
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
      $this->logit("Deleted {$this->module} with Id {$device}");
      $this->loggui("Deleted {$this->module} with Id {$device}");

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
      $q="INSERT INTO ip SET  ";

      if ( ! isset($_REQUEST['address']) )  
        throw new DatabaseInsertException("- No address value");
      //if ( ! is_numeric($_REQUEST['address']) ) 
      //  throw new DatabaseInsertException("- Address is not numeric");

        $address=$_REQUEST['address'];
        $q.=" address=INET_ATON('{$_REQUEST['address']}') ";
        if (isset($_REQUEST['subnet']))  $q.=", subnet={$_REQUEST['subnet']} ";
        if (isset($_REQUEST['status']))  $q.=", status={$_REQUEST['status']} ";
        if (isset($_REQUEST['comment'])) $q.=", comment='{$_REQUEST['comment']}' ";
        if (isset($_REQUEST['system']))  $q.=", system={$_REQUEST['system']} ";
        if (isset($_REQUEST['source']))  $q.=", source='{$_REQUEST['source']}' ";
        if (isset($_REQUEST['dns_update'])) $q.=", dns_update={$_REQUEST['dns_update']} ";


      $this->debug("UpdateNew() $q", 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseInsertException($conn->error);

      echo "<p class='UpdateMsgOK'>Successful: new {$this->module} with address=$address added</p>";

      // after inserting, locate that record, and show the Update() screen.
      $res = $conn->query("SELECT address,id from ip where address='" .$address ."'");
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);
      while (($row = $res->fetch_assoc()) !== NULL) {
        $this->id=$row['id'];
      }

      $this->loggui("new {$this->module} id={$row['id']} address=$address added");

      // locate that record, and show the Update() screen.
      $ref0=$this->calling_script;
      $ref1=$ref0. "?action=Edit&action_idx=$this->id";
      echo "<br><p>Now review/update the <a href='{$ref1}'>{$this->module} details</a> or go back to the <a href='{$ref0}'>{$this->module} list</a></p>";

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
        $q='UPDATE ip SET ';
        $q.=" address=INET_ATON('{$_REQUEST['address']}') ";
        if (isset($_REQUEST['subnet']))  $q.=", subnet={$_REQUEST['subnet']} ";
        if (isset($_REQUEST['status']))  $q.=", status={$_REQUEST['status']} ";
        if (isset($_REQUEST['comment'])) $q.=", comment='{$_REQUEST['comment']}' ";
        if (isset($_REQUEST['system']))  $q.=", system={$_REQUEST['system']} ";
        if (isset($_REQUEST['source']))  $q.=", source='{$_REQUEST['source']}' ";
        if (isset($_REQUEST['dns_update'])) $q.=", dns_update={$_REQUEST['dns_update']} ";

        $q.=" WHERE id={$this->id} LIMIT 1";     // only this record

      $this->debug("Update() " .$q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      #echo "<p class='UpdateMsgOK'>Update Successful</p>";
 $txt=<<<TXT
<p class='UpdateMsgOK'>Update Successful</p>
 <br><p> Go back to the <a href="{$_SESSION['caller']}">{$this->module} list</a></p>
TXT;
      echo $txt;

      $this->loggui("{$this->module} index {$this->id}  updated");

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

    $comment='Added from WebGUI'; 
    try {

        // Name
        $output.=<<<TXT
        <tr><td width="87"  title="Enter a valid IP address, in the format W.X.Y.Z ">IP Address:</td>
            <td width="400"> <input name="address" type="text" value="" onBlur="checkLen(this,4) ">
        </td></tr>
        <tr><td width="87" title="Comment?">Comment:</td>
            <td width="400"> <input name="comment" type="text" value="{$comment}" onBlur="checkLen(this,0)">
        </td></tr>
TXT;
        // Subnet
        $output.=  '<tr><td title="Which Subnet does this below to?">Subnet:</td><td>'."\n";
        $output.=  $this->get_subnetdropdown(1) . '</td></tr>'."\n";

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
  public function query($update_mode=TRUE)
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection
    #$output ='<form action="GuiEditDevice_control.php" method="POST">';
    $output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'; //debugging
    $output.="<table id='t3' width='760' border='0' class='text13'>";

    try {

      if ($update_mode) {
        $q=<<<TXT
SELECT id,INET_NTOA(address) AS address,subnet,status,comment,system,source,dns_update from ip
  WHERE id='{$this->id}'
  LIMIT 1
TXT;
        $this->debug("Editip::query() $q", 3);
        $res = $conn->query($q);
        if ($res === FALSE)
          throw new DatabaseErrorException($conn->error);

        // Title: Grab the list of field names
        #$fields=$res->fetch_fields();
        #while (($row = $res->fetch_assoc()) !== NULL) {
        $row = $res->fetch_assoc();

      } else { 
        // add mode, only show defaults 
        $row['address']='';
        $row['comment']='';
        $row['subnet']=0;
        $row['id']=0;
        $row['source']='';
        $row['system']=0;
      }

      # Display the record:
        #$this->debug(var_dump($row), 3);
        $output.= '<tr><td width="87">IP Address:</td><td width="400">' ."\n";
        $output.= '<input name="address" type="text" value="' .stripslashes($row['address']) .'"/>' ."\n";
        $output.= '</td><td>Index:' .$row['id'] .'</td>' ."</tr>\n";

        // Subnet
        $output.=  '<tr><td>Subnet:</td><td>' ."\n";
        $output.=  $this->get_subnetdropdown($row['subnet']) . '</td></tr>'."\n";

        $output.= '<tr><td width="87">IP Comment:</td><td width="400">' ."\n";
        $output.= '<input name="comment" type="text" value="' .stripslashes($row['comment']) .'"/>' ."\n";

        $output.= '<tr><td width="87">IP Source:</td><td width="400">' ."\n";
        $output.= '<input name="source" type="text" value="' .stripslashes($row['source']) .'"/>' ."\n";


        // System
        $output.=  '<tr><td>End-Device:</td><td>' ."\n";
        $output.=  $this->get_systemdropdown($row['system']) . '</td></tr>'."\n";

        // Status
        //$output.=  '<tr><td>Status:</td><td>' ."\n";
        //$output.=  $this->get_statusdropdown($row['status']) . '</td></tr>'."\n";

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

      #}
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



function get_statusdropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="select value from vstatus where id='$s';";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['value'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     $ret='<select name="status">';

     $q='SELECT id, value FROM vstatus ORDER BY value ASC;';
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['id'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
            .'>' .$row['value'] .'</option>' ."\n";
     }
     $ret.="</select> \n";
   }

   return $ret;
}

function get_systemdropdown($s)       //TBD: return mask too
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   $q="select id,name,mac,comment,r_ip,r_timestamp from systems ";
   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q.=" where id='$s';";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['name'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     $q.=" ORDER BY name";
     $ret='<select name="system">';
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['id'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
            .'>' .$row['name'] .': ' .$row['r_ip'] .' on ' .$row['r_timestamp'] .', mac=' .$row['mac'] .', comment=' .$row['comment'] .'</option>' ."\n";
     }
     $ret.="</select> \n";
   }

   return $ret;
}

function get_subnetdropdown($s)       //TBD: return mask too
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="select id,ip_address,ip_netmask from subnets where id='$s';";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['ip_address'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     //$q="select id,ip_address,ip_netmask from subnets ORDER BY ip_address ASC where id='$s';";
     $q="select id,ip_address,ip_netmask from subnets ORDER BY ip_address";
     $ret='<select name="subnet">';
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['id'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
            .'>' .$row['ip_address'] .' /' .$row['ip_netmask'].'</option>' ."\n";
     }
     $ret.="</select> \n";
   }

   return $ret;
}


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
