<?php
/**
 *
 * graphdot.php
 *
 * Long description for file:
 *
 * @package     FreeNAC
 * @author      Core team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: find.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 * Parametetrs:
 *    /graphdot.php?sw=7
 */

## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../lib:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

  ## Loggin in? User identified?
  include 'session.inc.php';
  check_login(); // logged in?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------


### --------- main() -------------



// 1. Check rights
if ($_SESSION['nac_rights']<1) {
  throw new InsufficientRightsException($_SESSION['nac_rights']);
} 
else if ($_SESSION['nac_rights']==1) {
  $action_menu='';
}
else if ($_SESSION['nac_rights']==2) {
  $action_menu='';
  //$action_menu=array('Print','Edit');   // 'buttons' in action column
}
else if ($_SESSION['nac_rights']==99) {
  $action_menu='';
  //$action_menu=array('Print', 'Edit', 'Delete');   // 'buttons' in action column
}

// set parameters   fro gui_control.php
$title="";
$sortlimit=200;
$sortby='';
$searchby='';
$searchstring='';
$action_fieldname="";     $idx_fieldname="";

$dotvmps=''; $dotpatch=''; $dothosts=''; $dotdose=''; $dotports='';

// ___________ functions ________________

function get_color($vlan) {
  if (($vlan == 0) || ($vlan == 900)) {
    return('grey70');
  };

  if ($vlan < 9) {
    return('darkorange');
  };

  if ($vlan < 500) {
    return('khaki1');
  };

  return('darkgoldenrod1');
};


// ___________ main() ________________
$dot_file=dirname(__FILE__) . "/tmp/vmps.dot";

$sw = validate_webinput($_REQUEST['sw']);
if (! isset($sw))
  throw new Exception('Invalid switch');

// generate the HTML page object
  $report=new WebCommon(false);     // new webpage, no header
  $report->logger->setDebugLevel(1); 
  $conn=$report->getConnection();     //  make sure we have a DB connection
  $dottype=$report->conf->web_dotcmd;   // path to dot tool


// 1. Make the ports
$q = "SELECT * FROM port WHERE switch='$sw';";
  $report->debug($q, 3);
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  $dotports .= "\n# 1. Make the ports\n\n";
  while (($row = $res->fetch_assoc()) !== NULL) {
    if ((stristr($row['name'], 'Fa')) || (stristr($row['name'], 'Gi'))) {
      $portref = 'port' .strtr($row['name'], "/", "e");
      $dotports .= $portref ." [ label=\"" .$row['name'] ."\",shape=\"box\" ] \n";
    }
  }


// 2. Make the nodes
$q = "SELECT patchcable.outlet as outlet, location.name as location, port.name as port FROM patchcable
                       LEFT JOIN port ON port.id = patchcable.port
                       LEFT JOIN switch ON switch.id = port.switch
                       LEFT JOIN location ON location.id = patchcable.office
                   WHERE switch.id = $sw AND patchcable.outlet != '';";
  $report->debug($q, 3);
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  while (($row = $res->fetch_assoc()) !== NULL) {
    $doseref = 'dose' . strtr($row['outlet'], "/. -", "efgh");
    $dotdose .= $doseref . " [ label=\"".$row['location'].'\n('.$row['outlet'].')",shape="egg",fontsize=10 ] '."\n";

    // 3. and patch them to the ports
    $portref = 'port' .strtr($row['port'], "/", "e");
    $dotpatch .= $portref .'->' .$doseref ." [       dir=\"none\" ] \n";
  }



// Make nodes for all the hosts since in the last $querdays Days.
$today = " AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-".$conf->web_lastdays.")";


$q = "SELECT systems.mac as mac, systems.name as name, users.username as description, vlan.color as vlan_color, port.name as port, port.id as portid
        FROM systems
        LEFT JOIN vlan ON vlan.id = systems.vlan
        LEFT JOIN users ON users.id = systems.uid
        LEFT JOIN port ON systems.LastPort = port.id
        LEFT JOIN switch ON port.switch = switch.id
        WHERE switch='$sw' $today";
  $report->debug($q, 3);
  $res = $conn->query($q);
  if ($res === FALSE)
       throw new DatabaseErrorException($q .'; ' .$conn->error);

  $dothosts .= "\n\n#2. Make nodes : hosts\n";
  $dotvmps .= "\n\n#3. Make links\n";

  while (($row = $res->fetch_assoc()) !== NULL) {
    $hostref = 'host'.strtr($row['mac'], ".", "e");
    if ($row['vlan_color'] == '') { $row['vlan_color'] = 'DEDEDE';};
      $dothosts .= $hostref." [ label=\"".$row['name'].'\n'.$row['description'].'",style="filled",fontsize=10,fillcolor="#'.$row['vlan_color']."\" ] \n";

    // 3. Make links
    $portref = 'port'. strtr($row['port'], "/", "e");
    $hostdose = $report->get_dose($row['portid']);
    if ($hostdose) {
      $doseref = 'dose'.strtr($hostdose, "/. -", "efgh");
      $dotvmps .= $doseref.'->'.$hostref." [ dir=\"none\" ] \n";
    } else {
      $dotvmps .= $portref.'->'.$hostref." [ dir=\"none\" ] \n";
    }

  }


$dot = "digraph simple_hierarchy {\n\n";
$dot .= $dotports."\n";
$dot .= $dotdose."\n";
$dot .= $dotpatch."\n";
$dot .= $dothosts."\n";
$dot .= $dotvmps."\n";
$dot .= "\n}\n";

$cmd = "$dottype $dot_file -Tpng ";

if ($report->logger->getDebugLevel() > 1) {
  $report->debug("DOT command would be: $cmd");
  $report->debug("DOT file contents would be: $dot");
  echo "<b>\n$cmd\n</b>\n<hr>\n";
  echo "<pre>$dot</pre>";

} else {
  #$dotfile = fopen("$dot_file", "w");
  $report->debug("open $dot_file", 1);
  if ( ($dotfile = fopen("$dot_file", "w") ) == FALSE ) {
    $report->logit("Cannot open $dot_file for writing ");
    echo "Cannot open $dot_file for writing ";

  } else {
    fwrite($dotfile,$dot);
    fclose($dotfile);
    $report->debug("tmp/vmps.dot written");
    $report->debug($cmd);
    passthru($cmd);
    //exec("rm tmp/vmps.dot > /dev/null");
  }
}








  echo $report->print_footer();


?>
