<?Php
/**
 * 
 * GuiUserManager.php
 *
 * Long description for file:
 * Class to handle User login, authetication, authorisation
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */



class GuiUserManager extends WebCommon 
{
  private $props=array();      // See also WebCommon and Common


  function __construct()
  {
    parent::__construct();     // See also WebCommon and Common
    $this->logger->setDebugLevel(3);
    // broken $this->logger->setLogToStdErr();
    $this->debug(' __construct()', 3);  
    
  }

  public function isValidUserName ($in_user_name)
  {
    if ($in_user_name=='' or ereg('[^[:alnum:] _-]', $in_user_name) === TRUE)
      return FALSE;
    else
      return TRUE;
  }

  // Kill old sessions
  private function killSession ($userid=0)
  {
    if ($userid > 0) {
        $_SESSION = array();  // Unset all of the session variables
        if (strlen(session_id()) > 0)  {
          session_destroy();
          $this->debug("killSession- session_destroy", 3);
        }
        #TBD $this->clearLogins($userid);
    }
  }

  // Generate & remember new session
  private function newSession ($userid=0)
  {
    global $sess_time;
    global $drupal_auth, $drupal_db;                // config.inc
    $this->debug("newSession() userid=$userid", 3);
    $conn = $this->getConnection();

    if ($userid > 0) {
        // new session
        include('session.inc.php');      // retrieve session
        $this->debug("newSession(): new session timeout/id/name=$sess_time, " 
          .session_id() .", " .session_name());
        $session_id = session_id();

#      $q = <<<EOQ
#INSERT INTO LoggedInUsers(uid, session_id, last_access)
#     VALUES('$userid', '$session_id', NOW())
#EOQ;
      #$this->logger->debug($q, 2);
      #if (! $conn->query($q)  ) {
      #  $this->logger->logit($q .'; error: ' .$conn->error  );
      #  throw new DatabaseErrorException($conn->error);
      #}

      // initialise session data
         $_SESSION['uid']=$userid;
         $_SESSION['username']='';
         $_SESSION['email']   ='';
         $_SESSION['firstname']='';
         $_SESSION['lastname']='';
         $_SESSION['organisation']='';
         $_SESSION['tel']='';
         $_SESSION['address']='';
         $_SESSION['login_data']='';
         $_SESSION['GuiVlanRights']='';

      if ($drupal_auth===true) {
        $this->logger->debug("newSession(): get user values from drupal");
        // find all available fields, then store them in SESSION
        $field_names=array();
        $q="SELECT name from $drupal_db.profile_fields";
        $this->logger->debug($q, 2);
        $results = $conn->query($q);
        if ($results === FALSE) throw new DatabaseErrorException($conn->error);
        while (($row = $results->fetch_assoc()) !== NULL) {
          $field_names[]=$row['name'];
          $this->debug("found fieldname   {$row['name']}",  3);
        }

        foreach ($field_names as $field_name) {

          $q=<<<TXT
SELECT title, name, pv.value 
  FROM $drupal_db.profile_fields pf left join $drupal_db.profile_values pv on pf.fid=pv.fid 
  WHERE name='{$field_name}' AND uid={$userid};
TXT;
          $this->debug($q, 2);
          $results = $conn->query($q);
          if ($results === FALSE) throw new DatabaseErrorException($conn->error);
          while (($row = $results->fetch_assoc()) !== NULL) {
            $_SESSION[$field_name]=$row['value'];
            $this->debug("Store SESSION {$field_name}={$row['value']}",  1);
          }
        }


        $q="select * from $drupal_db.users WHERE uid={$userid}";
        $this->debug($q, 2);
        $results = $conn->query($q);
        if ($results === FALSE) throw new DatabaseErrorException($conn->error);
        while (($row = $results->fetch_assoc()) !== NULL) {
         $_SESSION['username']=$row['name'];
         $_SESSION['mail']=$row['mail'];
        }

        // save for later
        $_SESSION['uid']=$userid;
        $_SESSION['login_data']=$_SESSION['profile_forename'] 
          .' ' .$_SESSION['profile_familyname'] ;


      } else {    // freenac user table, not drupal

        $q = "SELECT * FROM users WHERE id = '$userid' LIMIT 1";
        $this->debug($q, 2);
        $results = $conn->query($q);
        if ($results === FALSE) throw new DatabaseErrorException($conn->error);
        while (($row = $results->fetch_assoc()) !== NULL) {
          $_SESSION['uid']=$userid;
          $_SESSION['db_name']='opennac';
          $_SESSION['username']=$row['username'];
          $_SESSION['email']   =$row['rfc822mailbox'];
          $_SESSION['firstname']=$row['GivenName'];
          $_SESSION['lastname']=$row['Surname'];
          $_SESSION['organisation']=$row['Department'];
          $_SESSION['nac_rights']=$row['nac_rights'];
          $_SESSION['GuiVlanRights']=$row['GuiVlanRights'];
          $_SESSION['login_data']=$_SESSION['firstname'] .' ' .$_SESSION['lastname'] ;

          if ($_SESSION['nac_rights']>=1) {
            if ($_SESSION['nac_rights'] == 99)
              $_SESSION['nac_rights_text']="administrator";
            else if ($_SESSION['nac_rights'] == 2)
              $_SESSION['nac_rights_text']="edit";
            else
              $_SESSION['nac_rights_text']="read-only";
          }
        }
      }
      
      if ($this->logger->getDebugLevel()>2) {
        print_r($_SESSION);
        #var_dump($_SESSION);
      }
    }

  }


  // Removing possibly dangerous characters
  private function super_escape_string ( $in_string, $in_conn, $in_removePct = FALSE)
  {
    return( $this->real_escape_string1($in_string, $in_conn, $in_removePct) );
    #$this->logger->debug("super_escape_string", 3);
    #$str = $in_conn->real_escape_string($in_string);
    #if ($in_removePct)
    #  $str = ereg_replace('(%)', "\\\1'", $str);
    #$this->logger->debug("super_escape_string: ret=$str", 3);
    #return $str;
  }

  // - get db connection
  // - verify that username and password are valid
  // - clear out existing login information for user. (if any)
  // - log user into table (associate SID with user name).
  //
  public function processLogin($in_user_name, $in_user_passwd)
  {
    $this->debug("processLogin", 2);
    #echo "processLogin <br>";
    // 1. internal arg checking.
    if ($in_user_name == '' || $in_user_passwd == '')
      throw new InvalidArgumentException();

    try
    {
      $conn = $this->getConnection();

      // confirmUser also validates that the
      // username and password are secure and are not
      // attempts at SQL injection attacks ...
      // Throws an InvalidLoginException if
      // the username or password is not valid.
      $userid = $this->confirmUser($in_user_name, $in_user_passwd, $conn);

      $this->killSession($userid);
      $this->newSession ($userid);

    }
    catch (Exception $e) {
      #if (isset($conn))
      #  $conn->close();
      throw $e;
    }
    // our work here is done.  clean up and exit.
    $conn->close();
  }


  // - get db connection
  // - verify that username and password are valid
  // - clear out existing login information for user. (if any)
  // - log user into table (associate SID with user name).
  //
  public function processAdLogin()
  {
    $this->debug("processAdLogin", 2);
    try
    {
      $conn = $this->getConnection();
      $userid = $this->confirmAdUser($conn);
      $this->killSession($userid);
      $this->newSession ($userid);
    }
    catch (Exception $e) {
      #if (isset($conn))
      #  $conn->close();
      throw $e;
    }
    // our work here is done.  clean up and exit.
    $conn->close();
  }



  private function confirmAdUser( $in_db_conn = NULL)
  {
    $userid=-1;     # invalid
    $this->debug("confirmAdUser()", 3);


    if (!isset ($_SERVER['PHP_AUTH_USER']) || !$_SERVER['PHP_AUTH_USER']) {
      throw new InvalidLoginException("PHP_AUTH_USER not set");
    }

    // 1. make sure we have a database connection.
    if ($in_db_conn==NULL) $conn=$this->getConnection(); else $conn=$in_db_conn;

    try {
      // 2. make sure incoming username is safe for queries.
      $uname = $this->super_escape_string($_SERVER['PHP_AUTH_USER'], $conn);

      #$this->debug("Checking user $uname in NAC users table  ..", 2);
      $q = <<<EOQ
SELECT * FROM users WHERE username = '$uname'
EOQ;
      $this->debug($q, 3);
      $results = @$conn->query($q);
      if ($results === FALSE)
        throw new DatabaseErrorException($conn->error);

      // 4. re-confirm the name and the passwords match, get userid
      while (($row = $results->fetch_assoc()) !== NULL)
      {
            $userid = $row['id'];
            break;    // just take first match?
      }
      $results->close();

    } catch (Exception $e) {
      if ($in_db_conn === NULL and isset($conn))
        $conn->close();
      throw $e;
    }

    // only clean up what we allocated.
    if ($in_db_conn === NULL)
      $conn->close();

    $this->debug("userid=$userid", 3);
    // throw on failure, or return the user ID on success.
    if ($userid==-1)
      throw new InvalidLoginException("user=$uname");

    return $userid;
  }


  //
  // - internal arg checking
  // - get a connection
  // - get the record for the username.
  // - verify the password
  //
  private function confirmUser( $in_uname, $in_user_passwd, $in_db_conn = NULL)
  {
    global $drupal_auth, $drupal_db, $sql_auth, $ad_auth;   // config.inc
    $this->debug("confirmUser()", 3);
    #echo "confirmUser <br>";
    $login_ok = FALSE;
    $userid=-1;     # invalid

    // 1. make sure we have a database connection.
    if ($in_db_conn==NULL) $conn=$this->getConnection(); else $conn=$in_db_conn;

    try {
      // 2. make sure incoming username is safe for queries.
      $uname = $this->super_escape_string($in_uname, $conn);

      // 3. get the record with this username
      //    either from users, or drupal users
      if ($drupal_auth===true) {
        $this->debug("Checking user in drupal DB ..", 2);
        $q = <<<EOQ
SELECT uid,pass FROM $drupal_db.users
 WHERE name = '$uname'
EOQ;
        
      } else if ($sql_auth===true) {
        $this->debug("Checking user in NAC users table  ..", 2);
        $q = <<<EOQ
SELECT * FROM users
 WHERE username = '$uname'
EOQ;
      } else if ($ad_auth===true) {
        $this->debug("Checking user in NAC users table  ..", 2);
        $q = <<<EOQ
SELECT * FROM users
 WHERE username = '$uname'
EOQ;
      }

      $results = @$conn->query($q);
      if ($results === FALSE)
        throw new DatabaseErrorException($conn->error);

      // 4. re-confirm the name and the passwords match, get userid
      while (($row = $results->fetch_assoc()) !== NULL)
      {
        $this->debug("check $uname and $in_uname", 2);
        if (strcasecmp($uname, $in_uname) == 0) {
          $this->debug("user name match, check password..", 2);

          // good, name matched.  does password?
          if ($ad_auth===true) {   // for AD, don't check password
            $login_ok = TRUE;
            $userid = $row['uid'];

          } else if (md5($in_user_passwd) == $row['pass']) {
            $this->debug("password ok", 2);
            $login_ok = TRUE;
            $userid = $row['uid'];
          }
          else
            $login_ok = FALSE;
          break;
        }
      }
      $results->close();

    } catch (Exception $e) {
      if ($in_db_conn === NULL and isset($conn))
        $conn->close();
      throw $e;
    }

    // only clean up what we allocated.
    if ($in_db_conn === NULL)
      $conn->close();

    // throw on failure, or return the user ID on success.
    if ($login_ok === FALSE)
      throw new InvalidLoginException("user=$uname");

    return $userid;
  }


//
// - get connection
// - delete any rows for this user ID
//
  private function clearLogins( $in_userid, $in_db_conn=NULL)
  {
    $this->debug("clearLogins $in_userid", 2);
    // 0. internal arg checking
    if (!is_numeric($in_userid))
      throw new InvalidArgumentException();

    // 1. make sure we have a database connection.
    if ($in_db_conn==NULL) $conn=$this->getConnection(); else $conn=$in_db_conn;

    try {
      // 2. delete any rows for this user in LoggedInUsers
      $query = <<<EOQ
DELETE IGNORE FROM LoggedInUsers WHERE uid = $in_userid
EOQ;
      $this->debug($query, 2);
      if (! $conn->query($query)  ) {
        throw new DatabaseErrorException($conn->error);
      }
    }
    catch (Exception $e) {
      if ($in_db_conn === NULL and isset($conn))
        $conn->close();
      throw $e;
    }

    // clean up and return.
    if ($in_db_conn === NULL)
      $conn->close();
  }



  //
  // - get connection
  // - make sure the username does not already exist.
  // - add record to users table.
  //
  public function createAccount ( $in_uname, $in_pw, $in_fname, $in_email, 
				  $in_year, $in_month, $in_day)
  {
    // 0. quick input validation
    if ($in_pw == '' or $in_fname == '' or !$this->isValidUserName($in_uname)) {
      throw new InvalidArgumentException();
    }

    // 1. get a database connection with which to work. throws on failure.
    $conn = $this->getConnection();

    try
    {
      // 2. make sure username doesn't already exist.
      $exists = FALSE;
      $exists = $this->userNameExists($in_uname, $in_conn);
      if ($exists === TRUE)
        throw new UserAlreadyExistsException();

      // 3a. make sure the parameters are safe for insertion,
      //      and encrypt the password for storage.
      $uname = $this->super_escape_string($in_uname, $conn);
      $fname = $this->super_escape_string($in_fname, $conn);
      $email = $this->super_escape_string($in_email, $conn);
      $pw = md5($in_pw);

      // 3b. create query to insert new user.  we can be sure
      //     the date values are SQL safe, or the checkdate
      //     function call would have failed.
      $qstr = <<<EOQ
INSERT INTO Users (name,pass,full_name,user_email,birthdate)
     VALUES ('$uname', '$pw', '$fname', '$email', '$in_year-$in_month-$in_day')
EOQ;

      // 3c. insert new user
      $results = @$conn->query($qstr);
      if ($results === FALSE)
        throw new DatabaseErrorException($conn->error);

      // we want to return the newly created user ID.
      $user_id = $conn->insert_id;
    }
    catch (Exception $e) {
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    // clean up and exit
    $conn->close();
    return $user_id;
  }




 // Old
  public function authUser($username, $password)
  {
  	global $connect;
  	debug2("authUser: name=$username, pass=$password");
    $q = "SELECT * FROM `users` WHERE name='$username' AND pass='$password'";
    $r = mysql_query($q, $connect);
    if (mysql_num_rows($r) == 1) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}



?>
