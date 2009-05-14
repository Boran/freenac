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
  private $id, $action;      // See also WebCommon and Common


  function __construct($action, $id=0, $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("GuiEditDevice__construct id=$id, debug=$debug_level, action=$action", 2);

    // 1. verify/clean 'id'
    #if ( !is_int($id) )     // must be a number
    if ( !is_numeric($id) )     // must be a number
       throw new InvalidWebInputException("invalid index: <$id> is not an integer");
    //if ( $id===0 )              
    //   throw new InvalidWebInputException(""GuiEditDevice__construct invalid index: zero");

    #if (isset($_REQUEST['action_idx'])) $logger->debug("action_idx=" .$_REQUEST['action_idx'], 2);
    $this->id=$id;                   // remember the record number
    $_SESSION['report1_index']=$id;  // for passing to other scripts: no longer used?
    
    // 2. verify/clean 'action'
    // Now, have we a REQUEST action to carry out?
    if ( !isset($action) ) {
       throw new InvalidWebInputException("No action ");
    }
    $this->action=validate_webinput($action);

  }


  public function print_title($title)
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
        $this->print_title('Update End-Device Details');
        #$logger->debug("action=$action, report1_index=" .$_SESSION['report1_index'], 1);
        echo $this->Update();
        echo $this->query();
        echo $this->print_footer();

      } else if ($action==='Edit') {
        $this->print_title('Edit End-Device Details');
        echo $this->query();
        echo $this->print_footer();

       
      } else if ($action==='Add') {
        if (isset($_REQUEST['name']) && isset($_REQUEST['mac']) ) {
          $this->print_title('New End-Device');  // Add step2
          echo $this->UpdateNew();
        } else {        // Add Step1
          $this->print_title('Add new End-Device');
          echo $this->Add();
        }
        echo $this->print_footer();
       
      } else if ($action==='Delete') {
        $this->print_title('Edit End-Device Details');
        $this->Delete();
       
      } else if ($action==='Restart Port') {
        $this->print_title('Restart port request');
        $this->RestartPort();
        echo $this->print_footer();
       
      } else {
        // do nothing, action does not concern us.
      }
    }
  }


  public function RestartPort()
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']); if ( $this->id===0 )              
      throw new InvalidWebInputException("RestartPort() invalid index: zero");

    $conn=$this->getConnection(); //  make sure we have a DB connection
    $device=$this->id;    // rely on the constructor to clean & ensure a valid id
    $port_index=0;
    $this->debug("RestartPort() sys device index {$device}", 2);
    $txt='';

    // find the port for this System/mac
      $q="SELECT LastPort, sw.switch_type  FROM systems s INNER JOIN port p ON s.LastPort=p.id INNER JOIN switch sw on p.switch=sw.id WHERE s.id={$device} LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);
      while (($row = $res->fetch_assoc()) !== NULL) {
        $port_index=$row['LastPort'];
        $switch_type=$row['switch_type'];
      }
    
    $this->debug("LastPort=" .$port_index .", switch_type=" .$switch_type .", check_clear_mac=" .$this->conf->check_clear_mac, 2);

    if (($switch_type==="1") && ($this->conf->check_clear_mac)) { // use the clear_mac and not port restart function

      if ( $this->clear_mac_request($device) ) {
        $txt.=" <p class='UpdateMsgOK'>The MAC address will be be cleared from the IOS Switch.</p> ";
      } else  {
        $txt.=" <p class='UpdateMsg'>The MAC cannot be cleared since the device ID is invalid.</p> <p>Please go <a HREF='javascript:javascript:history.go(-1)'>back to the previous screen</a>,  ";
      }


    }      // if clear_mac
    //} else  {
      // Lets do a port restart: 

    if ( $this->port_restart_request($port_index) ) {
      // Add later:<a href=/port.php?action=View&action_idxname=port.id&action_fieldname=PortIndex&action_idx=${port_index}>
      $txt.=" <p class='UpdateMsgOK'>The Switch Port number ${port_index}</a> will be restarted within one minute</p> <p>To followup, <a href='logserver.php'>view the serverlog</a> for a confirmation, or go <a HREF='javascript:javascript:history.go(-1)'>back to the previous screen</a>,  ";
    } else {
      $txt.=" <p class='UpdateMsg'>The Switch Port cannot be restarted as it is invalid.</p> <p>Please go <a HREF='javascript:javascript:history.go(-1)'>back to the previous screen</a>, ";
    }
    $txt.='</div>';

    //}      // if clear_mac
    echo $txt;
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

    $q="DELETE FROM systems WHERE id={$device} LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);

      // Inform the user that is was OK
      $txt=<<<TXT
<p class='UpdateMsgOK'>Delete Successful</p>
 <br><p > Go back to the <a href="{$_SESSION['caller']}">End-Device list</a></p>
</div>
TXT;
      echo $txt;
      $this->logit("Deleted system with Index {$device}");
      $this->loggui("Deleted system with Index {$device}");

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
      // TBD: call validate_input?
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
   * Update a record: read fields/data from the POST, generate SQL and execute
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
    if ( !is_numeric($_REQUEST['action_idx']) || $_REQUEST['action_idx']==0)     // must be a number>0
       throw new InvalidWebInputException("invalid index: is not an integer");

    $this->id=$_REQUEST['action_idx'];

    try {
      $q='';
        $q='UPDATE systems SET ';
        // got name?
        $q.=($_REQUEST['name']!='' ? 'name=\'' .$_REQUEST['name'] .'\' ' : '');
        // status, user, office, comment
        $q.=', status='.$_REQUEST['status'];
        $q.=($_REQUEST['username']!='' ? ', uid='.$_REQUEST['username'].' ' : '');
        $q.=($_REQUEST['office']!='' ? ', office='.$_REQUEST['office'].'' : '');
        $q.=($_REQUEST['sys_class']!='' ? ', class=\''.$_REQUEST['sys_class'].'\'' : '');
        $q.=($_REQUEST['sys_class2']!='' ? ', class2=\''.$_REQUEST['sys_class2'].'\'' : '');
        # allow empty values
        $q.=(', expiry=\''.$_REQUEST['expiry'].'\'');   
        $q.=(', email_on_connect=\''.$_REQUEST['email_on_connect'].'\'');   
        $q.=(', inventory=\''.$_REQUEST['inventory'].'\'');   
        $q.=(', comment=\''.$_REQUEST['comment'].'\'');   

        // TBD: DNS Alias & DHCP
        if ($this->conf->web_showdns) {
          // TODO : validate DNS aliases
          $q.=", dns_alias='".$_REQUEST['dns_alias']."'";
        }
        if ($this->conf->web_showdhcp) {
          // TODO : validate dhcp_ip as ip address
          if (($_REQUEST['dhcp_fix'] == 'dhcp_fix') && ($_REQUEST['dhcp_ip'] != '')) {
            $q.=", dhcp_fix=1, dhcp_ip='".$_REQUEST['dhcp_ip']."'";
          }
        }

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
      #$this->logit($q);
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
   * Add a new device: Query a record and display on the WebGUI
   */
  public function add()
  {
    global $js1;
    if ($_SESSION['nac_rights']<2)    // must have edit rights
      throw new InsufficientRightsException($_SESSION['nac_rights']);

    $conn=$this->getConnection();     //  make sure we have a DB connection
    $this->debug("EditDevice::Add() ", 3);
    #$output ='<form name="formadd" action="GuiEditDevice_control.php" method="POST">';
    $output ='<form name="formadd" action="' .$_SERVER['PHP_SELF'] .'" method="POST">';
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
   * Next Step is Either Update, Delete, or Restart Port
   */
  public function query()
  {
    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection
    #$output ='<form action="GuiEditDevice_control.php" method="POST">';
    $output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    #$output ='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'; /i/debugging
    $output.="<table id='t3' width='760' border='0' class='text13'>";

$q=<<<TXT
SELECT sys.id, sys.name, sys.mac, sys.status, sys.vlan, lvlan.default_name as lastvlan, sys.uid as user, sys.office, port.name as port, sys.lastseen, swloc.name as location, swi.name as switch, sys.r_ip as lastip, sys.r_timestamp as lastipseen, sys.comment, eth.vendor, dns_alias, dhcp_fix, dhcp_ip, inventory, class, class2, email_on_connect, expiry
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

        $output.=<<<TXT
         <tr><td>Switch:</td>
           <td>{$row['switch']}, port= {$row['port']}, location= {$row['location']} </td>
           <td><input type="submit" name="action" class="bluebox" value="Restart Port" /> </td>
         </tr> 
TXT;
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


        $output.= '<tr><td>Inventory:</td><td>'."\n"
          . '<input name="inventory" type="text" size=40 value="' .stripslashes($row['inventory']) .'"/>' ."\n"
          . '</td>' ."\n";
        $output.= '<tr><td>Classification1:</td><td>' ."\n"
          . $this->get_class1dropdown($row['class'], 'sys_class')
          . '</td></tr>' ."\n";
        $output.= '<tr><td>Classification2:</td><td>' ."\n"
          . $this->get_class1dropdown($row['class2'], 'sys_class2')
          . '</td></tr>' ."\n";
        $output.= '<tr><td>On connect, send Email to:</td><td>'."\n"
          . '<input name="email_on_connect" type="text" size=40 value="' .stripslashes($row['email_on_connect']) .'"/>' ."\n"
          . '</td>' ."\n";
        $output.= '<tr><td>Expiry (block access from this date):</td><td>'."\n"
          . '<input name="expiry" type="text" size=40 value="' .stripslashes($row['expiry']) .'"/>' ."\n"
          . '</td>' ."\n";

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


function get_vlandropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] < 2 ) {   // read-only
     // show nothing
   }

   else if ($_SESSION['nac_rights'] ==2 ) {   // edit: user list
     $ret='<select name="vlan">';
     $q="select id, default_name from vlan ";

     // if GuiVlanRights is set, only show those Vlans
         if ( ! empty($_SESSION['GuiVlanRights']) ) {
           $vlans_to_show = explode(',', $_SESSION['GuiVlanRights']);
           #$number_vlans = count($vlans_to_show) - 1;
           $number_vlans = count($vlans_to_show);
           $this->debug("get_vlandropdown: limit to $number_vlans vlans: " .$_SESSION['GuiVlanRights'], 3);
          
           if ( $number_vlans == 0 ) {
             echo "<option value=\"\">No vlans defined</option>";
             $q .= " WHERE id='';";
           }
           else {
             for ($i = 0; $i < $number_vlans; $i++) {
                 if ( $i == 0 )
                    $q .=" WHERE ";
                 if ( $i < ($number_vlans - 1) )
                    $q .= " id = '{$vlans_to_show[$i]}' OR ";
                 else
                    $q .= " id = '{$vlans_to_show[$i]}'";
               }
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

/**
 * Generic Drop down list: for the table $table, find the entry $id.
 *                 in read-only mode show just that entry, else show a list and
 *                 allow it to be changed. Send the result back in a SUBMIT with the 
 *                 name in  $table.
 */
function get_class1dropdown($s='1', $table="sys_class")
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="SELECT id, value as displayname FROM $table WHERE id='$s'";
     $res = $conn->query($q);  $this->debug($q ,3);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['displayname'];
     }
   }
   else if ($_SESSION['nac_rights'] > 1) {   // edit/admin
     $ret="<select name='${table}'>";
     $q="SELECT id, value as displayname FROM ${table} ORDER BY value"; // Get details for all rows
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
