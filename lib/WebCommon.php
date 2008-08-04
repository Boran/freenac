<?php
/**
 *
 * WebCommon.php
 *
 * Long description for file:
 * Class of common objects/functions for the WebGUI
 * see also the 'Common' Class which this extends
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id
 * @link        http://freenac.net
 *
 */
class WebCommon extends Common 
{
  // see also Variables inherited from Common
  protected $calling_script, $calling_href, $module, $table;   

  function __construct($show_header=true, $debuglevel=1)
  {
    parent::__construct();
    $this->calling_script=basename($_SERVER['SCRIPT_FILENAME']);   // TBD: clean unneeded, trust apache?
    if (isset($_SESSION['caller'])) {
      $this->calling_href=$_SESSION['caller'];    // TBD: needed? same as calling_script?
    }
    $this->module='WebCommon';
    $this->logger->setDebugLevel($debuglevel);  // 3 for max debugging
    $this->remote_host=validate_webinput($_SERVER['REMOTE_ADDR']);

    if (! function_exists('mysqli_connect')) {
      throw new DatabaseErrorException("PHP has not been compiled with mysqli support");
    }

    if ($show_header===true) // Show Webpage start, is the constructor the right place?
      echo $this->print_header();  
  }

  /**
   * Add the class name and uid to debug messages
   */
  public function debug($msg, $level=1) 
  {
    if (isset($_SESSION['uid'])) {
       $this->logger->debug("uid={$_SESSION['uid']} "  .get_class($this) ." " .$msg, $level);
    } else {
       $this->logger->debug(get_class($this) ." " .$msg, $level);
    }
  }

  public function logit($msg) 
  {
    if (isset($_SESSION['uid'])) {
       $this->logger->logit("uid={$_SESSION['uid']} "  .get_class($this) ." " .$msg);
    } else {
       $this->logger->logit(get_class($this) ." " .$msg);
    }
  }



  public function loggui($msg, $who=0, $priority='info')
  {
    $conn=$this->getConnection();     //  make sure we have a DB connection

    if ( (isset($_SESSION['uid'])) && ($who===0) ) {
      $who=$_SESSION['uid'];
    }
    $q="insert into guilog (who, host, datetime, priority, what)
         values ('$who', '$this->remote_host', NOW(), '$priority', 'Web: $msg')";
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseInsertException("Unable to insert guilog entry:: " .$conn->error);
  }


  public function print_logo()
  {
    global $head_left1;
    return $head_left1 . "\n";
  }
  public function print_headerSmall($print_links=true)
  {
    $this->print_header($print_links);
  }

  public function print_header($print_links=true)
  {
    global $header1;
    $ret='';

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_header: HEADER already true', 3);

    } else {
      if (! isset($_SESSION['nac_rights']) || ($_SESSION['nac_rights']=='')) {
        $this->debug("print_header() nac_rights not set: ignore, do not show menu/headers", 2);
        return;
      }

      define('HEADER',true); // The header is out
      $ret.= $header1 ."\n";
      #$ret.= $_SESSION['nac_rights'];
      $ret.= $this->print_logo() ."\n";
      $ret.= main_menu();     // webfuncs.inc
      $this->debug('print_header: done', 3);
    }
    return $ret;
  }


  public function print_headerMin()
  {
    global $header1;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_headerMin: HEADER already true',2);
    #} 
    #else {
      return $header1;
    }
  }

  public function print_header1()
  {
    global $header1;
    $ret= $header1 ."\n";
    $ret.= $this->print_logo() ."\n";
    return $ret;
  }


  public function print_footer($print_links=true)
  {
    global $sql_auth, $drupal_auth;
    $text='';

    if (defined('FOOTER')){   // already displayed?
      $this->debug('print_footer: FOOTER already true',2);
    } 
    else {
      if (!isset ($_SESSION['login_data'])) {
        $userdata=">> Not logged in <<";
        $text=<<<EOF
  </tr> </table> 
  </body> </html>
EOF;
      }
      else {
        $userdata="<br>Logged in as: " .$_SESSION['login_data']
          ." (" .$_SESSION['nac_rights_text'] .")";

        if (($sql_auth===true) || ($drupal_auth===true)) {
          $logout_button="<li><a href='./logout.php'>Log out</a></li>";
        }
        else {
          $logout_button='';
        }

        $text=<<<EOF
  <div id='user_footer'><p>$userdata</p></div>
  <a href='./logout.php'>Log out</a>
EOF;
        if ($print_links===true) {
// Stop for now, not needed with the new menus?
#          $text.=<<<EOF
#  <div id="headermenue">
#  <ul>
#     <li><A HREF='javascript:javascript:history.go(-1)'< Back</A></li>
#     <li><a href="./index.php">Main Menu</a></li>
#     $logout_button
#  </ul> </div>
#EOF;
        }
        $text.=<<<EOF
  </tr> </table> </body> </html>
EOF;

      }
      return $text;

    }
  }


  /* no menu on the botton */
  public function print_footer_empty()
  {
    if (defined('FOOTER')){   // already displayed?
      $this->debug('print_footer: FOOTER already true',2);
    } 
    else {
      $ret="</table></body></html>";
      define('FOOTER',true);
      return $ret;
    }
  }



  public function print_title($title)
  {
    $ret= $this->print_header();
    $ret.= "<div id='GuiList1Title'>{$title}</div>";
    $this->debug("print_title($title)" , 3);
    return $ret;
  }


  /**
   * Delete a record from the WebGUI
   */
  public function Delete($table, $id)
  {
    global $_SESSION, $_REQUEST;

    if ($_SESSION['nac_rights']<2)
      throw new InsufficientRightsException($_SESSION['nac_rights']);
    if ( $id===0 )
      throw new InvalidWebInputException("Delete() invalid index: zero");
    if ( strlen($table)===0 )
      throw new InvalidWebInputException("Delete() no table name.");
    
    $ret = $this->print_title("Delete {$this->module} record");

    #var_dump($_REQUEST);
    $conn=$this->getConnection();     //  make sure we have a DB connection
    $this->debug("Delete() index {$id}", 3);

    $q="DELETE FROM {$table} WHERE id='{$id}' LIMIT 1";     // only this record
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($q ." :: " .$conn->error);

    // Inform the user that is was OK
      $txt=<<<TXT
<p class='UpdateMsgOK'>Delete of entry with index=$id Successful</p>
 <br><p>Go back to the <a href="{$this->calling_href}">{$this->module} list</a></p>
</div>
TXT;
    $ret.= $txt;
    $this->logit("Deleted {$this->module}: table=$table, Id={$id}");
    $this->loggui("Deleted {$this->module}: table=$table, Id={$id}");
    return $ret;
  }


// ------------ functions common to the FreeNAC DB scheam, to be reused where possible in the
// ------------ Web GUI

/**
 * Get a Vlan name, given its index number
 */
public function get_vlan($s)
{
   $conn=$this->getConnection();     //  make sure we have a DB connection
   $ret='';
   $q="select default_name from vlan where id='$s'";
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret=$row['default_name'];
     }
   return($ret);
}


/**
 * Look up what systems were seen on a port in the last $conf->web_lastdays days
 */
public function get_hosts($port) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
  $ret='';
$q=<<<TXT
SELECT s.name, 
  s.LastSeen, CONCAT(users.Surname, ' ',users.GivenName, ', ',users.Department) as owner 
  FROM systems as s 
  LEFT JOIN users ON users.id = s.uid 
  WHERE LastPort='$port'  AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-{$this->conf->web_lastdays}) 
TXT;
     //$this->debug($q ,3);
     $res = $conn->query($q);
     if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

     while (($row = $res->fetch_assoc()) !== NULL) {
         $ret.=$row['name'] .' (' .$row['owner'] .'), ' .$row['LastSeen'] .'<br>';
     }
   //$this->debug($ret ,3);
   return($ret);
}


/**
 * Look up the first switch or patch location ID for a port
 */
public function get_locationid($port) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
  $query = "SELECT patchloc.id as patchlocid, switchloc.id as switchlocid FROM port
                  LEFT JOIN patchcable ON patchcable.port = port.id
                   LEFT JOIN location patchloc ON patchloc.id = patchcable.office
                  LEFT JOIN switch ON switch.id = port.switch
                   LEFT JOIN location switchloc ON switchloc.id = switch.location WHERE port.id = '$port';";
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {

       if (($row['patchlocid'] = '') || (!$row['patchlocid'])) {
         return($row['switchlocid']);
       } 
       else {
         return($row['patchlocid']);
       }
  }
}


/**
 * Lookup the patch outlet name
 */
public function get_patch($port)
{
  $conn=$this->getConnection(); 
  $q = "SELECT outlet,location.name as location FROM patchcable LEFT JOIN location ON location.id = patchcable.office WHERE port='$port'";
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
    return($row['outlet'] ." (" .$row['location'] .")");
  } 
  return(FALSE);
}


/**
 * Lookup the patch outlet name
 */
public function get_dose($port)
{
  $conn=$this->getConnection(); 
  $q = "SELECT outlet FROM patchcable WHERE port='$port'";
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
    return($row['outlet']);
  } 
  return(FALSE);
}


/**
 * Print a HTML lookup list of switches
 */
public function print_switch_sel($sw)
{
  $conn=$this->getConnection(); 
  $q = "SELECT DISTINCT(switch.id) as id, switch.name as name, switch.ip as ip, CONCAT(building.name,' ',location.name) as location
 FROM systems
 LEFT JOIN port ON port.id = systems.LastPort
 LEFT JOIN switch ON port.switch = switch.id
 LEFT JOIN location ON location.id = switch.location
  LEFT JOIN building ON building.id = location.building_id
WHERE  (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-" .$this->conf->web_lastdays .") AND port.switch != ''
ORDER BY switch.name;";

  $this->debug($q, 3);
  $res = $conn->query($q);
  $html="<select name=sw>\n";
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
    $html .= '<option value="' .$row['id'] .'"';
    if ($sw == $row['id']) {
      $html .= ' selected ';
    };
    $html .='>'.$row['name'].' ('.$row['location'].")</option>\n";
  };
  $html .= "</select>\n";
  return($html);
}

/* not used? */
function print_dot_sel() 
{
  $types = array('dot','neato','fdp','twopi','circo');
  $html = "<select name=\"dottype\">\n";

  foreach ($types as $mytype) {
    $html .= '<option value="'.$mytype.'"';
    if ($this->conf->web_dotcmd == $mytype) {
      $html .= ' selected ';
    };
    $html .= ">$mytype</option>\n";
  };
  $html .= "</select>\n";
  return($html);
}




/**
 * Get port comment
 */
public function get_port_comment($port) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
  // TBD: could really look up the port/switch name for nicer logging?
  if (isset($port) && is_numeric($port) && $port>1 ) {

    $this->debug("get_port_comment $port", 1);
    $q="SELECT comment from port WHERE id=$port LIMIT 1";
      $this->debug($q, 3);
      $res = $conn->query($q);
    if ($res === FALSE)
      throw new DatabaseErrorException($conn->error);
    while (($row = $res->fetch_assoc()) !== NULL) {
      return ($row['comment']);
    }

  } else {
    $this->logit("get_port_comment invalid port index=<$port>");
    // return nothing
  }
}


/**
 * Set the restart flag for a port
 */
public function port_restart_request($port) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection

  // TBD: could really look up the port/switch name for nicer logging?

  if (isset($port) && is_numeric($port) && $port>1 ) {

    $this->debug("port_restart_request $port", 1);
    $this->loggui("Port index $port restart requested");
    $q="UPDATE port set restart_now=1 WHERE id=$port LIMIT 1";
      $this->debug($q, 3);
      $res = $conn->query($q);
    if ($res === FALSE)
      throw new DatabaseErrorException($conn->error);
    return true;

  } else {
    $this->logit("port_restart_request invalid port index=<$port>");
    return false;
  }
}

/**
 * Delete a port
 */
public function port_delete($port)
{
  $conn=$this->getConnection();     //  make sure we have a DB connection

  // TBD: could really look up the port/switch name for nicer logging?
  if (isset($port) && is_numeric($port) && $port>1 ) {

    $this->debug("port_delete $port", 1);
    $q="DELETE from port WHERE id=$port LIMIT 1";
      $this->debug($q, 3);
      $res = $conn->query($q);
    if ($res === FALSE)
      throw new DatabaseErrorException($conn->error);

    $this->loggui("Port $port deleted ");
    $this->logit("Port $port deleted ");
  } else {
    $this->logit("port_delete invalid port=$port");
  }
}


/**
 * Look up the first switch or patch location for a port
 */
public function get_location($port) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
$q=<<<TXT
SELECT patchloc.id as patchlocid, CONCAT(patchbd.name,' ',patchloc.name) as patchloc,
   switchloc.id as switchlocid, CONCAT(switchbd.name,' ',switchloc.name) as switchloc
   FROM port
   LEFT JOIN patchcable ON patchcable.port = port.id
   LEFT JOIN location patchloc ON patchloc.id = patchcable.office
   LEFT JOIN building as patchbd ON patchbd.id = patchloc.building_id
   LEFT JOIN switch ON switch.id = port.switch
   LEFT JOIN location switchloc ON switchloc.id = switch.location
   LEFT JOIN building as switchbd ON switchbd.id = switchloc.building_id
   WHERE port.id = '$port'
TXT;

  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {

       if (($row['patchloc'] = '') || (!$row['patchloc'])) {
         return($row['switchloc']);
       } 
       else {
         return($row['patchloc']);
       }
  }
}

public function strip_datversion($long_version) 
{
     $list=explode('.',$long_version);
     $short_version = $list[2];
     return($short_version);
}


public function print_dat_stats($q) 
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
  $readme_url='http://vil.nai.com/vil/DATReadme.aspx';
  $ret='';
  $unknown='';
  $total=0;
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  $ret.= "<table cellspacing=0 cellpadding=5 border=1>\n";
  $ret.= "<tr><th><a href=\"$readme_url\">DAT Version</a><th>count";

     while (($row = $res->fetch_assoc()) !== NULL) {
       #$short_version = $this->strip_datversion($row['virusdatver']);
       $short_version = $row['virusdatver'];
       if ($short_version > 0) {
               $ret.= "<tr>";
               $ret.= "<td>$short_version</a>";
               $ret.= "<td>".$row['count'];
               $ret.= "\n";
       } else {
               $unknown = $unknown + $row['count'];
       };
       $total = $total + $row['count'];

     }
     $ret.= "<tr><td>Unknown<td>$unknown";
     $ret.= "</table>\n<b>Total = $total\n";
  return($ret);
}


public function print_stats($q)
{
  $conn=$this->getConnection();     //  make sure we have a DB connection
  $ret='';
  $total=0;
  $this->debug($q, 3);
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  $ret.= "<table cellspacing=0 cellpadding=5 border=1>\n";
     while (($row = $res->fetch_assoc()) !== NULL) {
        #if (!$th) {
        if (!isset($th)) {
                $ret.= "<tr>";
                foreach ($row as $key => $value) {
                        $ret.= "<th>$key";
                };
                $ret.= "\n";
                $th = TRUE;
        };
        $ret.= "<tr>";
        foreach ($row as $value) {
                $ret.= "<td>$value &nbsp;";
        };
        $ret.= "\n";
        $total = $total + $value;

     }
     $ret.= "</table>\n<b>Total = $total\n";
  return($ret);
}


// ---------------------------------------------
}  // class

?>
