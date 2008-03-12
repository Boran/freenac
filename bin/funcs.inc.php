<?php
/**
 * /opt/nac/bin/funcs.inc.php
 *
 * common PHP functions used by several scripts
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */


/**
* Common PHP functions used by several scripts
*/

chdir(dirname(__FILE__));
set_include_path("./:../lib/:../");

/**
* Load automagically a file containing the class specified by classname
* @param object $classname	Class to load
*/
function __autoload($classname)
{
   if (file_exists("../lib/$classname.php"))
      require_once "../lib/$classname.php";
   else if (file_exists("../enterprise/$classname.php"))
      require_once "../enterprise/$classname.php";
}

/**
* Get configuration variables from config file
*/
require_once 'etc/config.inc';

/**
* @global object $conf		Contains configuration parameters from the config table
* @global object $logger	Provides for logging facilities
*/
$logger=Logger::getInstance();
$conf=Settings::getInstance();

/**
* Tell if an IPv4 address is valid (well-formed)
* @param string $ip	IP address to test
* return boolean	True if IP address is valid, false otherwise
*/
function valid_ip($ip)
{
   $tmp = explode(".", $ip);
   if (count($tmp) != 4)
   {
      return false;
   }
   else
   {
      foreach($tmp AS $sub)
      {
         if (!preg_match("/^([0-9]{1,3})$/", $sub))
         {
            return false;
         }
         if (($sub < 1) || ($sub > 255))
         {
            return false;
         }
      }
   }
   return true;
}

/**
* Get WINS Name from IP Address
* Original contribution from johnboy68
* @param string $ip   Ip Address
* @return string      WINS Name
*/
function getwinsfromip($ip)
{
   #Try to avoid command injection
   $ip = ereg_replace("[|&;`]", "", $ip);
   if (! valid_ip($ip))
   {
      return false;
   }
   /*
   Successful query
   
   added interface ip=192.168.201.216 bcast=192.168.201.255 nmask=255.255.255.0
   Socket opened.
   Looking up status of 192.168.202.222
        HOST59          <00> -         M <ACTIVE>
        WORK            <00> - <GROUP> M <ACTIVE>
        HOST59          <20> -         M <ACTIVE>
        WORK            <1e> - <GROUP> M <ACTIVE>
        WORK            <1d> -         M <ACTIVE>
        ..__MSBROWSE__. <01> - <GROUP> M <ACTIVE>

        MAC Address = CC-00-FF-EE-EE-EE
   */

   /*
   Failed query
   
   added interface ip=192.168.201.216 bcast=192.168.201.255 nmask=255.255.255.0
   Socket opened.
   Looking up status of 192.168.202.223
   No reply from 192.168.202.223
   */

   /*
   Case where we are trying to lookup the hostname of the server running this script

   added interface ip=192.168.201.216 bcast=192.168.201.255 nmask=255.255.255.0
   Socket opened.
   Looking up status of 192.168.202.216
   No reply from 192.168.202.216   
   */

   #Call nmblookup for this ip address
   $command = "nmblookup -A $ip";
   $output = shell_exec($command);
   $foutput = explode("$ip",$output); // all after the IP
   $foutput = explode(" ",$foutput[1]);    // fields sep. by spaces
   $foutput = trim($foutput[0]);           // get first filed, i.e. STNS59 above
   # Check if we have a result;
   if ($foutput=="No")
   {
      #No result, return IP address
      return mysql_escape_string($ip);
   }
   else if (strpos($ip,"bcast="))
   {
      return mysql_escape_string($ip);
   }
   else
   {
      #Return the hostname we've just learnt
      return mysql_escape_string($foutput);
   }
}

/**
* Converts a vlan id to a vlan name
* @param integer $vlanID 	Vlan ID
* @return mixed 		Vlan name
*/
function vlanId2Name($vlanID) {
   // Todo: Proper Error Handling, and use better Database abstraction
   $vlan_name = NULL;
   if (is_numeric($vlanID))
   {
      $vlan_name=v_sql_1_select("select default_name from vlan where id='$vlanID' limit 1");
      if ($vlan_name)
         return $vlan_name;
      else
         return '--NONE--';
   }
   else
   {
      return '--NONE--';
   }
}

/**
* Get the last number of an SNMP OID
* The OID is separated by dots and we use them as a separator.
* Example: OID=1.2.3.4.5.6.7.8
*	   Returns: 8
* @param mixed $oid		OID of interest
* @return mixed			Last part of the OID. 
*/
function get_last_index($oid)                                                  
{
   if ( ! $oid )
      return false;
   $temp=explode('.',$oid);
   return $temp[count($temp)-1];
}

/**
* Returns the difference between 2 dates in secs
* @param mixed $date1			Date to substract from
* @param mixed $date2			Date
* @return mixed				Difference in second between those 2 dates
*/
function time_diff($date1,$date2)
{
   if ($date1 && $date2)
   {
      $temp=explode(' ',$date1);
      //Return false if there are no spaces in $date1
      if (count($temp) == 1)
         return false;
      $time_info_1=explode(':',$temp[1]);
      //Return false because the date format is not what we expected
      if ( strcmp($time_info_1[0],$temp[1]) ===0 )
         return false;
      $date_info_1=explode('-',$temp[0]);
      //Return false because the date format is not what we expected
      if ( strcmp($date_info_1[0], $temp[0]) === 0 )
         return false;
      $temp=explode(' ',$date2);
      //Return false if there are no spaces in $date2
      if (count($temp) == 1)
         return false;
      $time_info_2=explode(':',$temp[1]);
      //Return false because the date format is not what we expected
      if ( strcmp($time_info_2[0],$temp[1]) ===0 )
         return false;
      $date_info_2=explode('-',$temp[0]);
      //Return false because the date format is not what we expected
      if ( strcmp($date_info_2[0], $temp[0]) === 0 )
         return false;
      $time1=mktime((int)$time_info_1[0],(int)$time_info_1[1],(int)$time_info_1[2],(int)$date_info_1[1],(int)$date_info_1[2],(int)$date_info_1[0]);
      $time2=mktime((int)$time_info_2[0],(int)$time_info_2[1],(int)$time_info_2[2],(int)$date_info_2[1],(int)$date_info_2[2],(int)$date_info_2[0]);
      if ( ($time1 !== false) && ($time2 !== false) )
      {
         $time=$time2-$time1;
         return $time;
      }
      else
      {
         //Invalid arguments
         return false;
      }
   }
   else
   {
      //No dates were specified
      return false;
   }
}

/**
* Wrapper around the debug method part of the logger object.
* Logs to debug level 1
* It will be soon depreciated. Present only for backwards compatibility.
* @param mixed $msg Message to log
*/
function debug($msg) {
   global $logger;
   $msg=rtrim($msg);
   if (strlen($msg)>0) {
      $logger->debug($msg);
   }
} 

/**
* Wrapper around the debug method part of the logger object.
* Logs to debug level 1
* It will be soon depreciated. Present only for backwards compatibility.
* @param mixed $msg	Message to log
*/
function debug1($msg) {
  global $logger;
  $msg=rtrim($msg);
  if (strlen($msg)>0) {
     $logger->debug($msg);
  }
}

/**
* Wrapper around the debug method part of the logger object.
* Logs to debug level 2
* It will be soon depreciated. Present only for backwards compatibility.
* @param mixed $msg     Message to log
*/
function debug2($msg) {
  global $logger;
  $msg=rtrim($msg);
  if (strlen($msg)>0) {
     $logger->debug($msg,2);
  }
}

/**
* Wrapper around the logit method part of the logger object.
* It will be soon depreciated. Present only for backwards compatibility.
* @param mixed $msg     Message to log
*/
function logit($msg) {
  global $logger;
  $msg=rtrim($msg);
  $logger->logit($msg);
}

/**
* Write key events to naclog which is visible from the GUI
* This should NOT be called from a secondary server, i.e.
* avoid it in vmpsd_external
* @param mixed $level	Level of severity of the message
* @param mixed $msg	Message to log
*/
function log2db($level, $msg)
{
  if ( $level && $msg )
  {
     global $connect,$logger;
     $msg=rtrim($msg);
     if (strlen($msg)>0 ) {
       db_connect();                 // just in case its not connected
       #$query="INSERT DELAYED INTO naclog "
       $query="insert into naclog set what='".mysql_real_escape_string($msg)."', host='".mysql_real_escape_string($_SERVER['HOSTNAME'])."', priority='".$level."'";
       #$query="INSERT INTO naclog "
       #  . "SET what='" . mysql_real_escape_string($msg )  . "', "
       #  . "host='"     . mysql_real_escape_string($_SERVER["HOSTNAME"]) . "', "
       #  . "priority='$level' ";
       #$logger->logit("$query\n");
       $res = mysql_query($query, $connect);
       if (!$res) 
       { 
          $logger->logit('Cannot write to vmplog table: ' . mysql_error(), LOG_ERR); 
          return false;
       }
       return true;
     }
     else
        return false;
   }
   else
      return false;
  // To view recent entries:
  // select * from naclog ORDER BY datetime DESC LIMIT 5;
}


## log2db3: write to naclog if debug level=3 
function log2db3($msg)
{
  if ($msg)
  {
     global $connect, $logger;
     $level='debug';
     $msg=rtrim($msg);
     if (($logger->getDebugLevel()==3) && (strlen($msg)>0) ) {
        db_connect();                 // just in case its not connected
        #$query="INSERT INTO naclog "
        $query="INSERT DELAYED INTO naclog "
          . "SET what='" . $msg   . "', "
          . "priority='" . $level . "' ";
        #$logger->logit("$query\n");
        $res = mysql_query($query, $connect);
        if (!$res) 
        { 
           $logger->logit('Cannot write to vmplog table: ' . mysql_error(), LOG_ERR); 
           return false;
        }
        return true;
      }
      else
         return false;
   }
   else
      return false;
}

/**
* Creates a connection to the MySQL database with the parameters defined in config.inc
*/
function db_connect()
{
   global $connect, $dbhost, $dbuser, $dbpass, $dbname, $logger;

   if ( ! $connect=@mysql_connect($dbhost, $dbuser, $dbpass))
   {
      $logger->logit("Could not connect to mysql: " . mysql_error(), LOG_ERR);
      exit(1);
   }
   if ( ! @mysql_select_db($dbname, $connect))
   {
      $logger->logit("Could not select database: ".mysql_error(), LOG_ERR);
      exit(1);
   }
}

/**
 * Abstract calling of unix commands.
 * Problem: popen does not pass back command success
 * so syscall cannot say if the command works.
 * @param mixed $command	Command to be executed
 * @return mixed		Result from that command
 */
function syscall($command){
   if ($command)
   {
      $result='';
      #if ( $proc = popen("($command) ","r") ) {
      if ( $proc = popen("($command) 2>&1","r") ) {
          while (!feof($proc))
            $result .= fgets($proc, 1000);
          pclose($proc);
          #debug2("syscall(): executed $command, RETURN=$result");
          return $result;
      #} else {       # will never be reaches, popen does not pass back command success
      #  logit("syscall error ", $proc);
      #  return undef;
      }
      else
         return false;
   }
   else return false;
}

function ping_mac($mac)
# Return: true=Ping successful
{
  db_connect();
  global $connect, $logger;
  
  $query="SELECT r_ip from systems "
        . " WHERE mac='" . $mac . "'";
        #$logger->logit("$query\n");
        $res= mysql_query($query, $connect);
        if (!$res) 
        { 
           $logger->logit('Invalid query: ' . mysql_error(),LOG_ERR); 
           exit(1);
        }

   $rowcount=mysql_num_rows($res);

   if ($rowcount==1) {
     list($ip)=mysql_fetch_array($res, MYSQL_NUM);
     if (strlen($ip)<8) {
       debug2("Invalid IP - $ip for mac $mac");
       return(false); 
     }
     #debug2("ping $ip - $mac");
     logit("ping $ip - $mac");

     // ping for max 1 sec, make sure it does not sty running/hung
     #$answer=syscall("ping -c 1 -w 1 $ip");
     $answer=syscall("ping -c 3 -w 1 $ip");
     syscall("killall ping");
     if ( preg_match("/0 received,/m", $answer) ) {
       #$logger->logit "Ping Error: $answer\n";
       logit("Ping Error no answer: $answer");
       return false;

     } else if ( preg_match("/\d+ received,/m", $answer) ) {
       #$logger->logit "Ping OK\n";
       logit("Ping OK: $answer");
       #logit("Ping OK");
       return true;

     } else {
       #$logger->logit "Ping Error: $answer\n";
       logit("Ping Error: $answer");
       return false;
     }
   } else {   #
     logit("No IP found for $mac");
     return false;
   }
}

/**
* Perform a case insensitive search for a given value in an array and return its key
* @param mixed $str	Value to look for
* @param array $array	Array to look in
* @return mixed		Key for that value, or false otherwise
*/
function array_isearch($str,$array)                    
{
   if ($str && $array)
   {
      if ( ! is_array($array))
         return false;
      foreach($array as $k => $v)
      {
         if (strcasecmp($v,$str)==0)
         {
            return $k;
         }
      }
      return false;
   }
   else
      return false;
}

/**
* Perform a case insensitive search for a given value in a bi-dimensional array and return its key
* @param mixed $str     Value to look for
* @param array $array   Array to look in
* @return mixed         Key for that value, or false otherwise
*/
function array_multi_isearch($str,$array)
{
   if ($str && $array)
   {
      if ( ! is_array($array))
         return false;
      foreach($array as $k)
      {
         if (array_isearch($str,$k))
            return $k;
      }
      return false;
   }
   else
      return false;
}

/**
* Search the array for a given key and return its value, but using tokenizers
* @param mixed $str             String to look for
* @param array $array           Array where we should look in
* @param mixed $token           Token to use as a separator
* @param integer $number        The number of parts we want to return
* @return mixed                 Desired value or false otherwise
*/
function array_find_key($str,$array,$token,$number)   
{
   if ($str && $array && $token && $number)
   {
      if ( ! is_array($array))
         return false;
      foreach($array as $k => $v)
      {
         if (strcasecmp(str_get_last($k,$token,$number),str_get_last($str,$token,$number))==0)
         {
            return $v;
         }
      }
      return false;
   }
   else
      return false;
}

/**
* Search the array for a given value and return it, but using tokenizers
* @param mixed $str		String to look for
* @param array $array		Array where we should look in
* @param mixed $token		Token to use as a separator
* @param integer $number	The number of parts we want to return
* @return mixed			Desired value or false otherwise
*/
function array_find_value($str,$array,$token,$number)   
{
   if ($str && $array && $token && $number)
   {
      if ( ! is_array($array))
         return false;
      foreach($array as $k => $v)
      {
         if (strcasecmp(str_get_last($v,$token,$number),str_get_last($str,$token,$number))==0)
         {
            return $v;
         }
      }
      return false;
   }
   else
      return false;
}

/**
* Return the last parts of a tokenized string
* @param $string	String to split
* @param $token		Token to use to split the string
* @param $number	How many parts we want to return
* @return mixed		Desired string
*/
function str_get_last($string,$token,$number)          
{
   $final='';
   if (! $string || ! $token || ! $number)
      return false;
   $temp=explode($token,$string);
   //Token not found in the string
   if (strcmp($temp[0],$string)===0)
      return false;
   $tokens=count($temp);
   for ($i=$tokens-$number;$i<$tokens;$i++)
      $final.=$token.$temp[$i];
   return $final;
}

/**
* Send SQL and expect just one row to change
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
function v_sql_1_update($query) {
  if (!$query)
     return false;
  #logit($query);
  global $connect;
  db_connect();

  $res = mysql_query($query, $connect);
  if (!$res) { 
    logit('Invalid query: ' . mysql_error()); 
    return(FALSE);

  } else if (mysql_affected_rows() ==1) {
    return(TRUE);
  } else {
    return(FALSE);
  }
}


/**
* Send SQL and expect just one /field/row to return
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
function v_sql_1_select($query) {
  if (!$query)
     return false;
  #logit($query);
  global $connect;
  db_connect();

  $result=false;
  $res = mysql_query($query, $connect);
  if (!$res) { 
    logit('Invalid query: ' . mysql_error()); 

  } else if (mysql_num_rows($res)==1) {
    list($result)=mysql_fetch_array($res, MYSQL_NUM);
  }
  return($result);
}


/**
*  Normalise mac address format
* Get a MAC address from the from XX:XX:XX:XX:XX:XX and convert it to XXXX.XXXX.XXXX
* @param mixed $old_mac		MAC address to convert
* @return mixed 		MACC address converted
*/
function normalise_mac($old_mac) {
  if ( ! $old_mac )
     return false;
  $mac = $old_mac;

  // Add zero to fill to 2 digits where needed, e.g.
  // convert 0:0:c:7:ac:1 to 00:00:0c:07:ac:01
  $digits=split(':',$old_mac);              # get one string per "part"
  if ($digits === false)
     return false;
  #$logger->logit("Join= " . join('', $digits) . "\n");
  $digits = preg_replace('/^([0-9a-fA-F])$/', '0${1}', $digits); 
  $mac = join(':', $digits);

  #$mac = preg_replace('/^([0-9a-fA-F]):/', '0${1}:',  $mac);  # start
  #$mac = preg_replace('/:([0-9a-fA-F]):/S', ':0${1}:', $mac);  # middle 
  #$mac = preg_replace('/:([0-9a-fA-F])$/', ':0${1}',  $mac);  # end
  #$logger->logit("$mac\n");

  // remove space, dash, dots, colon
  $mac = preg_replace('/-|\.|\s|:/', '', $mac); 

  # Add . every 4 digits
  $mac="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";
  #$logger->logit("$mac\n");

  return $mac;
}


/**
* Execute query and return assoc array
*   Assuming a table t1 with 2 Fields Code and Value:
*   $r= mysql_fetch_all("SELECT * from t1")
*   foreach ($r as $row) { $logger->logit("$row[Code], $row[Value]\n");
* @param mixed $query	Query to execute
* @return mixed		Result of the query if successful, or error otherwise
*/
function mysql_fetch_all($query){
  if (!$query)
     return false;
  $r=@mysql_query($query);
  $result = false;
  if($err=mysql_errno()) return $err;

  if(@mysql_num_rows($r))
    while($row=mysql_fetch_array($r,MYSQL_ASSOC))
      $result[]=$row;
  return $result;
}

/**
* Execute query, fetch one row and return assoc array
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
function mysql_fetch_one($query){
  if (!$query)
     return false;  
  #$logger->logit("QUERY: $query\n");
  $r=@mysql_query($query);
  if($err=mysql_errno())return $err;
  if(@mysql_num_rows($r))
  return mysql_fetch_array($r,MYSQL_ASSOC);
}

/**
* Execute query and return assoc array
*   Assuming a table t1 with 2 Fields Code and Value:
*   $r= mssql_fetch_all("SELECT * from t1")
*   foreach ($r as $row) { $logger->logit("$row[Code], $row[Value]\n");}
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
function mssql_fetch_all($query){
  if ( ! $query)
     return false;
  global $logger;
  $r=@mssql_query($query);
  $return = false;
  if (! $r) { 
    $logger->logit("Cannot execute query " .mssql_get_last_message());
    return false;
  }

  if(@mssql_num_rows($r))
    while($row=mssql_fetch_array($r))
      $result[]=$row;
  return $result;
}

/**
* Execute query, fetch one row and return assoc array
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
function mssql_fetch_one($query){
  if (! $query)
     return false;
  #global $logger;
  #$logger->logit("QUERY: $query\n");
  $r=@mssql_query($query);
  if($err=mssql_errno())return $err;
  if(@mssql_num_rows($r))
  return mssql_fetch_array($r);
}

/**
 * Since we could not reliably count affected rows after mysql operations
 * see also http://php.net/manual/en/function.mysql-info.php
 * USAGE:
 * $vals = get_mysql_info($linkid);
 * if($vals['rows_matched'] == 0){
 *    mysql_query("INSERT INTO table values('val1','val2', 'valetc')", $linkid);
 * }
 */
function get_mysql_info($linkid = false)
{
    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();

    if ($strInfo === false)
       return false;
    //TODO: What about those variables? Where are we getting those? SB please explain
    $return = array();
    ereg("Records: ([0-9]*)", $strInfo, $records);
    ereg("Duplicates: ([0-9]*)", $strInfo, $dupes);
    ereg("Warnings: ([0-9]*)", $strInfo, $warnings);
    ereg("Deleted: ([0-9]*)", $strInfo, $deleted);
    ereg("Skipped: ([0-9]*)", $strInfo, $skipped);
    ereg("Rows matched: ([0-9]*)", $strInfo, $rows_matched);
    ereg("Changed: ([0-9]*)", $strInfo, $changed);

    $return['records'] = $records[1];
    $return['duplicates'] = $dupes[1];
    $return['warnings'] = $warnings[1];
    $return['deleted'] = $deleted[1];
    $return['skipped'] = $skipped[1];
    $return['rows_matched'] = $rows_matched[1];
    $return['changed'] = $changed[1];

    return $return;
}

function mysql_affected_rows2($linkid = false)
{
    global $logger;

    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();
    if ($strInfo === false)
       return false;
    //TODO: What about the variable count? Where are we getting it? SB please explain
    if (ereg("Records: ([0-9]*)", $strInfo, $count) == false) {
      ereg("Rows matched: ([0-9]*)", $strInfo, $count);
    }
    $logger->debug("mysql_affected_rows2: count=$count[1], $strInfo", 3);
    return $count[1];
}

function write_auth($port_id, $system_id, $vlan)
{
   if ( $port_id && $system_id && $vlan )
   {
      if ( ! is_integer($port_id))
         return false;
      if ( ! is_integer($system_id))
         return false;
      global $logger;
      if (is_integer($vlan) && ($vlan>=0))
      {
         $query="REPLACE vmpsauth set AuthLast=NOW(), AuthVlan='$vlan', AuthPort='$port_id', sid='$system_id';";
         $logger->debug($query,3);
         return v_sql_1_update($query);
      }
      else
      {
          return false;
      }
   }
   else
      return false;
}

/**
* Delete a record of the specified table
* @param mixed $table		Table to delete from
* @param mixed $field		Field to use in the comparation
* @param mixed $identifier	What identifies this device?
* @return boolean		True if successful
*/
function do_delete($table, $field, $identifier)
{
   if ( $table && $field && $identifier )
   {
      global $logger;
      $table = mysql_escape_string($table);
      $field = mysql_escape_string($field);
      $identifier = mysql_escape_string($field);
      $query="DELETE FROM $table WHERE $field='$identifier';";
      $logger->debug($query,3);
      $res = mysql_query($query);
      if (!$res)
      {
         $logger->logit(mysql_error());
         return false;
      }
      else
      {
         return true;
      }
   }
   else
      return false;
}
/** 
* Get the netmask in 255.255.0.0 form
* @param integer $netmaskbits Bits of the netmask (1-32)
* @return string $netmask 	Netmask (255.255.0.0 form)
*/

function transform_netmask($netmaskbits) {
$netmask = array();
$netmask[32] = '255.255.255.255';
$netmask[31] = '255.255.255.254';
$netmask[30] = '255.255.255.252';
$netmask[29] = '255.255.255.248';
$netmask[28] = '255.255.255.240';
$netmask[27] = '255.255.255.224';
$netmask[26] = '255.255.255.192'; 
$netmask[25] = '255.255.255.128';
$netmask[24] = '255.255.255.0';
$netmask[23] = '255.255.254.0';
$netmask[22] = '255.255.252.0';
$netmask[21] = '255.255.248.0';
$netmask[20] = '255.255.240.0';
$netmask[19] = '255.255.224.0';
$netmask[18] = '255.255.192.0';
$netmask[17] = '255.255.128.0';
$netmask[16] = '255.255.0.0';
$netmask[15] = '255.254.0.0';
$netmask[14] = '255.252.0.0';
$netmask[13] = '255.248.0.0';
$netmask[12] = '255.240.0.0';
$netmask[11] = '255.224.0.0';
$netmask[10] = '255.192.0.0';
$netmask[9] = '255.128.0.0';
$netmask[8] = '255.0.0.0';
$netmask[7] = '254.0.0.0';
$netmask[6] = '252.0.0.0';
$netmask[5] = '248.0.0.0';
$netmask[4] = '240.0.0.0';
$netmask[3] = '224.0.0.0';
$netmask[2] = '192.0.0.0';
$netmask[1] = '128.0.0.0';
	return($netmask[$netmaskbits]);
};
/** 
* Reformat a MAC Adress from 0123.2345.2345 to 01:23:23:45:23:45
* @param string $mac	MAC adress of the device (dotted format)
*/
function reformat_mac($macdot) {
  if ( ! $macdot ) 
     return false;
  
  $numbers = explode('.',$macdot);
  if (strcmp($numbers[0],$macdot)===0)
     return false;
 
  $value = $numbers[0].$numbers[1].$numbers[2];
  for ($i=0; $i <= 6; $i++) {
        $mac .= substr($value,$i*2,2).':';
  };

  $mac = rtrim($mac,':.');
  return($mac);


};

/**
* Delete all references to a MAC address from the FreeNAC tables
* @param mixed $mac		MAC address of the device to delete
*/
function cascade_delete($mac)
{
   global $logger;
   if (!$mac)
      return false;
   # Get system id of this device
   $query="SELECT id FROM systems where mac='$mac';";
   $logger->debug($query,3);
   $system_id = v_sql_1_select($query);
   if (!$system_id)
      return false;
   # Tables which have an sid field.
   $tables_to_delete_from=array('EpoComputerProperties','nac_hostscanned','nac_openports','wsus_systems','wsus_systemToUpdates','epo_systems');
   foreach ($tables_to_delete_from as $table)
   {
      do_delete($table,'sid',$system_id);
   }

   # And now delete it from the systems table
   do_delete('systems','id',$system_id);
}
### EOF ###
?>
