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
            <li><a href="GuiEditDevice_control.php"  title="Add a new end device to the database">Add end-device</a></li>
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

/**
 * new style drop down menus
 * TBD: implement this as a reusable claas!
*/
function main_menu2()
{
   $text=<<<EOF

<script type="text/javascript"> <!--
function showHideLayers() { //v9.0
  var i,p,v,obj,args=showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) 
  with (document) if (getElementById && ((obj=getElementById(args[i]))!=null)) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
//--> </script>


<table id="menu0">
<tbody>
<tr>

<td><div class="smenutitle" id="apDiv2"
  onmouseover="showHideLayers('smenu_device1','','show')" 
  onmouseout ="showHideLayers('smenu_device1','','hide')">
  <a >End-Devices</a>
  <div class="smenu" id="smenu_device1">
    <a href="unknowns.php" title="List unknown end devices and print/edit/delete them">Find unknowns</a><br/>
    <a href="find.php"  title="List end devices recently seen and print/edit/delete them">Find recent </a><br/>
    <a href="listall.php"  title="List end devices with lots of detail">Detailed list</a><br/>
    <a href="GuiEditDevice_control.php?action=Add"  title="Add a new End-Device/PC to the Database">Add new End-Device</a><br/>
  </div>
</div></td>

<td><div class="smenutitle" 
  onmouseover="showHideLayers('smenu_stats1','','show')" 
  onmouseout ="showHideLayers('smenu_stats1','','hide')">
  <a >Reports</a>
  <div  class="smenu" id="smenu_stats1">
     <a href="stats.php" title="Port usage, End devices per Vlan/Class/Operating System, Anti-virus status/Windows Update Errors">Statistics & Graphs</a><br/>
     <a href="hubs.php" title="List ports with more than one end-device">Hub finder</a><br/>
     <a href="graphswitch.php" title="Graphs cables/ports recently used for one switch">Switch port diagram: single switch</a><br/>
     <a href="graphswitchall.php" title="Graphs cables/ports recently used for all switches">Switch port diagram: all switches</a><br/>
  </div>
</div></td>

<td><div class="smenutitle" 
  onmouseover="showHideLayers('smenu_config','','show')"
  onmouseout ="showHideLayers('smenu_config','','hide')">
  <a >Configuration</a>
<div class="smenu" id="smenu_config">

   <a href="port.php" title="Switch port naming, usage, configuration">Switch-Port</a><br/>
   <a href="switch.php" title="Switch names/IPs/settings">Switches</a><br/>
            <a href="config.php" title="Main configuration table">Main Configuration</a><br/>
            <a href="vlan.php"   title="Definiton of vlan names, numbers">Vlans</a><br/>
   <a href="patchcable.php" title="Document of cable between switch ports and end devices">Patch cables</a><br/>
            <a href="vlanswitch.php" title="Non standard vlan on selected switches">Vlan exceptions</a><br/>
	    <a href="user.php">Users</a><br/>
            <a href="location.php">Locations</a><br/>
            <a href="nmapsubnet.php">Subnets </a><br/>
	    <a href="class1.php">Device Class1</a><br/>
            <a href="class2.php">Class2</a></li>
</div>
</div></td>


<td><div class="smenutitle" 
  onmouseover="showHideLayers('smenu_mon','','show')"
  onmouseout ="showHideLayers('smenu_mon','','hide')">
  <a >Monitoring</a>
<div class="smenu" id="smenu_mon">
            <a href="phpsysinfo/">Server information</a><br/>
            <a href="loggui.php">GUI change log</a><br/>
            <a href="logserver.php">Server summary log</a><br/>
            <a href="logtail1.php">Syslog Message log</a><br/>
            <a href="logtaildebug.php">Syslog Debug Log</a><br/>
</div>
</div></td>


<td><div class="smenutitle"
  onmouseover="showHideLayers('smenu_help','','show')"
  onmouseout ="showHideLayers('smenu_help','','hide')">
  <a >Help</a>
<div class="smenu" id="smenu_help">
  <a href="http://freenac.net/en/community?q=en/usersguide">Users Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/installguide">Install Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/techguide">Technical Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/support/faqs">FAQ</a><br/>
  <a href="http://freenac.net/phpBB2/">FreeNAC Forum</a><br/>
  <a href="http://freenac.net/en/community">FreeNAC Website</a><br/>
</div>
</div></td>

<td><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><td>

</tr>
</tbody>
</table>

EOF;
   return $text;
}

### --------- main() -------------
  $report=new WebCommon(false);  
  $report->logger->setDebugLevel(1);
  echo $report->print_headerMin();

  echo main_menu2();
  #echo main_menu();   // TBD: show the old menu if there is no java script?
  echo <<<EOF
  <img src='./images/logo500.png' border='0' style="padding-left: 30px;"/>
EOF;

  #var_dump($_SESSION);
  echo $report->print_footer(false);
?>
