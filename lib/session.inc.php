<?php
/**
 *
 * session.inc.php
 *
 * Common session handling for all files in the project
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */

// Session: stored session data in cookies, ignore URLS
global $sess_name, $sess_time;
$logger=Logger::getInstance();

if ( ! session_id() ) {    // is there already a session?
  # Php session handling:
  ini_set('session.auto_start', 0);
  ini_set('session.use_cookies', 1);
  ini_set('session.use_only_cookies', 1);
  ini_set('session.gc_maxlifetime', $sess_time);  // 60min=1h
  ini_set('session.save_handler', 'files');  // 'user' does not work
  session_name($sess_name);
  session_start();       // resume or start the session
  #$_SESSION['sql_auth']=$sql_auth;         // globals, TBD
  #$_SESSION['drupal_auth']=$drupal_auth;
}

  $_SESSION['created'] = TRUE;
  if (!isset($_SESSION['login_data'])){
    $_SESSION['login_data']='Not logged in';     // global
  }
  $logger->debug("session.inc, session_id="   .session_id()   
    .", session_name=" .session_name() .", login_data=" .$_SESSION['login_data']
    #.", uid=" .$_SESSION['uid']
    , 3);

?>
