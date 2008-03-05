<?php
/**
 *
 * login.php
 *
 ** @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 * ALPHA
 */



## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  #$logger->setDebugLevel(1);
  $logger->setDebugLevel(3);
  #$logger->debug('Start, uid=' .$_SESSION['uid'], 3);

## Main () ###
  ob_start();
  $result='';
 
  // Check form submit:
  if ( isset($_POST['submit']) ) {             // form submit, check fields
    $logger->debug('username=' . $_POST['username'] .', password=' .$_POST['password'], 3);

    if ( !isset($_POST['username']) || !isset($_POST['password']) ) {
      $logger->debug('Password or username not set');
      $result="<p>Please fill in ALL fields.</p>";

    } else if ( empty($_POST['username']) || empty($_POST['password']) ) {
      $logger->debug('Password or username empty');
      $result= '<font class=text16red>Please fill in both user and password.</font>.';

    } else {

      try {
        $usermgr = new GuiUserManager();
        $usermgr->processLogin($_POST['username'], $_POST['password']);

        $result= 'Login success: '; 
        $logger->logit($result .$_SESSION['username'] .', ' .$_SESSION['login_data'] .', '
          .$_SERVER['REMOTE_ADDR'] .', ' .$_SESSION['organisation'] );
	header('Location: ./ChooseAccount.php');
        exit();

      } catch (InvalidLoginException $ex) {
        $result= '<font class=text16red>Username or Password incorrect</font>.';
      }
      
    }
 }

/*
<div id=login align='centre'>
<div>
*/
?>

<!- create the form with a submit button, note the $_SERVER['PHP_SELF'] ->
<?php echo print_headerSmall(); ?>
<body>
<p/>
<form name="login" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table class=bw width="500" border=0 cellspacing='8' align='centre'>
  <tr><td class=text18 colspan='2'>Please identify yourself</td></tr>
  <tr class=text15><td width='100'>Username:</td> 
      <td><input type="text"     name="username" maxlength="100" /><br /></td></tr>
  <tr class=text15><td width='100'>Password:</td> 
      <td><input type="password" name="password" maxlength="100" /><br /></td></tr>
  <tr>
      <td colspan='2' align='center'>
      <input class=text15 type="submit" name="submit" value="Login" />
</table>
</form>
</body>
<?php echo "<p>" .$result; ?>

