<html>
<head>
<?php
chdir(dirname(__FILE__));
set_include_path("./:../");

require("./funcs.inc");

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_SAMACCOUNTNAME']);
   if ($rights>=2)
   {
      echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=./write/\">\n";
      echo "</head>\n";
      echo "</html>\n";
   }
   else if ($rights==1)
   {
      echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=./read/\">\n";
      echo "</head>\n";
      echo "</html>\n";
   }
   else
   { 
      echo "\t<title>Invalid credentials</title>\n";
      echo "</head>\n";
      echo "<body>\n";
      echo "\t<h1>ACCESS DENIED</h1>\n";
      echo "</body>\n";
      echo "</html>\n";
   }
}
else
{
   echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=./write/\">\n";
   echo "</head>\n";
   echo "</html>\n";
}
?>
