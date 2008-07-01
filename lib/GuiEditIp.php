<?php
/**
 * 
 * GuiEditIp.php
 *
 * Long description for file: ip table
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
  private $id, $action;      // See also WebCommon and Common


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

    $this->id=$id;                   // remember the record number
    $this->module='IP';              // identify module, in Webcommon
    $this->table='ip';               // identify SQL table, in Webcommon
    
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
        echo $this->InsertOrUpdate();
        echo $this->edit_record();          // Show update form
        echo $this->print_footer();

      } else if ($action==='Edit') {
        echo $this->print_title("Edit {$this->module} Details");
        echo $this->edit_record();          // Show update form
        echo $this->print_footer();
       
      } else if ($action==='Add') {

        if (isset($_REQUEST['address']) && isset($_REQUEST['comment']) ) {
	  // Add step2
          echo $this->print_title("New {$this->module} record");  
          echo $this->InsertOrUpdate(FALSE);  // Add mode

        } else {        // Add Step1
          echo $this->print_title("Add new {$this->module}");
          echo $this->edit_record(false);    // Show Add form: update_mode=false
        }
        echo $this->print_footer();
       
      } else if ($action==='Delete') {
        echo $this->Delete($this->table, $this->id);
       
      } else {
        // do nothing, action does not concern us.
      }
    }
  }



  /**
   * Insert or Update a record
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
    #if ( !is_numeric($_REQUEST['action_idx']) || $_REQUEST['action_idx']==0) 
    #   throw new InvalidWebInputException("invalid index: is not an integer");
    #$mac=$this->sqlescape($name);

    if ( ! isset($_REQUEST['address']) )
      throw new DatabaseInsertException("- No address value");
    //if ( ! is_numeric($_REQUEST['address']) )
    //  throw new DatabaseInsertException("- Address is not numeric");

    $q='';
    if ($update_mode==TRUE) {
      $this->debug("Update() action_idx={$_REQUEST['action_idx']}", 3);
      $this->id=$_REQUEST['action_idx'];
      $q="UPDATE {$this->table} SET ";

    } else {
      $q="INSERT INTO {$this->table} SET ";
    }

    try {
      $address=trim($_REQUEST['address']);
        $q.=" address=INET_ATON('{$address}') ";
        if (isset($_REQUEST['subnet']))  $q.=", subnet={$_REQUEST['subnet']} ";
        if (isset($_REQUEST['system']))  $q.=", system={$_REQUEST['system']} ";
        if (isset($_REQUEST['status']))  $q.=", status={$_REQUEST['status']} ";
        if (isset($_REQUEST['comment'])) $q.=", comment='{$_REQUEST['comment']}' "; //string
        if (isset($_REQUEST['source']))  $q.=", source='{$_REQUEST['source']}' ";
        if (isset($_REQUEST['dns_update'])) $q.=", dns_update={$_REQUEST['dns_update']} ";

      if ($update_mode==TRUE) {
        $q.=" WHERE id={$this->id} LIMIT 1";     // only this record
      }

      $this->debug("InsertOrUpdate() Query=" .$q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      // Update Aliases in the systems table, if needed.
      if (isset($_REQUEST['aliases']) && (strlen($_REQUEST['aliases'])>0) && ($_REQUEST['system'] > 0) ) {
        $q= "UPDATE systems set dns_alias='{$_REQUEST['aliases']}' WHERE id={$_REQUEST['system']} LIMIT 1 ";

        $this->debug("InsertOrUpdate() Query=" .$q, 3);
        $res = $conn->query($q);
        if ($res === FALSE)
          throw new DatabaseErrorException($conn->error);
      }

      if ($update_mode==TRUE) {
        echo "<p class='UpdateMsgOK'>Update Successful</p>";
        $this->loggui("{$this->module} id=" .$_REQUEST['id'] .", system id=" .$_REQUEST['system'] ." updated");

        // Show follow up instructions/links
        echo "<br><p>Next: make more changes below, or go back to the <a href='{$this->calling_script}'>{$this->module} list</a></p>";

      } else {
        echo "<p class='UpdateMsgOK'>Successful: new {$this->module} with address=$address added</p>";

        // after inserting, locate that record, and show the Update() screen.
        $q = "SELECT address,id from ip where address=INET_ATON('$address') LIMIT 1";
        #$q = "SELECT address,id from ip where address='" .ip2long($address) ."'";
        $this->debug("InsertOrUpdate() $q", 3);
        $res = $conn->query($q);
        if ($res === FALSE)
          throw new DatabaseErrorException($conn->error);
        while (($row = $res->fetch_assoc()) !== NULL) {
          $this->id=$row['id'];
          //echo "<p class='UpdateMsgOK'>Index={$this->id}</p>";
        }
        $this->loggui("new {$this->module}, id={$this->id}, address=$address added");

        // Show follow up instructions/links
        $ref1=$this->calling_script. "?action=Edit&action_idx=$this->id";
        echo "<br><p>Now review/update the <a href='{$ref1}'>{$this->module} details</a> or go back to the <a href='{$this->calling_script}'>{$this->module} list</a></p>";
      }

    } catch (Exception $e) {
      throw $e;
    }
  }



  /**
   * Add or display a record, allow changes.
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
SELECT id,INET_NTOA(address) AS address,subnet,status,comment,system,source,dns_update from ip
  WHERE id='{$this->id}'
  LIMIT 1
TXT;
        $this->debug("edit_record() update_mode=query, $q", 3);
        $res = $conn->query($q);
        if ($res === FALSE)
          throw new DatabaseErrorException($conn->error);

        // Title: Grab the list of field names
        #$fields=$res->fetch_fields();
        #while (($row = $res->fetch_assoc()) !== NULL) {
        $row = $res->fetch_assoc();

      } else { 
        $this->debug("edit_record() update_mode=add, $q", 3);
        // add mode, only show defaults 
        $row['address']='';
        $row['comment']='';   // TBD: Added on YY by XX
        $row['subnet']=0;
        $row['id']=0;
        $row['source']='';
        $row['system']=0;
      }

      # Display the record:
        #$this->debug(var_dump($row), 3);
        $output.= '<tr><td width="87" title="Enter a valid IP address, in the format W.X.Y.Z ">IP Address:</td><td width="400">' ."\n";
        $output.= '<input name="address" type="text" value="' .stripslashes($row['address']) .'" onBlur="checkLen(this,7)"/>' ."\n";
        $output.= '</td><td>Index:' .$row['id'] .'</td>' ."</tr>\n";

        $output.= '<tr><td width="87" title="Status? (1=update DNS, 2=reserved - document only)">Status:</td><td width="400">' ."\n";
        $output.=  $this->get_dstatusdropdown($row['status']) . '</td></tr>'."\n";

        // Subnet
        $output.=  '<tr><td title="Which Subnet does this below to? Used for creating reverse DNS records.">Subnet:</td><td>'."\n";
        $output.=  $this->get_subnetdropdown($row['subnet']) . '</td></tr>'."\n";

        // System
        $output.=  '<tr><td title="What End-Device is this IP address linked to?">End-Device:</td><td>' ."\n";
        $output.=  $this->get_systemdropdown($row['system']) . '</td></tr>'."\n";

        #$output.= '<tr><td width="87" title="Update dns?">Dns update:</td><td width="400">' ."\n";
        #$output.= '<input name="dns_update" type="text" value="' .stripslashes($row['dns_update']) .'"/>' ."\n";

        $output.= '<tr><td width="87" title="Optional: Aliases (space or comma separated). Changing this value means that the alias field of the above end-device is updated.">Aliases:</td><td width="400">' ."\n";
        $output.= '<input name="aliases" type="text" value="' .$this->get_systemaliases($row['system']) .'"/>' ."\n";

        $output.= '<tr><td width="87" title="Optional Comment?">Comment:</td><td width="400">' ."\n";
        $output.= '<input name="comment" type="text" value="' .stripslashes($row['comment']) .'"/>' ."\n";

        //$output.= '<tr><td width="87" title="Optional: source of imported data">IP Source:</td><td width="400">' ."\n";
        //$output.= '<input name="source" type="text" value="' .stripslashes($row['source']) .'"/>' ."\n";

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



function get_dstatusdropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="select value from dstatus where id='$s'";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['value'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     $ret='<select name="status">';

     $q='SELECT id, value FROM dstatus ORDER BY value ASC';
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


function get_systemaliases($s)       // Lookup current aliases
{
   $ret='';
   $conn=$this->getConnection();     //  make sure we have a DB connection
   $q="select dns_alias from systems where id='$s'";
     $this->debug("Query=" .$q, 3);
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['dns_alias'];
     }
   return $ret;
}

function get_systemdropdown($s)       // Show a list of Dend-Devices
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
