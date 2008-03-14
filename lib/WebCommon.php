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
  protected $calling_script;


  function __construct($show_header=true)
  {
    parent::__construct();
    $this->calling_script=basename($_SERVER['SCRIPT_FILENAME']);   // TBD: clean?
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
         values ('$who', '$this->remote_host', NOW(), '$priority', '$msg')";
      $this->debug($q, 3);
      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseInsertException("Unable to insert guilog entry:: " .$conn->error);
  }


  public function print_logo()
  {
    global  $head_left1;
    echo $head_left1;
  }
  public function print_header($print_links=true)
  {
    echo $this->print_headerMin();
    echo $this->print_logo();
    echo main_menu();     // webfuns.inc
  }
  public function print_headerSmall($print_links=true)
  {
    $this->print_header($print_links);
  }

  /* the old one... */
  public function print_header_old($print_links=true)
  {
    global $header1, $header2, $head_right1, $head_right2;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_header: HEADER already true',2);

    } else {
      if ($print_links===false) {
        $lthis->debug('print_header: do not print right links', 3);
        $head_right1='';
        $head_right2='';
      }
      $ret= $header1 . $header2;
      define('HEADER',true); // The header is out
      $this->debug('print_header: done', 3);
      return $ret;
    }
  }


  public function print_headerMin()
  {
    global $header1;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_headerMin: HEADER already true',2);
    } 
    return $header1;
  }

  public function print_headerSmall_old($print_links=true)
  {
    global $header1, $header2, $head_right1, $head_right2, $header2_small;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_headerSmall: HEADER already true',2);
    } 
    else {
      if ($print_links===false) {
        $this->debug('print_headerSmall: do not print right links', 3);
        $head_right1='';
        $head_right2='';
      }
      #$ret= $header1 . $header2;
      $ret= $header1 . $header2_small;
      define('HEADER',true); // The header is out
      $this->debug('print_headerSmall done', 3);
      return $ret;
    }
  }


  public function print_footer($print_links=true)
  {
    global $sql_auth, $drupal_auth;

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
EOF;
        if ($print_links===true) {
          $text.=<<<EOF
  <div id="headermenue">
  <ul>
     <li><A HREF='javascript:javascript:history.go(-1)'< Back</A></li>
     <li><a href="./index.php">Main Menu</a></li>
     $logout_button
  </ul> </div>
EOF;
     #<li><a href="./ChooseAccount.php">Change Account</a></li>
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


// ------------ funnions common to the FreeNAC DB scheam, to be reused where possible in the
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
  //$this->debug($q);
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
