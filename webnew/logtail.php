<?php
/**
 * logtail.php
 *
 * Long description for file:
 * common PHP functions used by several web GUI scripts
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: logtail.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */


## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

  include 'session.inc.php'; // resume or create session
  check_login();             // logged in? User identified?
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);
## end of standardc header ------

global $ad_auth;

function logtail($filename,$length)
{
  $logger=Logger::getInstance();
	if (!$length) { $length=10; };
	$logfile = fopen($filename,'r');
	// TBD: catch error if file cannot be read, or non existant.
        // TBD: set a variable for the pattern match?
  	$cmd="/usr/bin/tail -n $length $filename | egrep \"vmpsd|postconnect\"";
        $logger->debug($cmd, 3);
        exec($cmd, $logtext, $error);

	$text = "<h2>The last $length lines in the log $filename :</h2>";
	if ($error){
	    $text .= "Tail Error: ($cmd) $error\n";
  	    $logger->logit("Tail Error: ($cmd) $error");
	} 
        else {
	    $text .= "\n<p>\n<pre>\n";
	    foreach ($logtext as $logline) {
		$text .= $logline."\n";
	    };
	    $text .= "</pre>";
	};
	return($text);
}

echo print_headerSmall(false);
if ($ad_auth===true)
{
   if ($_SESSION['nac_rights']>=1) {
     echo logtail($conf->web_logtail_file,$conf->web_logtail_length);
   }
   else {
     echo "<h1>ACCESS DENIED</h1>";
     echo "<p>Please verify the nac_rights for username: <" .$_SESSION['username'] 
      .">.</p>";
     $logger->logit("ACCESS DENIED: verify the nac_rights for username: <" .$_SESSION['username'] .">.</p>");
   }
}
else {       // TBD: test if ad_auth=false
   echo logtail($conf->web_logtail_file,$conf->web_logtail_length);
}
echo read_footer();   

?>
