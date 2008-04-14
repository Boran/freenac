<?php
/**
 * webfuncs.inc
 *
 * Long description for file:
 * common PHP functions used by several web GUI scripts
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id: webfuncs.inc,v 1.1 2008/02/22 13:04:57 root Exp root $
 * @link        http://freenac.net
 *
 */

chdir(dirname(__FILE__));

require_once('../bin/funcs.inc.php');   // include pat set here too
require_once('../etc/config.inc');
require_once "Logger.php";    // in the nac lib
$logger=Logger::getInstance();
  #$logger->setDebugLevel(3);
  #$logger->debug("webfuncs.inc: $dbhost, $dbuser, $dbname.", 3); //TBD
  #print_r($dbhost);

require_once "web1.config.inc";     // TBD: move to /opt/nac/config.inc . note the absolute path!
require_once "exceptions.inc";

define('UA_SALT', 'web1.376654seed');


#### Functions ####
##################

function ismemberof($username,$group)
{
   ## This function says whether a username in the form user@domain.com is belongs to the AD group specified
   global $ad_server,$ad_user,$ad_password,$ad_port,$ad_base;
   $counter=0;
   $ad=ldap_connect($ad_server,$ad_port);
   if ($ad)
   {
      if (ldap_bind($ad,$ad_user,$ad_password))
      {
         if ($ad_port==3268) 		## Global catalog is more flexible. 
                             		## In the event that we have more than one domain, 
                             		## the username gives us the domain name 
                             		## where we should look at.
         {
            ## Build the DN out of the username
            $dn=explode('@',$username);
            if (empty($dn))
               return false;
            $temp_dn=str_replace('.',',DC=',$dn[1]);
            $dn='DC='.$temp_dn;
         }
         else
            ## We are using a domain-level lookup, so in this case we need to know where the user information is 
            $dn=$ad_base;
         $attributes=array('memberOf');  ## We are interested only in the memberOf attribute
         $filter='(&(userPrincipalName='.$username.')(objectClass=person))'; ## Retrieve info only for this specific username
         $result=ldap_search($ad,$dn,$filter,$attributes);   ## Do the search
         if ($result)
         {
            $entries=ldap_get_entries($ad,$result);
            if ($entries['count']==1)    ## We should have only one result
            {
               $groups=$entries[0]['memberof']['count'];
               if ($groups>0)
               {
                  for ($i=0;$i<$groups;$i++)
                  {
                     if (strcasecmp($entries[0]['memberof'][$i],$group) == 0)
                        $counter++;  	## Username is a member of the group
                  }
               }
            }
         }
      }
      else
      {
         echo 'Unable to bind to LDAP server with given credentials';
      }
      ldap_close($ad);
   }
   else
   {
      echo 'Unable to connect to LDAP server';
   }
   if ($counter>0)
      return true;
   else
      return false;
}

function user_rights($username)
#This function outputs the rights associated to the username
{
   global $dbuser,$dbpass;
   db_connect($dbuser,$dbpass);
   $query='select * from guirights order by code desc;';
   $res=mysql_query($query);
   $rights=-1;
   while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
   {
      if ( ! empty($result['ad_group']))
         if (ismemberof($username,$result['ad_group']))
         {
            $rights<$result['code'] ? $rights=$result['code'] : $result['code']=$rights;
            break;
         }
   }
   return $rights;
}

/**
 * Header for each page
 *
 */
global $head_title, $head_right1, $head_right2, $js1;
$header1=<<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	  <title>{$head_title}</title>
	  <link href="bw.css" rel="stylesheet" type="text/css" />
	</head>
TEXT;

//  if (trim(document.getElementById('name').value)== \'\') {
$js1=<<<TEXT
<script type="text/javascript"> 
// TBD: Both of these are stil early prototypes,,,,
//
// Alert if field n on form f is less than l chars
function checkLen(f,len)
{
  //alert(document.formadd.name.value.trim());
  var myform="formadd";  // hard coded to formadd for now; f.form does not work
  if (eval("document." +myform +"." +f.name +".value.trim().length") <len) {
    alert('The value must contain at least ' +len +' characters');
  }
}
function checkForm()
{
        var cname, cmac;
        with(document.formadd) {
                cname  = name;
                cmac   = mac;
        }

        if(trim(cname.value).length == 0) {
                alert('Please enter a name');
                cname.focus();
                return false;
        } else if(trim(cmac.value).length == 0) {
                alert('Please enter a mac');
                cmac.focus();
                return false;
        } else {
                cname.value    = trim(cname.value);
                cmac.value   = trim(cemail.value);
                return true;
        }
}
/* Strip whitespace from the beginning and end of a string
Input : a string
*/
//function trim(str)
String.prototype.trim = function()
{
  return this.replace(/^\s+|\s+$/g, '');
}
</script>
TEXT;


$header2=<<<TEXT
<table class="header2" width="1000" border="0">
  <tr height="80"> 
    <td valign="center">{$head_left1}</td>
    <td align="right">{$head_right1}<p>{$head_right2}</p> </td>
  </tr>
  </table>
  <p/>
TEXT;
// TBD: why will it not table the width from table#header2_small in bw.css?
$header2_small=<<<TEXT
<table id="header2_small" width="500">
  <tr height="80"> 
    <td valign="center">{$head_left1}</td>
    <td align="right">{$head_right1}<p>{$head_right2}</p> </td>
  </tr>
</table>
TEXT;
		
/*
 * Popul a JavaScript alert window 
 */
function jalert($str='') 
{
  return "<script type='text/javascript'>alert('" .$str ."')</script>";
}

// Obsolete: use WebCommon class
function print_header($print_links=true){
   global $header1, $header2, $head_right1, $head_right2;	
   $logger=Logger::getInstance();

   if (defined('HEADER')){   // already displayed?
     $logger->debug('print_header: HEADER already true',2);

   } else {
     if ($print_links===false) {
       $logger->debug('print_header: do not print right links', 3);
       $head_right1='';
       $head_right2='';       
     }
     $ret= $header1 . $header2;
     define('HEADER',true); // The header is out
     $logger->debug('print_header: done', 3);
     return $ret;
   }
}

// Obsolete: use WebCommon class
function print_headerSmall(){
   global $header1, $header2_small;	
   $logger=Logger::getInstance();

   if (defined('HEADER')){   // already displayed?
     $logger->debug('print_header: HEADER already true', 3);

   } else {
     $logger->debug('print_headerSmall() ', 3);
     $ret= $header1 . $header2_small;
     define('HEADER',true); // The header is out
     return $ret;
   }
}

//
// Print page footer (if not already done)
// Obsolete: use WebCommon class
function print_footer(){
	if(!defined(FOOTER)){
		$ret="</table></body></html>";
		define('FOOTER',true);
		return $ret;
	}
}

// test only, not used
function report_menu()
{
/*
     <li>Sort order: <input type="text" name="sortby" size='30' maxlength="30" /></li>
*/
  $text=<<<EOF
  <div id="reportmenu">
  <ul >
     <li>Max. records: <input type="text" name="sortlimit" size='10' maxlength="20" /></li>
     <li>Sort order: <SELECT NAME=sortby>
      <OPTION LABEL="1.5.x">Order date</OPTION>
      <OPTION LABEL="1.0.x">status</OPTION>
     </SELECT> </li>
  </ul> </div>
EOF;
   return $text;
}

// Obsolete, use WebCommon class
function read_footer()
{
  if (!isset ($_SESSION['login_data'])) {
    $userdata=">> Not logged in <<";
    $text=<<<EOF
  <div align='center'>
  <font class=user_footer>$userdata</font></p>
  </div>
  </tr> </table> </body> </html>
EOF;
  } 
  else {
    $userdata="Logged in as: " .$_SESSION['login_data']
      ." (" .$_SESSION['nac_rights_text'] .")";

    #if (($_SESSION['sql_auth']===true) || ($_SESSION['drupal_auth']===true)) {
    #  $logout_button="<li><a href='./logout.php'>Log out</a></li>";
    #}
    #else {
      $logout_button='';
    #}

    $text=<<<EOF
  <div align='center'>
  <font class=user_footer>$userdata</font></p>
  </div>
  <div id="headermenue">
  <ul>
     <li><a href="./index.php">Main Menu</a></li>	
     $logout_button
  </ul> </div>
  </tr> </table> </body> </html>
EOF;
     #<li><a href="./ChooseAccount.php">Change Account</a></li>
  }
  return $text;
}

// Obsolete, use WebCommon class
function read_footer2()
{
  if (!isset ($_SESSION['login_data'])) {
    $userdata=">> Not logged in <<";
    $text=<<<EOF
  <p>  </p>
  <hr size='500'><p>  </p>
  <p ALIGN=CENTER class=text16> 
  <font class=user_footer>$userdata</font></p>
  </td>
   </tr> </table> </body> </html>
EOF;
  } else {
    $userdata="Welcome " .$_SESSION['login_data'];
    $text=<<<EOF
  <p>  </p>
  <hr size='500'><p>  </p>
  <p ALIGN=CENTER class=text16> 
  <a href="./index.php">Main Menu</a> &nbsp&nbsp|&nbsp&nbsp 
  <a href='./logout.php'>Log out</a>  &nbsp&nbsp|&nbsp&nbsp
  <font class=user_footer>$userdata</font></p>
  </td>
   </tr> </table> </body> </html>
EOF;
  #<a href="./ChooseAccount.php">Change Account</a> &nbsp&nbsp|&nbsp&nbsp 
  }
  return $text;
}

function user_tooltip($username) {
  global $readuser,$readpass;
  db_connect($readuser,$readpass);
  $query = "SELECT * FROM users WHERE username = '$username'; ";
  $res = mysql_query($query)  or die ("Unable to query MySQL");

  $user = mysql_fetch_array($res);

  //$text = $user['GivenName'].' '.$user['Surname'].', '.$user['Department']." (";
  $text .= $user['HouseIdentifier'].' '.$user['PhysicalDeliveryOfficeName']." / ";

  // Shorter tel number for INO
  if (stristr($user['TelephoneNumber'],'31-342')) {
    $user['TelephoneNumber'] = str_replace(' ','',substr($user['TelephoneNumber'],strlen($user['TelephoneNumber'])-7,7));
  };

  $text .= $user['TelephoneNumber']. " / ";
  $text .= $user['Mobile'];
  //."\n";

  return($text);
};

//
// Print the lookup results
//
function print_resultset($res,$server){
        $ret='';
        while ($row=mysql_fetch_array($res)){
                $ret.=($i%2==0)?'<tr class="light">':'<tr class="dark">';
                
                $ret.='<td><a href="'.$server['PHP_SELF'].'?action=edit&id='.$row['id'].'">';
                // if we have a name, echo the name, else just the id
                $ret.=($row['name']!=''?stripslashes($row['name']):$row['Jcard_no']);
                $ret.='</a></td>'."\n";
                $ret.='<td class="center">'.$row['Cust_part'].'</td>'."\n";                
                $ret.='<td class="center">'.$row['Print_ref'].'</td>'."\n";                
                $ret.='<td class="center">'.$row['Film_or_bags'].'</td>'."\n";
                $ret.='<td class="center">'.$row['Film_col'].'</td>'."\n";  
                $ret.='<td class="center">'.$row['Quantity'].'</td>'."\n";                                                                
                $ret.='<td class="center">'.$row['Ord_contact'].'</td>'."\n";                                                                
                $ret.='</tr>'."\n";
                $i++;
        }
        return $ret;
}

//
// validates webinput: carefully remove 'dangerous'  input
// if the variable is an array recursevly call the
// function for each value
//
function validate_webinput($value){
        if (is_array($value)){
                array_map('validate_webinput',$value);
        }
        else {
                if (get_magic_quotes_gpc()) {  // are quotes escaped already?
                        #$value = @stripslashes($value);
                        $value = stripslashes($value);
                }
                if (!is_numeric($value)){
			// prepends backslashes to: \x00, \n, \r, \, ', " and \x1a
                        #$value = @mysql_real_escape_string($value);
                        #$value = mysqli_real_escape_string($value);
                       $value = ereg_replace('(\'|;)', '\\\1', $value);  // escape ',;
                }
        }
        return trim($value);
}

/* 
 * not yet tested 
 * This method takes three parameters
 * 1. the string to escape, 
 * 2. (optional) indicating whether any markup element characters such as < or > 
 * should be escaped
 * 3. (optional) indicating whether percent symbols (%) should be stripped out.
 */
function mega_escape_string ($in_string, $in_markup = FALSE, $in_removePct = FALSE)
{
  $str = ereg_replace('(\')', '\\\1', $in_string);  // escape '
  if ($in_removePct)
    $str = ereg_replace('(%)', '\\\1', $str);       // escape %
  if ($in_markup == TRUE) {
    $str = htmlspecialchars($str, ENT_NOQUOTES, 'UTF-8');
  }

  return $str;
}




//
// retrieves all users from the databases and returns them as a hmtl dropdownlist
//
function get_userdropdown($selected_user) {
        global $rights;
        if ($rights>=2)
        {
           $ret='<select name="username">';
           $sql='SELECT id, username, surname, givenname FROM users ORDER BY surname'; // Get details for all users
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($r['id']==$selected_user?'selected="selected"':'').'>'.$r['surname'].' '.$r['givenname'].'</option>'."\n";
                }
           }
           $ret.='</select>'."\n";
        }
        else
        {
           $sql="select username from users where id='$selected_user';";
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0)
                while ($r=mysql_fetch_array($res))
                   $ret=$r['username'];
        }
        return $ret;
}

//
// retrieves all offices from the database and returns them as a html dropdownlist
//
function get_officedropdown($oid){
        $ret='<select name="office">';
        $sql='SELECT loc.id, loc.name as office, b.name as building FROM location as loc LEFT JOIN building as b on loc.building_id=b.id ORDER BY building, office'; // Get details for all offices
        $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
        if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($r['id']==$oid?'selected="selected"':'').'>'.$r['building'].' -  '.$r['office'].'</option>'."\n";
             }
        }
        $ret.='</select>'."\n";
        return $ret;
}

//
// retrieves the different status and returns them as a html dropdownlist
//
function get_status($s)
{
        global $rights;
        if ($rights>=2)
        {
           $ret='<select name="status">';
           $sql='SELECT id, value FROM vstatus ORDER BY value ASC;';
           $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
           if (mysql_num_rows($res)>0){
                while ($r=mysql_fetch_array($res)){
                        $ret.='<option value="'.$r['id'].'" '.($s==$r['id']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
                }
           }
           $ret.='</select>'."\n";
        }
        else
        {
          $sql="select value from vstatus where id='$s';";
          $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
          if (mysql_num_rows($res)>0)
                while ($r=mysql_fetch_array($res))
                   $ret=$r['value'];
        }
        return $ret;
}


//
// creates an excel sheet and sends it to the browser
// input: $result (must be a mysql query result)
//
function create_xls($result){
        $fields = mysql_num_fields($result);
        // create a new workbook
        $xls = new Spreadsheet_Excel_Writer();

        // format head row
        $head =& $xls->addFormat();
        $head->setBold();
        $head->setAlign('center');

        // send HTTP headers
        $xls->send('freenac.xls');

        // create a worksheet
        $sheet =& $xls->addWorksheet('FreeNAC');

        // iterate trough the result set
        $r=0; // row
        $c=0; // column
        for ($c=0; $c < $fields; $c++) {
                $sheet->writeString(0, $c, ucfirst(mysql_field_name($result, $c)), $head);
        }
        while ($row=mysql_fetch_row($result)){
                $r++;
                for ($c=0; $c < $fields; $c++) {
                        $sheet->write($r, $c, $row[$c]);
                }
        }
        // send the file
        $xls->close();
}



#---------------------------------
$big_sel = "SELECT s.id as sid, inventory as inventory, s.mac as mac, v.default_id as vlan, v.default_name as vlanname, vstatus.value as status,
	s.name as ComputerName, u.username as user, s.comment, s.ChangeDate, LastSeen, LastPort, sysbd.name as building, 
	CONCAT(sysbd.name,' ',sysloc.name) as officeSoll, patchloc.name as officeIst,
	pat.outlet as PatchSocket, patchloc.name as PatchCable,
	p.name as port, sw.name as switch,
	s.class as class, c1.value as classname,
	s.class2 as class2, c2.value as class2name,
	os.value as os, os.value as osname, os1.value as os1, os2.value as os2, os3.value as os3
FROM systems s
LEFT JOIN vstatus ON vstatus.id = s.status
LEFT JOIN vlan v ON v.id = s.LastVlan
LEFT JOIN port p ON p.id = s.LastPort
LEFT JOIN switch sw ON sw.id = p.switch
LEFT JOIN patchcable pat ON pat.port = p.id
	LEFT JOIN location as patchloc ON patchloc.id = pat.office
LEFT JOIN users u ON u.id = s.uid
LEFT JOIN sys_class c1 ON c1.id = s.class
LEFT JOIN sys_class2 c2 ON c2.id = s.class2
LEFT JOIN sys_os os ON os.id = s.os
LEFT JOIN sys_os1 os1 ON os1.id = s.os1
LEFT JOIN sys_os2 os2 ON os2.id = s.os2
LEFT JOIN sys_os3 os3 ON os3.id = s.os3
LEFT JOIN location sysloc ON sysloc.id = s.office
	LEFT JOIN building sysbd ON sysbd.id = sysloc.building_id";


function debug_dump()
{
  if (DEBUG_OUTPUT === TRUE) {
    $args = func_get_args();
    $output = '';
    foreach ($args as $arg) {
      if (is_array($arg))
        $output .=  print_r($arg, TRUE);
      else
        $output .= $arg;
    }
  }
}
/*
  function dump_array($in_array)
  {
    echo "<table width='100%' border='0'>\n";

    foreach ($in_array as $key => $val)
    {
      echo "<tr><td>$key</td><td>$val</td></tr>\n";
    }
    echo "</table>\n";
  }

dump_array($_REQUEST);
dump_array($_POST);
dump_array($_GET);
 */



/* If no UID is in the cookie, we abort */
function check_login() {
  $logger=Logger::getInstance();

  if (!isset ($_SESSION['uid']) || !$_SESSION['uid']) {  // have we a session?
    $usermgr = new GuiUserManager();
    //$usermgr->logger->setDebugLevel(3);    // enable for debugging
    $usermgr->processAdLogin();

    #echo print_headerSmall();
    #echo "<font class=text16red>ACCESS DENIED</font><br/><br/>";
    #echo "<font class=text15>Please <a href='./login.php'>Login</a></font>";
    #$logger->logit("check_login(): access denied");
    #exit;
  }
  $logger->debug("check_login() " .$_SESSION['login_data'] .", uid=" .$_SESSION['uid']);

  // Security: if someone manipulating headers? check a hash of the user agent+SEED
  if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = md5($_SERVER['HTTP_USER_AGENT']. UA_SALT);

  } else if ($_SESSION['user_agent'] !=
    md5($_SERVER['HTTP_USER_AGENT']. UA_SALT)) {

    echo print_headerSmall();
    echo "<font class=text16red>ACCESS DENIED: possible security violation</font><br/><br/>";
    echo "<font class=text15>Please <a href='./login.php'>Login</a></font>";
    $logger->debug("check_login(): access denied - user_agent mismatch " .$_SESSION['user_agent']);
    exit;
  }

  // is there a valid session at all?
  if  (session_name()=='')  {
    $logger->debug("empty session_name ");
  }
  if  (session_id()=='')  {
    $logger->debug("empty session_id ");
  }
  if ( !isset($_SESSION))  {
    $logger->debug("_SESSION not set");
  }
  #if ( (session_name()=='') || (!isset($_SESSION)) || (session_id()=="") ) {
  #  $logger->debug("empty session uid=" . $_SESSION['uid']);
    #TBD header('Location: ./logout.php');
  #}
}



/**
 * main_menu
 * use bw.css for formatting
 */
function main_menu_simple()
{
   $text=<<<EOF
         <p>  </p>
         <div id=menu1>
         <h3>End-device administration</h3>
         <ul>
            <li><a href="unknowns.php" title="List unknown end devices and print/edit/delete them">Find unknowns</a></li>
            <li><a href="find.php"  title="List end devices recently seen and print/edit/delete them">Find recent </a></li>
            <li><a href="listall.php"  title="List end devices with lots of detail">Detailed list</a></li>
            <li><a href="deviceadd.php" title="Add a new end device to the database">Add end-device</a></li>
         </ul>

         <h3>Reporting</h3>
         <ul>
            <li><a href="stats.php" title="Port usage, End devices per Vlan/Class/Operating System,
        Anti-virus status/Windows Update Errors">Statistics & Graphs</a></li>
            <li><a href="hubs.php" title="List ports with more than one end-device">Hub finder</a></li>
         </ul>
         <ul>
            <li>Switch port diagrams:</li>
            <li><a href="graphswitch.php" title="Graphs cables/ports recently used for one switch">single switch</a></li>
            <li><a href="graphswitchall.php" title="Graphs cables/ports recently used for all switches">all switches</a></li>
         </ul>
         <h3 title="View current FreeNAC settings">Configuration</h3>
         <ul>
            <li><a href="port.php" title="Switch port naming, usage, configuration">Switch-Port</a></li>
            <li><a href="switch.php" title="Switch names/IPs/settings">Switches</a></li>
            <li><a href="patchcable.php" title="Document of cable between switch ports and end devices">Patch cables</a></li>
         </ul>
         <ul>
            <li>Key settings:</li>
            <li><a href="config.php" title="Main configuration table">Config</a></li>
            <li><a href="vlan.php"   title="Definiton of vlan names, numbers">Vlans</a></li>
            <li><a href="vlanswitch.php" title="Non standard vlan on selected switches">Vlan exceptions</a></li>
         </ul>
         <ul>
            <li><a href="user.php">Users</a></li>
            <li><a href="location.php">Locations</a></li>
            <li><a href="nmapsubnet.php">Subnets </a></li>
            <li><a href="class1.php">Device Class1</a></li>
            <li><a href="class2.php">Class2</a></li>
         </ul>
         <h3>Monitoring</h3>
         <ul>
            <li><a href="phpsysinfo/">Server information</a></li>
         </ul>
         <ul>
            <li>DB history:</li>
            <li><a href="loggui.php">GUI change log</a></li>
            <li><a href="logserver.php">Server summary log</a></li>
         </ul>
         <ul>
            <li>Syslog:</li>
            <li><a href="logtail1.php">Message log</a></li>
            <li><a href="logtaildebug.php">Debug Log</a></li>
         </ul>

         </div>
EOF;
   return $text;
}



/*
 * main_meun()
 * with new-style drop down menus
 * TBD: implement this as a reusable claas!
*/
function main_menu()
{
  global $logger, $_SESSION;
  if (isset($_SESSION['nac_rights']) && ($_SESSION['nac_rights']>1)) {
    $edit_mode=true;
  } else {
    $edit_mode=false;
    $logger->debug("main_menu() nac_rights not set, or <2", 2);
  }
  #var_dump($_SESSION);
  #$logger->debug("main_menu()");
  #$text="{$_SESSION['nac_rights']}";
  $text='';

  $text.=<<<EOF
<script type="text/javascript"> <!--
function showHideLayers() {
  var i,p,v,obj,args=showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3)
  with (document) if (getElementById && ((obj=getElementById(args[i]))!=null)) { 
    v=args[i+2];
    if (obj.style) { 
      obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; 
    }
    obj.visibility=v; 
  }
}
//--> </script>

<table id="menu0">
<tbody>
<tr>

<td><div class="smenutitle" 
  onmouseover="showHideLayers('smenu_device1','','show')"
  onmouseout ="showHideLayers('smenu_device1','','hide')">
  <a >End-Devices</a>
  <div class="smenu" id="smenu_device1">
    <a href="unknowns.php" title="List unknown end devices and print/edit/delete them">Find unknowns</a><br/>
    <a href="find.php"  title="List end devices recently seen and print/edit/delete them">Find recent </a><br/>
    <a href="listall.php" title="List end devices with lots of detail">Detailed list</a><br/>
    <a href="mydevs.php"  title="List My end devices: attributed to My username">My Devices</a><br/>
EOF;

//   if ($edit_mode)    // edit/admin
     $text.=<<<EOF
    <a href="deviceadd.php?action=Add"  title="Add a new End-Device/PC to the Database">Add new End-Device</a><br/>
EOF;

   $text.=<<<EOF
  </div>
</div></td>
EOF;

   if ($edit_mode)    // edit/admin
     $text.=<<<EOF
<td><div class="smenutitle"
  onmouseover="showHideLayers('smenu_stats1','','show')"
  onmouseout ="showHideLayers('smenu_stats1','','hide')">
  <a >Reports</a>
  <div  class="smenu" id="smenu_stats1">
     <a href="stats.php" title="Port usage, End devices per Vlan/Class/Operating System, Anti-virus status/Windows Update Errors">Statistics & Graphs</a><br/>
     <a href="hubs.php" title="List ports with more than one end-device">Hub finder</a><br/>
     <a href="graphswitch.php" title="Graphs cables/ports recently used for one switch">Switch port diagram: single switch</a><br/>
     <a href="graphswitchall.php" title="Graphs cables/ports recently used for all switches">Switch port diagram: all switches</a><br/>
  </div>
</div></td>
EOF;

   $text.=<<<EOF
<td><div class="smenutitle"
  onmouseover="showHideLayers('smenu_config','','show')"
  onmouseout ="showHideLayers('smenu_config','','hide')">
  <a >Configuration</a>
<div class="smenu" id="smenu_config">

   <a href="port.php" title="Switch port naming, usage, configuration">Switch-Port</a><br/>
   <a href="switch.php" title="Switch names/IPs/settings">Switches</a><br/>
            <a href="config.php" title="Main configuration table">Main Configuration</a><br/>
            <a href="vlan.php"   title="Definiton of vlan names, numbers">Vlans</a><br/>
   <a href="patchcable.php" title="Document of cable between switch ports and end devices">Patch cables</a><br/>
            <a href="vlanswitch.php" title="Non standard vlan on selected switches">Vlan exceptions</a><br/>
            <a href="user.php">Users</a><br/>
            <a href="location.php">Locations</a><br/>
            <a href="nmapsubnet.php">Subnets </a><br/>
            <a href="class1.php">Device Class1</a><br/>
            <a href="class2.php">Class2</a></li>
</div>
</div></td>
EOF;

   $text.=<<<EOF
<td><div class="smenutitle"
  onmouseover="showHideLayers('smenu_mon','','show')"
  onmouseout ="showHideLayers('smenu_mon','','hide')">
  <a >Monitoring</a>
<div class="smenu" id="smenu_mon">
            <a href="logserver.php">Server summary log</a><br/>
            <a href="loggui.php">GUI change log</a><br/>
            <a href="logtail1.php">Syslog Message log</a><br/>
            <a href="logtaildebug.php">Syslog Debug Log</a><br/>
            <a href="phpsysinfo/">Server status</a><br/>
</div>
</div></td>
EOF;

   $text.=<<<EOF
<td><div class="smenutitle"
  onmouseover="showHideLayers('smenu_help','','show')"
  onmouseout ="showHideLayers('smenu_help','','hide')">
  <a >Help</a>
<div class="smenu" id="smenu_help">
  <a href="http://freenac.net/en/community?q=en/usersguide">Users Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/installguide">Install Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/techguide">Technical Guide</a><br/>
  <a href="http://freenac.net/en/community?q=en/support/faqs">FAQ</a><br/>
  <a href="http://freenac.net/phpBB2/">FreeNAC Forum</a><br/>
  <a href="http://freenac.net/en/community">FreeNAC Website</a><br/>
</div>
</div></td>

</tr>
</tbody>
</table>
EOF;
#<td><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><td>

   return $text;
}


