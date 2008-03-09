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
 * use bw.css for formatting
 */
function main_menu()
{
   $text=<<<EOF
         <p>  </p>
         <div id=menu1>
         <h3>End-device administration</h3> 
         <ul>
            <li><a href="unknowns.php" title="List unknown end devices and print/edit/delete them">Find unknowns</a></li>
            <li><a href="find.php"  title="List end devices recently seen and print/edit/delete them">Find recent </a></li>
            <li><a href="listall.php"  title="List end devices with lots of detail">Detailed list</a></li>
         </ul>

         <h3>Reporting</h3> 
         <ul>
            <li><a href="stats.php" title="Port usage, End devices per Vlan/Class/Operating System, 
	Anti-virus status/Windows Update Errors">Statistics & Graphs</a></li>
            <li><a href="hubs.php" title="List ports with more than one end-device">Hub finder</a></li>
         </ul>
         <ul>
            <li>Switch port diagrams:</li>
            <li><a href="graphswitch.php" title="Graphs cables/ports recently used for one switch">single switch</a></li>
            <li><a href="graphswitchall.php" title="Graphs cables/ports recently used for all switches">all switches</a></li>
         </ul>
	 <h3 title="View current FreeNAC settings">Configuration</h3>
	 <ul>
	    <li><a href="port.php" title="Switch port naming, usage, configuration">Switch-Port</a></li>
            <li><a href="switch.php" title="Switch names/IPs/settings">Switches</a></li> 
            <li><a href="patchcable.php" title="Document of cable between switch ports and end devices">Patch cables</a></li>
	 </ul> 
	 <ul>
	    <li>Key settings:</li>
            <li><a href="config.php" title="Main configuration table">Config</a></li>
            <li><a href="vlan.php"   title="Definiton of vlan names, numbers">Vlans</a></li>
            <li><a href="vlanswitch.php" title="Non standard vlan on selected switches">Vlan exceptions</a></li>
	 </ul> 
	 <ul>
	    <li><a href="user.php">Users</a></li>
            <li><a href="location.php">Locations</a></li>
            <li><a href="nmapsubnet.php">Subnets </a></li>
	    <li><a href="class1.php">Device Class1</a></li>
            <li><a href="class2.php">Class2</a></li>
	 </ul> 
	 <h3>Monitoring</h3>
	 <ul>
	    <li><a href="phpsysinfo/">Server information</a></li>
	 </ul> 
	 <ul>
	    <li>DB history:</li>
            <li><a href="loggui.php">GUI change log</a></li>
            <li><a href="logserver.php">Server summary log</a></li>
	 </ul> 
	 <ul>
	    <li>Syslog:</li>
            <li><a href="logtail1.php">Message log</a></li>
            <li><a href="logtaildebug.php">Debug Log</a></li>
	 </ul> 

         </div>
EOF;
   return $text;
}


### --------- main() -------------
  $report=new WebCommon(false);  
  $report->logger->setDebugLevel(1);
  echo $report->print_headerSmall(true);
  #var_dump($_SESSION);
  echo main_menu();
  echo $report->print_footer();
?>
