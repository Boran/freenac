<?php
/**
 * logtaildebug.php
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


  //new GuiLogtail($conf->web_logtail_file,$conf->web_logtail_length);
  new GuiLogtail('/var/log/debug', $conf->web_logtail_length, '');

?>
