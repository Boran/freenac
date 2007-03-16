<?php

dir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');

function main_menu()
{
   $text=<<<EOF
         <hr />
         <h2>Web Interface to the NAC Database:</h2>
         <ul>
            <li><a href="read.php">Finding PCs/ Devices</a> (Read-only query)
            <li><b><a href="read.php?name=unknown&submit=submit">Unknown hosts</a></b>
            <li><a href="hubs.php">Hub finder</a>: list ports with more than one end-device
         </ul>
         <h2>Statistics</h2>
         <a href="stats.php">some basic stats</a></h2>
         <h2>Graphs</h2>
         View the machines connected to each cable and port:<br>
         <ul>
            <li>Graphical view : <a href="vmps.php">one switch</a>
            <li><a href="allvmps.php">all switches</a>
         </ul>

EOF;
   return $text;
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_SAMACCOUNTNAME']);
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
