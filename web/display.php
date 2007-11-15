<?php

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('./webfuncs.inc');

include_once('print.inc');

function display_stuff()
{
   global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);
   $single_host = mysql_real_escape_string($_GET["single_host"]);
   echo print_host($single_host);
}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=1)
   {
      echo header_read();
      echo main_stuff();
      echo "<hr /><br />";
      display_stuff();
      echo read_footer();
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
{
   echo header_read();
   echo main_stuff();
   echo "<hr /><br />";
   display_stuff();
   echo read_footer();
}

?>
