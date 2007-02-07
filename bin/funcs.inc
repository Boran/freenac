<?php
/**
 * /opt/nac/bin/dump_ports
 *
 * Long description for file:
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
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link				http://www.freenac.net
 *
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");
require_once "etc/config.inc";

function debug1($msg) {
  global $debug_flag1, $debug_to_syslog;
  $msg=rtrim($msg);
  if (($debug_flag1==TRUE) && (strlen($msg)>0) ) {
    if ($debug_to_syslog===TRUE) {
      syslog(LOG_INFO, "Debug1: $msg");
    } else {
      echo "Debug1: $msg\n";
    }
  }
}

function debug2($msg) {
  global $debug_flag2, $debug_to_syslog;
  $msg=rtrim($msg);
  if (($debug_flag2==TRUE) && (strlen($msg)>0) ) {
    if ($debug_to_syslog===TRUE) {
      syslog(LOG_INFO, "Debug2: $msg");
    } else {
      echo "Debug2: $msg\n";
    }
  }
}

function logit($msg) {
  global $debug_flag1, $logit_to_stdout;
  $msg=rtrim($msg);
  syslog(LOG_INFO, "$msg");

  # Write a message on stdout too?
  if ($logit_to_stdout===TRUE) {
    echo "logit: $msg";
  }
}

function reporterr($level, $msg)
{
  global $connect;
  if (strlen($msg)>0 ) {
    db_connect();                 // just in case its not connected
    #$query="INSERT DELAYED INTO vmpslog "
    $query="INSERT INTO vmpslog "
      . "SET what='" . mysql_real_escape_string($msg )  . "', "
      . "priority='$level' ";
    #echo "$query\n";
    $res = mysql_query($query, $connect);
    if (!$res) { die('Cannot write to vmplog table: ' . mysql_error()); }
  }

  // To view recent entries:
  // select * from vmpslog ORDER BY datetime DESC LIMIT 5;
}

function reporterr3($msg)
{
  global $connect, $debug_flag3;
  $level='debug';
  $msg=rtrim($msg);
  if (($debug_flag3==TRUE) && (strlen($msg)>0) ) {
    db_connect();                 // just in case its not connected
    #$query="INSERT INTO vmpslog "
    $query="INSERT DELAYED INTO vmpslog "
      . "SET what='" . $msg   . "', "
      . "priority='" . $level . "' ";
    #echo "$query\n";
    $res = mysql_query($query, $connect);
    if (!$res) { die('Cannot write to vmplog table: ' . mysql_error()); }
  }
}

function db_connect()
{
  global $connect, $dbhost, $dbuser, $dbpass, $dbname;

  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}

function syscall($command){
   $result='';
   if ( $proc = popen("($command) 2>&1","r") ) {
       while (!feof($proc))
         $result .= fgets($proc, 1000);
       pclose($proc);
       return $result;
   }
}


function ping_mac($mac)
# Return: true=Ping successful
{
  db_connect();
  global $connect;

  $query="SELECT r_ip from systems "
        . " WHERE mac='" . $mac . "'";
        #echo("$query\n");
        $res= mysql_query($query, $connect);
        if (!$res) { die('Invalid query: ' . mysql_error()); }

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
       #echo "Ping Error: $answer\n";
       logit("Ping Error no answer: $answer");
       return false;

     } else if ( preg_match("/\d+ received,/m", $answer) ) {
       #echo "Ping OK\n";
       logit("Ping OK: $answer");
       #logit("Ping OK");
       return true;

     } else {
       #echo "Ping Error: $answer\n";
       logit("Ping Error: $answer");
       return false;
     }

   } else {   #
     logit("No IP found for $mac");
     return false;
   }
}


// function to change german umlauts into ue, oe, etc.
// http://ch2.php.net/iconv
function cv_input($str){
     $out = "";
     for ($i = 0; $i<strlen($str);$i++){
           $ch= ord($str{$i});
           switch($ch){
               case 195: $out .= "";break;   
               case 164: $out .= "ae"; break;
               case 188: $out .= "ue"; break;
               case 182: $out .= "oe"; break;
               case 132: $out .= "Ae"; break;
               case 156: $out .= "Ue"; break;
               case 150: $out .= "Oe"; break;
               default : $out .= chr($ch) ;
           }
     }
     return $out;
}

function snmp_restart_port($switch, $port) {
  global $lastseen_infnet_restart;
  if ($lastseen_infnet_restart===TRUE) {
     $answer=syscall("/opt/vmps/bin/restart_port $switch $port");
     debug1($answer);
     logit("restart_port for GWP: $answer");
  }
}


function lookup_vendor_mac($mac) {
  global $connect;

  $mac = preg_replace('/-|\.|\s/', '', $mac);        #remove space, dash, dots
  $mac="$mac[0]$mac[1]$mac[2]$mac[3]$mac[4]$mac[5]"; # Keep first 6 digits
  $query="SELECT vendor from ethernet WHERE mac='" . $mac . "' ";
  #debug1("$query\n");
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }

  if (mysql_num_rows($res) ==0) {
    $result='unknown';      # no entry
    #debug1("Etherner vendor not found");

  } else {
    $resultarray=mysql_fetch_array($res, MYSQL_NUM);
    $result=$resultarray[0];
  }
  #debug1("Ethernet vendor=$result");
  return $result;
}



## old: delete later #############
function mailit($switch, $msg) {
  ## These should be set in config.inc
  global $maildomain, $mail_user;

  if ($mail_user!=="") {
    debug1("Sending email alert to $mail_user");
    mail($mail_user, "VMPS alert on $switch", $msg);
  }
  # Send an email to super user, for the relevant switch
  # each switch has its own email alias "vmps.SWITCH@domain"
  if ($maildomain!=="0" ) {
    debug1("Sending email alert to vmps.$switch@$maildomain");
    mail("vmps.$switch@$maildomain", "VMPS notification on $switch", $msg);
  }
}

function notify2($switch, $msg, $subject) {
  global $connect, $mail_user;
  db_connect();

  ## 1. Sent email to sysadmin 
  if ($mail_user!=="") {    # usually for root
    debug1("Sending email alert to $mail_user");
    #mail($mail_user, "VMPS alert on $switch", $msg);
    mail($mail_user, $subject, $msg);
  }

  ## 2. Lookup notify list for that switch, email them too.
  $query="select notify from switch WHERE name='" . $switch . "'";
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }
  if (mysql_num_rows($res)==1) {
    $resultarray=mysql_fetch_array($res, MYSQL_NUM);
    $notify_users=$resultarray[0];

    debug1("Sending email alert to $notify_users");
    mail($notify_users, $subject, $msg);

  } else {
    ## TBD: error?
  }
}

function notify($switch, $msg) {
  global $connect, $mail_user;
  db_connect();

  if ($mail_user!=="") {    # usually for root
    debug1("Sending email alert to $mail_user");
    mail($mail_user, "VMPS alert on $switch", $msg);
  }

  $query="select notify from switch WHERE name='" . $switch . "'";
  $res = mysql_query($query, $connect);
  if (!$res) { die('Invalid query: ' . mysql_error()); }
  if (mysql_num_rows($res)==1) {
    $resultarray=mysql_fetch_array($res, MYSQL_NUM);
    $notify_users=$resultarray[0];

    debug1("Sending email alert to $notify_users");
    mail($notify_users, "VMPS alert on $switch", $msg);

  } else {
    ## TBD: error?
  }
}

## send SQL and expect just one row to change
function v_sql_1_update($query) {
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


## SQL SQL and expect just one /field/row to return
function v_sql_1_select($query) {
  #logit($query);
  global $connect;
  db_connect();

  $result=NULL;
  $res = mysql_query($query, $connect);
  if (!$res) { 
    logit('Invalid query: ' . mysql_error()); 

  } else if (mysql_num_rows($res)==1) {
    list($result)=mysql_fetch_array($res, MYSQL_NUM);
  }
  return($result);
}


## Normalise mac address format
function normalise_mac($old_mac) {
  $mac = $old_mac;

  // Add zero to fill to 2 digits where needed, e.g.
  // convert 0:0:c:7:ac:1 to 00:00:0c:07:ac:01
  $digits=split(':',$old_mac);              # get one string per "part"
  #echo "Join= " . join('', $digits) . "\n";
  $digits = preg_replace('/^([0-9a-fA-F])$/', '0${1}', $digits); 
  $mac = join(':', $digits);

  #$mac = preg_replace('/^([0-9a-fA-F]):/', '0${1}:',  $mac);  # start
  #$mac = preg_replace('/:([0-9a-fA-F]):/S', ':0${1}:', $mac);  # middle 
  #$mac = preg_replace('/:([0-9a-fA-F])$/', ':0${1}',  $mac);  # end
  #echo "$mac\n";

  // remove space, dash, dots, colon
  $mac = preg_replace('/-|\.|\s|:/', '', $mac); 

  # Add . every 4 digits
  $mac="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";
  #echo "$mac\n";

  return $mac;
}


//
// Insert a new user, if not already in the Users table.
// 
function insert_user ($AssocNtAccount) {
    ## Is this user already in out "users" table?
    $query="SELECT AssocNtAccount from users WHERE AssocNtAccount='$AssocNtAccount' ";
      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());

    ## The select query had no effect, so assume its a new user.
    if (mysql_affected_rows()==0) {

      ## TBD: Is this new user in our organisation? We should
      ##      really only set manual_direx_sync for "foreign" users.
      $query="INSERT INTO users SET LastSeenDirex=now(), manual_direx_sync='1', "
        .      "AssocNtAccount='$AssocNtAccount'";
      #$query="INSERT INTO users SET LastSeenDirex=now(),  "
      #  .      "AssocNtAccount='$AssocNtAccount'";
      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());

      $str = "New user added for Direx: $AssocNtAccount" ;
        debug2($str);
        reporterr('info', $str);
    }
}

//
// Execute query and return assoc array
//   Assuming a table t1 with 2 Fields Code and Value:
//   $r= mysql_fetch_all("SELECT * from t1")
//   foreach ($r as $row) { echo "$row[Code], $row[Value]\n";
//
function mysql_fetch_all($query){
  $r=@mysql_query($query);
  if($err=mysql_errno()) return $err;

  if(@mysql_num_rows($r))
    while($row=mysql_fetch_array($r))
      $result[]=$row;
  return $result;
}

function mysql_fetch_one($query){
  #echo "QUERY: $query\n";
  $r=@mysql_query($query);
  if($err=mysql_errno())return $err;
  if(@mysql_num_rows($r))
  return mysql_fetch_array($r);
}

//
// Execute query and return assoc array
//   Assuming a table t1 with 2 Fields Code and Value:
//   $r= mssql_fetch_all("SELECT * from t1")
//   foreach ($r as $row) { echo "$row[Code], $row[Value]\n";}
//
function mssql_fetch_all($query){
  $r=@mssql_query($query);
  if (! $r) { 
    echo "Cannot execute query " .mssql_get_last_message();
    return -1;
  }

  if(@mssql_num_rows($r))
    while($row=mssql_fetch_array($r))
      $result[]=$row;
  return $result;
}

function mssql_fetch_one($query){
  #echo "QUERY: $query\n";
  $r=@mssql_query($query);
  if($err=mssql_errno())return $err;
  if(@mssql_num_rows($r))
  return mssql_fetch_array($r);
}


### EOF ###
?>
