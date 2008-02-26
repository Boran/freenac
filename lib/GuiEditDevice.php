<?php
/**
 * 
 * GuiEditDevice.php
 *
 * Long description for file:
 * Class to Display a generic Query, with sorting and active buttons
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiEditDevice extends WebCommon
{
  private $id;      // See also WebCommon and Common


  function __construct($id=0)
  {
    parent::__construct();     // See also WebCommon and Common
    $this->logger->setDebugLevel(3);
    $this->id=$id;
    
    $this->debug($_SESSION['login_data'] .":Id=$id:" , 1);

    // Show Webpage start, is the constructor the right place?
    echo $this->print_header();

    # TBD: align='centre' does not work
    $txt=<<<TXT
<div style='text-align: centre;' class='text18'>
  <p>Edit End-Device Details
</div><br/>
TXT;
    echo $txt;
  }



  /**
   * Generic query report
   */
  public function query()
  {
    $conn=$this->getConnection();     //  make sure we have a DB connection
    $output ='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    $output.="<table id='t3' width='760' border='0' class='text13'>";
    $q=<<<TXT
SELECT
  sys.mac AS 'MAC Addr.',
  sys.name as Systemname, sys.vlan,
  vlan.default_name as VlanName, lvlan.default_name as LastVlan,
  vlan.vlan_group as VlanGroup, status.value as Status,
  usr.username as Username,
  sys.inventory, sys.description, sys.comment, sys.changedate,
  cusr.username as ChangeUser,
  sys.lastseen,
  b.name as building, loc.name as office,
  p.name as port, pcloc.name as PortLocation, p.comment as PortComment, p.last_activity as PortLastActivity,
  swi.ip as SwitchIP, swi.name as Switch, swloc.name as SwitchLocation,
  pc.outlet as PatchCableOutlet, pc.comment as PatchCableComment,
  sys.history,
  usr.surname, usr.givenname, usr.department, usr.rfc822mailbox as EMail,
  usrloc.name as UserLocation, usr.telephonenumber as UserTelephone, usr.mobile,
  usr.lastseendirectory as UserLastSeenDirectory,
  sos.value as OSName, sos1.value as OS1, sos2.value as OS2, sos3.value as OS3,
  sys.os4 as OS4,
  sys.class, sclass.value as ClassName, sys.class2, sclass2.value as ClassName2,
  sys.scannow, sys.r_ip, sys.r_timestamp, sys.r_ping_timestamp
  FROM systems as sys LEFT JOIN vlan as vlan ON vlan.id=sys.vlan
      LEFT JOIN vlan as lvlan ON lvlan.id=sys.lastvlan
      LEFT JOIN vstatus as status ON status.id=status
      LEFT JOIN users as usr ON usr.id=sys.uid
      LEFT JOIN users as cusr ON cusr.id=sys.changeuser
      LEFT JOIN location as loc ON loc.id=sys.office
      LEFT JOIN building as b ON b.id=loc.building_id
      LEFT JOIN port as p ON p.id=sys.lastport
      LEFT JOIN patchcable as pc ON pc.port=p.id
      LEFT JOIN location as pcloc ON pcloc.id=pc.office
      LEFT JOIN switch as swi ON swi.id=p.switch
      LEFT JOIN location as swloc ON swloc.id=swi.location
      LEFT JOIN location as usrloc ON usrloc.id=usr.location
      LEFT JOIN sys_os as sos ON sos.id=sys.os
      LEFT JOIN sys_os1 as sos1 ON sos1.id=sys.os1
      LEFT JOIN sys_os2 as sos2 ON sos2.id=sys.os2
      LEFT JOIN sys_os3 as sos3 ON sos3.id=sys.os3
      LEFT JOIN sys_class as sclass ON sclass.id=sys.class
      LEFT JOIN sys_class2 as sclass2 ON sclass2.id=sys.class2
  WHERE sys.id=$this->id
  LIMIT 1
TXT;

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
      while (($row = $res->fetch_assoc()) !== NULL) {
      #  foreach ($fields as $field) {
      #    $fname=$field->name;
      #    $output.="<tr><td>$fname</td><td>{$row[$fname]}</td></tr>";
      #  }
        // Name
        $output.= '<tr><td width="87">Name:</td><td width="400">' ."\n";
        $output.= '<input name="name" type="text" value="' .stripslashes($row['name']) .'"/>' ."\n";
        $output.= '</td></tr>'."\n";
        // MAC
        $output.= '<tr><td>MAC:</td><td>'."\n";
        $output.= $row['mac'] .(!is_null($row['vendor'])?' (' .$row['vendor'] .')':'') ."\n";
        $output.= '</td></tr>'."\n";
        $output.= '<input type="hidden" name="mac" value="'.$row['mac'].'" />'."\n";
        // Status
        $output.=  '<tr><td>Status:</td><td>'."\n";
        $output.=  $this->get_statusdropdown($row['status']) . '</td></tr>'."\n";
        // VLAN, last vlan/date
        $output.= "<tr><td>VLAN: </td><td>\n"
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
        if ($this->conf->web_showdns) {      //TBD: $conf is global, but we cannot it read it yet
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
        $output.= '<tr><td>&nbsp;</td><td>' ."\n"
           . '<input type="submit" name="action" value="Update" />' .'&nbsp;'
           . '<input type="submit" name="action" value="Delete" />' ."\n"
           . '</td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";
        $output.= '<tr><td>&nbsp;</td><td></td></tr>' ."\n";

      }
      // close the table
      $output.= '</table> ';

      include('EditDevice_more.inc.php');    // needs cleaning up: more read-only stuff

      $output.= '<!input type="hidden" name="action" value="update" />'
        . '<input type="hidden" name="id" value="' .$row['id'] .'" /></form>';


    } catch (Exception $e) {
      #if ($in_db_conn === NULL and isset($conn))
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    return($output);
  }                               // function


function get_vlandropdown($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection

   if ($_SESSION['nac_rights'] == 1) {   // read-only
     $q="select default_name from vlan where id='$s'";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['default_name'];
     }
   }

   else if ($_SESSION['nac_rights'] ==2 ) {   // edit: user list
     $ret='<select name="vlan">';
     $q="select id, default_name from vlan ";

     // if GuiVlanRights is set, only show those Vlans
         if ( ! empty($_SESSION['GuiVlanRights']) ) {
           $vlans_to_show = explode(',',$$_SESSION['GuiVlanRights']);
           $number_vlans = count($vlans_to_show) - 1;
          
           if ( $number_vlans == 0 ) {
             echo "<option value=\"\">No vlans defined</option>";
             $sql .= "id='';";
           }
           else {
             for ($i = 0; $i < $number_vlans; $i++) {
               if ( $i < ($number_vlans - 1) )
                 $sql .= "id = '{$vlans_to_show[$i]}' OR ";
               else
                 $sql .= "id = '{$vlans_to_show[$i]}'";
             }
           }

          $q.=" ORDER BY default_name";
        }

     // run the quers, make the list
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);
     while (($row = $res->fetch_assoc()) !== NULL) {
       $ret.='<option value="' .$row['default_name'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
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
       $ret.='<option value="' .$row['default_name'].'" '
            .($s==$row['id'] ? 'selected="selected"' : '')
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



///////////////////////////////////////////////////////////////////
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
