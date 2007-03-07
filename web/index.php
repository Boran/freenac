<html>
<head>
<?php
chdir(dirname(__FILE__));
set_include_path("./:../");

require("./funcs.inc");
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
   echo "\t<title>Access denied</title>\n";
   echo "</head>\n";
   echo "<body>\n";
   echo "<h1>ACCESS DENIED</h1>\n";
   echo "</body>\n";
}
?>
