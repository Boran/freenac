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
  include 'session.inc.php'; // resume or create session
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

/**
 * main ()
 */
  $report=new WebCommon(false);
  $report->logger->setDebugLevel(3);
  echo $report->print_headerMin();

  $login_data=isset($_SESSION['login_data']) ? $_SESSION['login_data'] : '';
  $logger->logit ("Web log out: $login_data, id=".session_id());
  $report->loggui("Web log out: $login_data, id=".session_id());

  // killSession
        if (isset($_COOKIE[session_name()])) {  // delete cookie
          setcookie(session_name(), '', time()-3600, '/');
        }
        $_SESSION = array();  // Unset all of the session variables
        if (strlen(session_id()) > 0)  {
          session_destroy();
        }

  echo "<p class='UpdateMsgOK'>Session logged out</p>";
  echo "<a class='Logout' href='./index.php'>> Main Menu</a>";
  echo $report->print_footer(false);
?>
