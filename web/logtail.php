<?php

dir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../etc/config.inc');
// include functions
require_once('webfuncs.inc');

function logtail($filename,$length)
{
	if (!$length) { $length=10; };
	$logfile = fopen($filename,'r');
        exec("/usr/bin/tail -n $length $filename", $logtext, $error);

	$text .= "<h1>Logfile : $filename</h1>";
	if ($error){
	    $text .= "Tail Error: $error\n";
	} else {
	    $text .= "\nLast $length lines :\n<p>\n<pre>\n";
	    foreach ($logtext as $logline) {
		$text .= $logline."\n";
	    };
	};
	return($text);
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
   echo logtail($conf->web_logtail_file,$conf->web_logtail_length);
   echo read_footer();
}


?>
