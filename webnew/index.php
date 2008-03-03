<?php
/**
 * index.php
 *
 * Long description for file:
 * Default script for starting the Web GUI
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: index.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */


## ----- Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./");
  require_once('webfuncs.inc');
  include 'session.inc.php'; // resume or create session
  $logger=Logger::getInstance();
  $logger->setDebugLevel(3); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
# --- end of standard header ------


/**
 * main_menu
 * TBD: make prettier, current focus is functiionality, not form
 */
function main_menu()
{
         #<ul class=text16 style='list-style: &#187; padding: 0 0 1em 0; margin-bottom: 1em;'>
         #<div class='button'>
         #<font class=text20 style='word-spacing: -1px'>FreeNAC for {$_SESSION['organisation']}</font>
            #<li><a href="report_specs.php"  >List products (specifications)</a></li>
   $text=<<<EOF
         <p>  </p>
         <div class='text16' style='padding: 4px; line-height: 150%'>
         <h3>End-device administration</h3> <ul>
            <li><a href="unknowns.php">List Unknown End-Device/PCs</a></li>
            <li><a href="find.php">List End-Device/PCs: last seen</a></li>
            <li><a href="listall.php">List End-Device/PCs: more details</a></li>
         </ul>

         <h3>Reporting</h3> <ul>
            <li><a href="hubs.php">Hub finder</a>: list ports with more than one end-device</li>
            <li><a href="stats.php">TBD: Statistics</a>: End_devices per class/OS/VLAN</li>
            <li>TBD: Cable + switch port usage: <a href="vmps.php">one switch</a>, <a href="allvmps.php">all switches</a></li>
            <li>TBD: Open Ports, Wsus, Epo, Daily stats..
         </ul>
	 <h3>Configuration</h3>
	 <ul>
		<li><a href="port.php">Switch-Port config</a>, <a href="switch.php">Switches</a>,
                          <a href="patchcable.php">Patch cables</a>
		<li>Base: <a href="config.php">Global config</a>, <a href="vlan.php">Vlans</a>, 
                          <a href="vlanswitch.php">Vlan exceptions</a>
		<li><a href="user.php">Users</a>, <a href="location.php">Locations</a>, <a href="nmapsubnet.php">Subnets </a>
		<li><a href="class1.php">Device Class1</a>, <a href="class2.php">Class2</a>
	 </ul> 
	 <h3>Monitoring</h3>
	 <ul>
		<li><a href="phpsysinfo/">System information</a>
		<li>DB history: <a href="loggui.php">GUI changes</a>, <a href="logserver.php">Server activity</a>
		<li>Syslog: <a href="logtail1.php">messages</a>, <a href="logtaildebug.php">Debug Log</a>
	 </ul> 

         </div>
EOF;
   return $text;
}


### --------- main() -------------
  ob_start();
  echo print_headerSmall();
  #var_dump($_SESSION);
  echo main_menu();
  echo read_footer();
?>
