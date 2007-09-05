<?php

dir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('config.inc');
// include functions
require_once('funcs.inc');

function main_menu()
{
   $text=<<<EOF
         <hr />
         <h2>End-device administration:</h2>
         <ul>
            <li><a href="find.php">Finding PCs/ Devices</a></li>
         </ul>
         <h2>Reporting</h2>
	 <ul>
            <li><a href="hubs.php">Hub finder</a>: list ports with more than one end-device</li>
            <li><a href="stats.php">Statistics</a>: End_devices per class/OS/VLAN</li>
            <li>Cable + switch port usage: <a href="vmps.php">one switch</a>, <a href="allvmps.php">all switches</a></li>
         </ul>
EOF;
   return $text;
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo main_menu();
      echo read_footer();   
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo main_menu();
   echo read_footer();
}


?>
