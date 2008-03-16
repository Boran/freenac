<?php
/**
 * index.php
 *
 * Long description for file:
 * Default script for starting the Web GUI
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: index.php,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */


## ----- Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./");
  require_once('webfuncs.inc');
  include 'session.inc.php'; // resume or create session
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1); // 0 to 3 syslog debugging levels
  check_login();             // logged in? User identified?
# --- end of standard header ------

### --------- main() -------------
  $report=new WebCommon(true);       // with header
  $report->logger->setDebugLevel(1);
  #echo main_menu_simple();   // TBD: show the old menu if there is no java script?
  echo <<<EOF
  <img src='./images/logo500.png' border='0' style="padding-left: 30px;"/>
EOF;

  #var_dump($_SESSION);
  echo $report->print_footer(false);
?>
