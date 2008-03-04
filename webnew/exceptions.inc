<?php
/**
 * exceptions.inc
 *
 * This is the default exception handler, thown by the web gui
 * We will log information about the error and send the user to
 * a n approriate error page
 *
 * Parameters:
 *    $in_exception         - the exception that was thrown.
 * Long description for file:
 * Class to handle User login, authetication, authorisation
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: exceptions.inc,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */


## install our custom exception handler.
set_error_handler('app_error_handler');
set_exception_handler('exception_handler');


function error_page($msg)
{
  #echo print_header();
  #<table width='80%' border='1' align='center' >
  #<table border='1' align='center' >
  echo <<<XX
  <table width='80%' border='0' align='center' >
  <tr>
    <td align='center'>
      <font color='red'><b>
      We are sorry, but an error has occurred while executing the application. <br/> 
      The system administrators have been notified.
      </font></b>
      <p>$msg</p>
      <p>
        Please go back to the <a href='./index.php' >Main Menu</a> and start again, or try again later.
      </p>
    </td>
  </tr>
  </table>
XX;

  echo read_footer();
}


function exception_handler($ex)
{
  $logger=Logger::getInstance();
  ## If we already have an error, then do no more.
  if (isset($_SESSION) and (isset($_SESSION['errstr']) or isset($_SESSION['exception']))) {
    return;
  }

  $class = get_class($ex);
  //if ($class == "DatabaseErrorException" ) {
  //  return;   // its already handled seperately
  //}
  $msg = $ex->getMessage();
  $user_msg =$ex->getFile() .' line '  .$ex->getLine();
  error_page($user_msg);

  // log the error: full to syslog, summary to webserver log
  // LOG_ERROR=3
  $logger->logit   ('EXCEPTION: class ' .$class .', line '  .$ex->getLine() 
               . ', '.$ex->getTraceAsString());
  $logger->loghttpd('web1 ERROR: file/line: ' . $ex->getFile() .' ' .$ex->getLine() 
                                        . ' ' . $ex->getCode());

  ## Now try to send the user to a better error page, saving
  ## more helpful information before we go.
  #if (isset($_SESSION)) {
  #  $_SESSION['exception'] = $in_exception;
  #}
  #header('Location: error.php');
}

class DatabaseErrorException extends Exception
{
  function __construct($in1) {
    logit('DatabaseErrorException: ' .$in1);
    error_page("Database error");
    $_SESSION['exception']=true;    // indicate that we've handled it
    parent::__construct("DB: $in1"); 
  }
}


class ArgumentTypeException extends Exception
{
  function __construct($in_type, $in_expected) {
    $_SESSION['exception']=true;    // indicate that we've handled it
    logit("ArgumentTypeException: Expected: $in_expected, received: $in_type");
    parent::__construct("Expected: $in_expected, received: $in_type"); 
  }
}

class ArgumentRangeException extends Exception
{
  function __construct($in_value, $in_bottom, $in_top) {
    $_SESSION['exception']=true;    // indicate that we've handled it
    parent::__construct("Value $in_value was not in the range $in_bottom .. $in_top"); 
  }
}
class InsufficientRightsException extends Exception
{
  function __construct($in1='') {
    logit('InsufficientRightsException: logindata/uid/nac_rights' 
      .$_SESSION['login_data'] .", " .$_SESSION['uid'] .", " .$_SESSION['nac_rights'] 
      .' from ' .$_SERVER['REMOTE_ADDR']);
    $_SESSION['exception']=true;    // indicate that we've handled it
    parent::__construct("Insufficient rights: ". $_SESSION['login_data']); 
  }
}
class InvalidLoginException extends Exception
{
  function __construct($in1='') {
    logit('InvalidLoginException: ' .$in1 .' from ' .$_SERVER['REMOTE_ADDR']);
    $_SESSION['exception']=true;    // indicate that we've handled it
    parent::__construct("Login failed: $in1"); 
  }
}
class InvalidWebInputException extends Exception
{
  function __construct($in1='') {
    #echo print_headerSmall();
    echo print_header();
    error_page("Invalid Argument");
    logit('InvalidWebInputException: ' .$in1);
    $_SESSION['exception']=true;    // indicate that we've handled it
    parent::__construct($in1); 
  }
}



function app_error_handler ( $in_errno, $in_errstr, $in_errfile, $in_errline, $in_errcontext)
{
  $logger=Logger::getInstance();
  ## If we already have an error, then do no more.
  if (isset($_SESSION) and (isset($_SESSION['errstr']) or isset($_session['exception']))) {
    return;
  }

  #error_log(date('c')
  #          . " Unhandled Error ($in_errfile, $in_errline): "
  #          . "$in_errno, '$in_errstr'\r\n", 3, LOGFILE_PATH);
  $logger->logit("app_error_handler: $in_errstr"
            . ", in $in_errfile, line: $in_errline, errno=$in_errno");

  #/**
  # * if we have session information, send the user to more
  # * helpful pastures.
  # */
  #if (isset($_SESSION))
  #{
  #  $_SESSION['errstr'] = "$in_errstr ($in_errfile, line $in_errline)";
  #}
  #header('Location: error.php');
}

?>
