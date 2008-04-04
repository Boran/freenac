<?php
/**
 * 
 * GuiEditDevice.php
 *
 * Long description for file:
 * Allow End-Device records to edited, deleted or inserted.
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


class GuiEditDevice extends WebCommon
{
  private $id;      // See also WebCommon and Common


  function __construct($id=0, $rep_name='Edit End-Device Details')
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel(1);

    //if ( ($id===0) || (!is_numeric($id)) ) 
    if ( (!is_numeric($id)) )     // allow 0 as a default, must be a number though
       throw new InvalidWebInputException("invalid record index");

    $this->id=$id;                   // remember the record number
    $_SESSION['report1_index']=$id;  // for passing to other scripts
    
    //$this->debug($_SESSION['login_data'] .":Id=$id:" , 1);

    // Show Webpage start, is the constructor the right place?
    echo $this->print_header(false);
    echo "<div id='GuiList1Title'>{$rep_name}</div>";
  }



  public function Delete()
  {
    $this->debug("Delete() index {$device}", 3);
    #var_dump($_REQUEST);
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);

    if (is_numeric($_REQUEST['action_idx']) && ($_REQUEST['action_idx']>0) ){
      $device=$_REQUEST['action_idx'];
    } else if (is_numeric($_SESSION['report1_index']) && ($_SESSION['report1_index']>0) ) {
      $device=$_SESSION['report1_index'];
    } else {
      throw new InvalidWebInputShowException("Cannot delete device with invalid index <{$_REQUEST['action_idx']}>");
    }

    $conn=$this->getConnection();     //  make sure we have a DB connection
    try {
      $q="DELETE FROM systems WHERE id={$device} LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);

      // Inform the user that is was OK
      //define('HEADER',false); // The header is out
      #echo $this->print_header();
      $txt=<<<TXT
<p class='UpdateMsgOK'>Delete Successful</p>
 <br><p > Go back to the <a href="{$_SESSION['caller']}">End-Device list</a></p>
</div>
TXT;
      echo $txt;
      $this->logit("Deleted system with Index {$device}");
      $this->loggui("Deleted system with Index {$device}");

    } catch (Exception $e) {
      throw $e;
    }
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

    #echo "<p class='UpdateMsg'>Insert Pending</p>";
    #$this->id

    try {
      // Read in request variables. Mac and name are set, others are optional
      $name=trim($_REQUEST['name']);            // get rid of leading/trailing spaces
      $mac=strtolower($_REQUEST['mac']);        // lower case by convention
      $mac=$this->sqlescape($mac);     		// TBD: verify syntax/length etc.
      $name=$this->sqlescape($name);   		
      $q="INSERT INTO systems SET mac='$mac', name='$name' ";

     if ( isset($_REQUEST['comment']) )
        $q.=", comment='" .$this->sqlescape($_REQUEST['comment']) ."'";
     if (( isset($_REQUEST['status']) ) && is_numeric ($_REQUEST['status']) )
        $q.=", status="  .$_REQUEST['status'] ;


     if ( ( isset($_REQUEST['vlan']) ) && is_numeric ($_REQUEST['vlan']) ){  // re-verify vlan assignment right
        // Restrict vlan for superusers?
        if ( !empty($_SESSION['GuiVlanRights']) && ($_SESSION['nac_rights']==2)) {
          $this->debug("Web user {$_SESSION['uid']} has restricted vlans: {$_SESSION['nac_rights']}", 1);
          $vlans_allowed = explode(',', $_SESSION['GuiVlanRights']);
          if (array_search($_REQUEST['vlan'], $vlans_allowed) ) {
             $q.=', vlan='  .$_REQUEST['vlan'];
          }
          else {
             $this->logger->logit("Web user {$_SESSION['uid']} is not allowed to assign vlan {$_REQUEST['vlan']} only {$_SESSION['GuiVlanRights']}");
          }
        }
        else {    // no restrictions
          $this->debug("Web user {$_SESSION['uid']} is allowed to assign any vlan:  vlan idx="  .$_REQUEST['vlan'], 3);
          $q.=', vlan='  .$_REQUEST['vlan'];
        }
     }

      $this->debug("UpdateNew() $q", 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseInsertException($conn->error);

      echo "<p class='UpdateMsgOK'>Successful: new end-device $name/$mac added</p>";
      #echo "<p class='UpdateMsgOK'>Now view/update the end-device details</p>";

      // after inserting, locate that record, and show the Update() screen.
      $res = $conn->query("SELECT id,name from systems where mac='" .$mac ."'");
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);
      while (($row = $res->fetch_assoc()) !== NULL) {
        $this->id=$row['id'];
      }
      $_SESSION['report1_index']=$this->id;  // for passing to other scripts

      $this->loggui("new end-device $name, mac=$mac, index=$this->id added");

      // locate that record, and show the Update() screen.
      $ref=$this->calling_script. "?action=Edit&action_idx=$this->id";
      #echo $ref;
      #$this->debug($ref); 
      echo "<p class='UpdateMsgOK'>Now review/update the <a href='$ref'>end-device details</a></p>";

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
    #echo "<p class='UpdateMsg'>Update Pending</p>";
    #var_dump($_REQUEST);
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);

    $conn=$this->getConnection();     //  make sure we have a DB connection

    try {
      $q='';
        $q='UPDATE systems SET ';
        // got name?
        $q.=($_REQUEST['name']!='' ? 'name=\'' .$_REQUEST['name'] .'\' ' : '');
        // status, user, office, comment
        $q.=', status='.$_REQUEST['status'];
        $q.=($_REQUEST['username']!='' ? ', uid='.$_REQUEST['username'].' ' : '');
        $q.=($_REQUEST['office']!='' ? ', office='.$_REQUEST['office'].'' : '');
        $q.=($_REQUEST['comment']!='' ? ', comment=\''.$_REQUEST['comment'].'\'' : '');
               /*    // TBD: DNS Alias & DHCP
                   if ($conf->web_showdns) {
                        // TODO : validate DNS aliases
                        $q.=", dns_alias='".$_REQUEST['dns_alias']."'";
                   };
                        // TODO : validate dhcp_ip as ip address
                   if ($conf->web_showdhcp) {
                        if (($_REQUEST['dhcp_fix'] == 'dhcp_fix') && ($_REQUEST['dhcp_ip'] != '')) {
                                 $q.=", dhcp_fix=1, dhcp_ip='".$_REQUEST['dhcp_ip']."'";
                        };
                   }; */

        // Restrict vlan for superusers?
        if ( !empty($_SESSION['GuiVlanRights']) && ($_SESSION['nac_rights']==2)) {
          $this->debug("Web user {$_SESSION['uid']} has restricted vlans: {$_SESSION['nac_rights']}", 1);
          $vlans_allowed = explode(',', $_SESSION['GuiVlanRights']);
          if (array_search($_REQUEST['vlan'], $vlans_allowed) ) {
             $q.=', vlan='  .$_REQUEST['vlan'];
          }
          else {
             $this->logger->logit("Web user {$_SESSION['uid']} is not allowed to assign vlan {$_REQUEST['vlan']}");
          }
        }
        else {    // no restrictions
          $this->debug("Web user {$_SESSION['uid']} is allowed to assign any vlan", 2);
          $q.=', vlan='  .$_REQUEST['vlan'];
        }

        // Log who made the change, when: 
        if (is_numeric($_SESSION['uid'])) $q.=", changeuser={$_SESSION['uid']}";
        $q.=", changedate=NOW()";
        $q.=" WHERE id={$this->id} LIMIT 1";     // only this record

      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      echo "<p class='UpdateMsgOK'>Update Successful</p>";
      $this->loggui("end-device " .$_REQUEST['name'] ."/" .$_REQUEST['mac'] ." updated");

    } catch (Exception $e) {
      throw $e;
    }
  }


  /**
   * Add a new device
   */
  public function add()
  {
    global $js1;
    if ($_SESSION['nac_rights']<2)    // must have edit rights
      throw new InsufficientRightsException($_SESSION['nac_rights']);

    $conn=$this->getConnection();     //  make sure we have a DB connection
    $this->debug("EditDevice::Add() ", 3);
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    $output ='<form name="formadd" action="GuiEditDevice_control.php" method="POST">';
    $output.= "\n$js1\n <table id='GuiEditDeviceAdd'>";

    $name=''; $mac='0001.0001.0001'; 
    try {

        // Name, MAC
        $output.=<<<TXT
        <tr><td width="87" title="What name is to be used by FreeNAC to reference this device?">Name:</td>
            <td width="400"> <input name="name" type="text" value="{$name}" onBlur="checkLen(this,1)">
        </td></tr>
        <tr><td width="87"  title="Enter a valid 12 digit hex MAC address, in the format xxxx.yyyy.zzzz ">MAC:</td>
            <td width="400"> <input name="mac" type="text" value="{$mac}" onBlur="checkLen(this,14) ">
        </td></tr>
TXT;
        // Status
        $output.=  '<tr><td title="Is the new device to be allowed on the network">Status:</td><td>'."\n";
        $output.=  $this->get_statusdropdown(1) . '</td></tr>'."\n";
        // VLAN, last vlan/date
        $output.= "<tr><td title='What vlan to be assigned to this device?'>VLAN: </td><td>\n"
          . $this->get_vlandropdown($this->conf->default_vlan) . '</td>';

      $output.=<<<TXT
        <tr><td></td><td>
        <input type="submit" class="bluebox" name="action" value="Add" onclick="return checkForm()"
		title="Click to add a new device with the above details"/>
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
   * Display a device record, allow changes.
   */
  public function query()
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    $output ='<form action="GuiEditDevice_control.php" method="POST">';
    $output.="<table id='t3' width='760' border='0' class='text13'>";

$q=<<<TXT
SELECT sys.id, sys.name, sys.mac, sys.status, sys.vlan, lvlan.default_name as lastvlan, sys.uid as user, sys.office, port.name as port, sys.lastseen, swloc.name as location, swi.name as switch, sys.r_ip as lastip, sys.r_timestamp as lastipseen, sys.comment, eth.vendor, dns_alias, dhcp_fix, dhcp_ip
                        FROM systems as sys LEFT JOIN vlan as lvlan ON sys.lastvlan=lvlan.id LEFT JOIN port as port ON port.id=sys.lastport LEFT JOIN switch as swi ON port.switch=swi.id LEFT JOIN location as swloc ON swloc.id=swi.location LEFT JOIN ethernet as eth ON (SUBSTR(sys.mac,1,4)=SUBSTR(eth.mac,1,4) AND SUBSTR(sys.mac,6,2)=SUBSTR(eth.mac,5,2))
  WHERE sys.id=$this->id
  LIMIT 1
TXT;

    try {
      $this->debug("EditDevice::query() $q", 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      // Title: Grab the list of field names
      $fields=$res->fetch_fields();
      #  foreach ($fields as $field) {
      #    $fname=$field->name;
      #    $output.="<tr><td>$fname</td><td>{$row[$fname]}</td></tr>";
      #  }
      while (($row = $res->fetch_assoc()) !== NULL) {
        #$this->debug(var_dump($row), 3);
        // Name
        $output.= '<tr><td width="87">Name:</td><td width="400">' ."\n";
        $output.= '<input name="name" type="text" value="' .stripslashes($row['name']) .'"/>' ."\n";
        $output.= '</td><td>Index:' .$row['id'] .'</td>' ."</tr>\n";
        // MAC
        $output.= '<tr><td>MAC:</td><td>' ."\n";
        $output.= $row['mac'] .(!is_null($row['vendor'])?' (' .$row['vendor'] .')':'') ."\n";
        $output.= '</td></tr>'."\n";
        $output.= '<input type="hidden" name="mac" value="' .$row['mac'] .'" />' ."\n";
        // Status
        $output.=  '<tr><td>Status:</td><td>' ."\n";
        $output.=  $this->get_statusdropdown($row['status']) . '</td></tr>'."\n";

        // VLAN, last vlan/date
        $output.= "<tr><td>VLAN: </td><td>\n"
          . $this->get_vlan($row['vlan']) ."  "
          . $this->get_vlandropdown($row['vlan']) . '</td> <td>Last VLAN:'
          . (is_null($row['lastvlan']) ? 'NONE' : $row['lastvlan'])
          .  '<br>' .$row['lastseen'] .'</td>' ."\n";

        // User, location, switch, comment, last IP/date
        $output.= '<tr><td>User:</td><td>' ."\n"
          . $this->get_userdropdown($row['user'])
          . '</td></tr>' ."\n";
        $output.= '<tr><td>Location:</td><td>' ."\n"
          . $this->get_officedropdown($row['office'])
          . '</td></tr>' ."\n";
        $output.= '<tr><td>Switch:</td><td>'."\n"
          . $row['switch'].', port= '.$row['port'].', location= '.$row['location'] ."\n"
          . '</td></tr>' ."\n";
        $output.= '<tr><td>Comment:</td><td>'."\n"
          . '<input name="comment" type="text" size=40 value="' .stripslashes($row['comment']) .'"/>' ."\n"
          . '</td><td>Last IP:' .(is_null($row['lastip']) ? 'NONE' : $row['lastip'])
          .  '<br>' .$row['lastipseen'] .'</td>' ."\n";

        // DNS, HCP Fix IP
        if ($this->conf->web_showdns) {     
          $output.= '<tr><td>DNS Alias(es):</td><td>' ."\n"
            .  '<input name="dns_alias" type="text" value="' .stripslashes($row['dns_alias']) .'"/>' ."\n"
            . '</td></tr>' ."\n";
        };
        if ($this->conf->web_showdhcp) {
          $output.= '<tr><td>Fixed DHCP IP assignment:</td><td>' ."\n";
          if ($row['dhcp_fix'] == 1) {
            $output. '<input name="dhcp_fix" type=checkbox value="dhcp_fix" checked> to ';
          } else {
            $output. '<input name="dhcp_fix" type=checkbox value="dhcp_fix"> to ';
          }
          $output. '<input name="dhcp_ip" type=text value="' .$row['dhcp_ip'] .'">';
        };

         // Submit
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.=<<<TXT
          <tr><td>&nbsp;</td><td>
          <input type="submit" name="action" class="bluebox" value="Update" />&nbsp;
          <input type="submit" name="action" class="bluebox" value="Delete" 
            onClick="javascript:return confirm('Really DELETE this end-device record?')"
            />
          </td></tr>'
TXT;
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";

      }
      // close the table
      $output.= '</table> ';

      include('EditDevice_more.inc.php');    // needs cleaning up: more read-only stuff

      $output.= '<!input type="hidden" name="action" value="update" />'
        . '<input type="hidden" name="id" value="' .$row['id'] .'" /></form>';


    } catch (Exception $e) {
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    return($output);
  }                               // function


function get_vlandropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] < 2 ) {   // read-only
     // show nothing
   }

   else if ($_SESSION['nac_rights'] ==2 ) {   // edit: user list
     $ret='<select name="vlan">';
     $q="select id, default_name from vlan WHERE ";

     // if GuiVlanRights is set, only show those Vlans
         if ( ! empty($_SESSION['GuiVlanRights']) ) {
           $vlans_to_show = explode(',', $_SESSION['GuiVlanRights']);
           #$number_vlans = count($vlans_to_show) - 1;
           $number_vlans = count($vlans_to_show);
           $this->debug("get_vlandropdown: limit to $number_vlans vlans: " .$_SESSION['GuiVlanRights'], 3);
          
           if ( $number_vlans == 0 ) {
             echo "<option value=\"\">No vlans defined</option>";
             $q .= "id='';";
           }
           else {
             for ($i = 0; $i < $number_vlans; $i++) {
               if ( $i < ($number_vlans - 1) )
                 $q .= "id = '{$vlans_to_show[$i]}' OR ";
               else
                 $q .= "id = '{$vlans_to_show[$i]}'";
             }
           }

          $q.=" ORDER BY default_name";
        }

     // run the query, make the list
     $this->debug("get_vlandropdown: " .$q, 3);
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option ' .($s==$row['id'] ? ' selected' : '')
            .' value="' .$row['id'].'" '
            .'>' .$row['default_name'] .'</option>' ."\n";

     }
     $ret.="</select> \n";
   }

   else if ($_SESSION['nac_rights'] > 2 ) {   // admin: all vlans
     $ret='<select name="vlan">';
     $q="select id, default_name from vlan ORDER BY default_name";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option ' .($s==$row['id'] ? ' selected' : '')
            .' value="' .$row['id'].'" '
            .'>' .$row['default_name'] .'</option>' ."\n";
     }
     $ret.="</select> \n";
   }

   return $ret;
}



function get_officedropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q='SELECT loc.id, loc.name as office, b.name as building FROM location as loc LEFT JOIN building as b on loc.building_id=b.id '. "WHERE id='$s'"; 
     $res = $conn->query($q);  $this->debug($q ,3);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['building'] .' - ' .$row['office'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     $ret='<select name="office">';
     $q='SELECT loc.id, loc.name as office, b.name as building FROM location as loc LEFT JOIN building as b on loc.building_id=b.id ORDER BY building, office'; 
     $res = $conn->query($q);  $this->debug($q ,3);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['id'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
            .'>' .$row['building'] .' - ' .$row['office'] .'</option>' ."\n";
     }
     $ret.="</select> \n";
   }
   return $ret;
}



function get_userdropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="SELECT id, username, CONCAT(surname,\' \',givenname,\', \',department) as displayname FROM users WHERE id='$s'";
     $res = $conn->query($q);  $this->debug($q ,3);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['displayname'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin 
     $ret='<select name="username">';
     $q='SELECT id, username, CONCAT(surname,\' \',givenname,\', \',department) as displayname FROM users ORDER BY surname'; // Get details for all users
     $res = $conn->query($q);  $this->debug($q ,3);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['id'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
            .'>' .$row['displayname'] .'</option>' ."\n";
     }
     $ret.="</select> \n";
   }
   return $ret;
}



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

function get_nmap_id($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection
   $ret=FALSE;
   $q="SELECT id FROM nac_hostscanned WHERE sid='$s'";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['id'];
     }
   return $ret;
}

function get_nmap_os($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection
   $ret=FALSE;
   $q="SELECT os FROM nac_hostscanned WHERE id='$s'";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['os'];
     }
   return $ret;
}



} // class



/////////// main() should never get here .. ///////////////////////////////////////
if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger=Logger::getInstance();
  $logger->debug("EditDevice:action:". $_POST['action'], 1);
}

if ( isset($_POST['submit']) ) {             // form submit, check fields
## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);
  $logger->debug("EditDevice main -submit");
  #echo handle_submit();

} else {    
  # Do nothing, we've been included.
}

?>
