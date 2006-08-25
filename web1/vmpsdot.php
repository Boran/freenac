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

include('config.inc');

$dot_file="tmp/vmps.dot";
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


function get_dose($switch,$port) {
  $sel = "SELECT von_dose FROM patchcable WHERE nach_switch='$switch' AND nach_port='$port';";
  $res = mysql_query($sel);

  if ($dose = mysql_fetch_array($res)) {
    return($dose['von_dose']);
  } else {
    return(FALSE);
  };
  return($dose);
};


function get_patch($switch,$port) {
  $sel = "SELECT von_dose,von_office FROM patchcable WHERE nach_switch='$switch' AND nach_port='$port';";
  list($dose,$office) = mysql_fetch_array(mysql_query($sel));
  return("$dose ($office)");
};

// ----- main() ----------------
db_connect();

$sw     = validate_webinput($_REQUEST['sw']);
$dottype= validate_webinput($_REQUEST['dottype']);
if (! isset($sw))      { $sw = '192.168.245.71'; };

## We don't need this as a paramter for now, and its dangerous, since
## its goes to the command line.
#if (! isset($dottype)) { $dottype = 'dot'; };
#if (!      ($dottype)) { $dottype = 'dot'; };
$dottype="/usr/bin/dot";
debug2("sw=$sw, dottype=$dottype");

$dotvmps=''; $dotpatch=''; $dothosts=''; $dotdose=''; $dotports='';

// 1. Make the ports
$sel_ports = "SELECT * FROM port WHERE switch='$sw';";
debug2($sel_ports);
$ports = mysql_query($sel_ports);
while ($port = mysql_fetch_array($ports)) {
  $portref = 'port'.strtr($port['name'], "/", "e");
  $dotports .= $portref." [ label=\"".$port['name']."\",shape=\"box\" ] \n";
};

// 2. Make the nodes
$sel_dose = "SELECT * FROM patchcable WHERE nach_switch='$sw' AND von_dose != ''";
$doses = mysql_query($sel_dose);
while ($dose = mysql_fetch_array($doses)) {
  $doseref = 'dose' . strtr($dose['von_dose'], "/. -", "efgh");
  $dotdose .= $doseref . " [ label=\"".$dose['von_office'].'\n('.$dose['von_dose'].')",shape="egg",fontsize=10 ] '."\n";

// 3. and patch them to the ports
  $portref = 'port'.strtr($dose['nach_port'], "/", "e");
  $dotpatch .= $portref.'->'.$doseref." [	dir=\"none\" ] \n";
};

// 2. Make nodes for all the hosts since in the last $querdays Days.
#$today = "AND LastSeen regexp " . date("Y-m-d");
#$today = "";
$today = " AND (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-$vmpsdot_querydays)";

$sel_hosts = "SELECT * FROM systems WHERE switch='$sw' $today";
$hosts = mysql_query($sel_hosts);
while ($host = mysql_fetch_array($hosts)) {
  $hostref = 'host'.strtr($host['mac'], ".", "e");
  $dothosts .= $hostref." [ label=\"".$host['name'].'\n'.$host['description'].'",style="filled",fontsize=10,fillcolor="'.get_color($host['vlan'])."\" ] \n";

// 3. Make links
  #$portref = $switchref. 'port'. strtr($host['port'], "/", "e");
  $portref = 'port'. strtr($host['port'], "/", "e");
  $hostdose = get_dose($host['switch'],$host['port']);
  if ($hostdose) {
    $doseref = 'dose'.strtr($hostdose, "/. -", "efgh");
    $dotvmps .= $doseref.'->'.$hostref." [ dir=\"none\" ] \n";
  } else {
    $dotvmps .= $portref.'->'.$hostref." [ dir=\"none\" ] \n";
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

// write the dotfile
if ($debug_flag2===true) {
  echo "<b>\n$cmd\n</b>\n<hr>\n";
  echo "<pre>$dot</pre>";

} else {
  $dotfile = fopen("tmp/vmps.dot", "w");
  fwrite($dotfile,$dot);
  fclose($dotfile);
  debug2("tmp/vmps.dot written"); 
  debug1($cmd);
  passthru($cmd);
  //exec("rm tmp/vmps.dot > /dev/null");
};

//echo $dot;

?>
