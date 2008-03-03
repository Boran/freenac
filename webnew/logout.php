<?php
/**
 *
 * logout.php
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */

## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

/**
 * main ()
 */
  require_once('./session.inc.php');      // retrieve session
  $login_data=isset($_SESSION['login_data']) ? $_SESSION['login_data'] : '';
  $logger->logit("logged out: $login_data, id=".session_id());

  session_destroy();
  if (isset($_COOKIE[session_name()])) {  // delete cookie
      setcookie(session_name(), '', time()-3600, '/');
  }
  $_SESSION = array();  // Unset all of the session variables

  //TBD: record logout time
  echo print_headerSmall();
  echo "<p class='text16'><b>Session logged out $login_data.</b></p>";
  echo "<a class='text16' href='./login.php'>Login</a>";
?>
