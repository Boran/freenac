<?php
chdir(dirname(__FILE__));
set_include_path("./:../");

require("./funcs.inc");

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_SAMACCOUNTNAME']);
   if ($rights>=2)
   {
      echo "<html>\n";
      echo "<head>\n";
      echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=./write/\">\n";
      echo "</head>\n";
      echo "</html>\n";
   }
   else if ($rights==1)
   {
      echo "<html>\n";
      echo "<head>\n";
      echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=./read/\">\n";
      echo "</head>\n";
      echo "</html>\n";
   }
   else
   { 
      echo "<html>\n";
      echo "<head>\n";
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
   echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
   echo "<head>\n";
   echo "   <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\n";
   echo "    <title>FreeNAC</title>\n";
   echo "    <link href=\"./bw.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
   echo "</head>\n";
   echo "<body>\n";
   echo "<table class=\"bw\" border=\"0\" align=\"center\" width=\"500\">\n";
   echo "   <tr height=\"80\">\n";
   echo "      <td>&nbsp;</td>\n";
   echo "   </tr>\n";
   echo "   <tr valign=\"top\">\n";
   echo "      <td align=\"center\">\n";
   echo "          <a href=\"./write/index.php\">Write web interface</a><br /><br />\n";
   echo "          <a href=\"./read/index.php\">Read-only web interface</a><br /><br />\n";
   echo "      </td>\n";
   echo "   </tr>\n";
   echo "</table>\n";
   echo "</body>\n";
   echo "</html>\n";
}
?>
