<?php
#
# VMPS: vmpsdot.php
#
#  2006.05.25/Sean Boran: Remove need for register_globals
#    Add debug1()
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 FreeNAC
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
##########################

$debug_flag1=false;
$debug_flag2=false;
#$debug_flag1=true;
#$debug_flag2=true;

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('./webfuncs.inc');


# Need to specify exact path:
$dot_file=dirname(__FILE__) . "/tmp/vmps.dot";
define_syslog_variables();
openlog("vmpsdot", LOG_PID, LOG_LOCAL5);

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


function get_dose($port) {
  $sel = "SELECT outlet FROM patchcable WHERE port='$port';";
  $res = mysql_query($sel)  or die ("Unable to query MySQL ($sel)");

  if ($dose = mysql_fetch_array($res)) {
    return($dose['outlet']);
  } else {
    return(FALSE);
  };
  return($dose);
};


function get_patch($port) {
  $sel = "SELECT outlet,location.name as location FROM patchcable LEFT JOIN location ON location.id = patchcable.office WHERE port='$port';";
  echo $sel;
  $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
  if (mysql_num_rows($sel) > 0) {
	  list($dose,$location) = mysql_fetch_array($res);
	  return("$dose ($location)");
  } else {
	  return(FALSE);
  };
};

// ----- main() ----------------
db_connect($dbuser,$dbpass);

$sw     = validate_webinput($_REQUEST['sw']);
$dottype= validate_webinput($_REQUEST['dottype']);
if (! isset($sw))      { $sw = '192.168.245.71'; };

## We don't need this as a paramter for now, and its dangerous, since
## its goes to the command line.
#if (! isset($dottype)) { $dottype = 'dot'; };
#if (!      ($dottype)) { $dottype = 'dot'; };
#$dottype="/usr/bin/dot";
$dottype=$conf->web_dotcmd;
debug2("sw=$sw, dottype=$dottype");

$dotvmps=''; $dotpatch=''; $dothosts=''; $dotdose=''; $dotports='';

// 1. Make the ports
$sel_ports = "SELECT * FROM port WHERE switch='$sw';";
debug2($sel_ports);
$ports = mysql_query($sel_ports) or die ("Unable to query MySQL ($sel_ports)");
$dotports .= "\n# 1. Make the ports\n\n";
if (mysql_num_rows($ports) > 0) {
	while ($port = mysql_fetch_array($ports)) {
	  if ((stristr($port['name'],'Fa')) || (stristr($port['name'],'Gi'))) {
	  $portref = 'port'.strtr($port['name'], "/", "e");
	  $dotports .= $portref." [ label=\"".$port['name']."\",shape=\"box\" ] \n";
	  };
	};
};

// 2. Make the nodes
$sel_dose = "SELECT patchcable.outlet as outlet, location.name as location, port.name as port FROM patchcable 
                       LEFT JOIN port ON port.id = patchcable.port 
                       LEFT JOIN switch ON switch.id = port.switch 
                       LEFT JOIN location ON location.id = patchcable.office
                   WHERE switch.id = $sw AND patchcable.outlet != '';";

debug2($sel_dose);
$doses = mysql_query($sel_dose) or die ("Unable to query MySQL ($sel_dose)");
if (mysql_num_rows($doses) > 0) {
	while ($dose = mysql_fetch_array($doses)) {
	  $doseref = 'dose' . strtr($dose['outlet'], "/. -", "efgh");
	  $dotdose .= $doseref . " [ label=\"".$dose['location'].'\n('.$dose['outlet'].')",shape="egg",fontsize=10 ] '."\n";

	// 3. and patch them to the ports
	  $portref = 'port'.strtr($dose['port'], "/", "e");
	  $dotpatch .= $portref.'->'.$doseref." [	dir=\"none\" ] \n";
	};
};

// 2. Make nodes for all the hosts since in the last $querdays Days.
#$today = "AND LastSeen regexp " . date("Y-m-d");
#$today = "";
$today = " AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-".$conf->web_lastdays.")";

$sel_hosts = "SELECT systems.mac as mac, systems.name as name, users.username as description, vlan.color as vlan_color, port.name as port, port.id as portid
        FROM systems 
	LEFT JOIN vlan ON vlan.id = systems.vlan
	LEFT JOIN users ON users.id = systems.uid
	LEFT JOIN port ON systems.LastPort = port.id
	LEFT JOIN switch ON port.switch = switch.id
          WHERE switch='$sw' $today";

$hosts = mysql_query($sel_hosts) or die ("Unable to query MySQL ($sel_hosts)");

$dothosts .= "\n\n#2. Make nodes : hosts\n";
$dotvmps .= "\n\n#3. Make links\n";
if (mysql_num_rows($hosts) > 0) {
	while ($host = mysql_fetch_array($hosts)) {
	  $hostref = 'host'.strtr($host['mac'], ".", "e");
	if ($host['vlan_color'] == '') { $host['vlan_color'] = 'DEDEDE';};
	  $dothosts .= $hostref." [ label=\"".$host['name'].'\n'.$host['description'].'",style="filled",fontsize=10,fillcolor="#'.$host['vlan_color']."\" ] \n";

	// 3. Make links
	  #$portref = $switchref. 'port'. strtr($host['port'], "/", "e");
	  $portref = 'port'. strtr($host['port'], "/", "e");
	  $hostdose = get_dose($host['portid']);
	  if ($hostdose) {
	    $doseref = 'dose'.strtr($hostdose, "/. -", "efgh");
	    $dotvmps .= $doseref.'->'.$hostref." [ dir=\"none\" ] \n";
	  } else {
	    $dotvmps .= $portref.'->'.$hostref." [ dir=\"none\" ] \n";
	  };
	};
};

$dot = "digraph simple_hierarchy {\n\n";
$dot .= $dotports."\n";
$dot .= $dotdose."\n";
$dot .= $dotpatch."\n";
$dot .= $dothosts."\n";
$dot .= $dotvmps."\n";
$dot .= "\n}\n";

$cmd = "$dottype $dot_file -Tpng ";

error_reporting(E_ERROR|E_WARNING|E_PARSE|E_NOTICE);
// write the dotfile
if ($debug_flag2===true) {
  debug2("DOT command would be: $cmd");
  debug2("DOT file contents would be: $dot");
  echo "<b>\n$cmd\n</b>\n<hr>\n";
  echo "<pre>$dot</pre>";

} else {
  #$dotfile = fopen("$dot_file", "w");
  debug1("open $dot_file"); 
  if ( ($dotfile = fopen("$dot_file", "w") ) == FALSE ) {
    logit("vmpsdot.php: Cannot open $dot_file for writing ");
    debug1("vmpsdot.php: Cannot open $dot_file for writing ");
    echo "Cannot open $dot_file for writing ";
  } else { 
    fwrite($dotfile,$dot);
    fclose($dotfile);
    debug1("tmp/vmps.dot written"); 
    debug1($cmd);
    passthru($cmd);
    //exec("rm tmp/vmps.dot > /dev/null");
  }
};

//echo $dot;

?>
