<?php
/**
 * /opt/nac/bin/funcs.inc.php
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
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("./:../");

function __autoload($classname)
{
   require_once "../lib/$classname.php";
}

require_once 'etc/config.inc';

$conf=Settings::getInstance();

function vlanId2Name($vlanID) {
          // Todo: Proper Error Handling, and use better Database abstraction
      return v_sql_1_select("select default_name from vlan where id='$vlanID' limit 1");
}

function is_field_active($field)
{
   $temp=v_sql_1_select("select value from config where name like '$field';");
   debug2("is_field_active: select value from config where name like '$field';");
   if (!empty($temp)&&((strcmp(trim($temp),"true")==0)||($temp==1)))
      return true;
   else
      return false;
}

function is_vm($mac)
{
   $counter=0;
   $parts=explode(".",$mac);
   $temp_mac=$parts[0].$parts[1].$parts[2];
   $query="select mac from ethernet where vendor like '%vmware%';";
   debug2("is_vm: $query");
   $res=mysql_query($query);
   if ($res)
   {
      while ($rows=mysql_fetch_array($res,MYSQL_ASSOC))
      {
         if (stripos($temp_mac,$rows['mac'])!==FALSE)
            $counter++;
      }
   }
   if ($counter>0)
      return true;
   else return false;
}

function get_last_index($oid)                                                   //Get the last number of an SNMP OID
{
   $temp=explode('.',$oid);
   return $temp[count($temp)-1];
}

function turn_on_port($port_index)                                              //Turn on port. Port index must be from SNMP
{
   global $switch,$snmp_rw,$snmp_port;

   $oid='1.3.6.1.2.1.2.2.1.7'.'.'.$port_index;
   if (!snmpset($switch,$snmp_rw,$oid,'i',1))
   {
      echo "\tCouldn't turn on port $port.\n";
      return false;
   }
   else return true;
}

function turn_off_port($port_index)                                             //Shut port down. Port index must be from SNMP
{
   global $switch,$snmp_rw,$snmp_port;
   $oid='1.3.6.1.2.1.2.2.1.7'.'.'.$port_index;
   if (!snmpset($switch,$snmp_rw,$oid,'i',2))
   {
      echo "\tCouldn't shut down port $port.\n";
      return false;
   }
   else return true;
}



function time_diff($date1,$date2)  //Returns the difference between 2 dates in secs
{
   $temp=explode(' ',$date1);
   $time_info_1=explode(':',$temp[1]);
   $date_info_1=explode('-',$temp[0]);
   $temp=explode(' ',$date2);
   $time_info_2=explode(':',$temp[1]);
   $date_info_2=explode('-',$temp[0]);
   $time1=mktime((int)$time_info_1[0],(int)$time_info_1[1],(int)$time_info_1[2],(int)$date_info_1[1],(int)$date_info_1[2],(int)$date_info_1[0]);
   $time2=mktime((int)$time_info_2[0],(int)$time_info_2[1],(int)$time_info_2[2],(int)$date_info_2[1],(int)$date_info_2[2],(int)$date_info_2[0]);
   $time=$time2-$time1;
   return $time;
}

function debug1($msg) {
  global $debug_flag1, $conf;
  $msg=rtrim($msg);
  if (($debug_flag1==TRUE) && (strlen($msg)>0) ) {
    if ($conf->debug_to_syslog) {
      syslog(LOG_INFO, "Debug1: $msg");
    } else {
      echo "Debug1: $msg\n";
    }
  }
}

function debug2($msg) {
  global $debug_flag2, $conf;
  $msg=rtrim($msg);
  if (($debug_flag2==TRUE) && (strlen($msg)>0) ) {
    if ($conf->debug_to_syslog) {
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
  if ($logit_to_stdout) {
    echo "logit: $msg";
  }
}

## log2db: write key events to naclog which is visible from the GUI
##         This should NOT be called from a secondary server, i.e.
##         avoid it in vmpsd_external
function log2db($level, $msg)
{
  global $connect;
  $msg=rtrim($msg);
  if (strlen($msg)>0 ) {
    db_connect();                 // just in case its not connected
    #$query="INSERT DELAYED INTO naclog "
    $query="insert into naclog set what='".mysql_real_escape_string($msg)."', host='".mysql_real_escape_string($_SERVER['HOSTNAME'])."', priority='".$level."'";
    #$query="INSERT INTO naclog "
    #  . "SET what='" . mysql_real_escape_string($msg )  . "', "
    #  . "host='"     . mysql_real_escape_string($_SERVER["HOSTNAME"]) . "', "
    #  . "priority='$level' ";
    #echo "$query\n";
    $res = mysql_query($query, $connect);
    if (!$res) { die('Cannot write to vmplog table: ' . mysql_error()); }
  }

  // To view recent entries:
  // select * from naclog ORDER BY datetime DESC LIMIT 5;
}


## log2db3: write to naclog if $debug_flag3
function log2db3($msg)
{
  global $connect, $debug_flag3;
  $level='debug';
  $msg=rtrim($msg);
  if (($debug_flag3==TRUE) && (strlen($msg)>0) ) {
    db_connect();                 // just in case its not connected
    #$query="INSERT INTO naclog "
    $query="INSERT DELAYED INTO naclog "
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

/* syscall
 * Abstract calling of unix commands.
 * Problem: popen does not pass back command success
 * so syscall cannot say if the command works.
 */
function syscall($command){
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
   
}

function remove_type($element)                          //Remove the type of one element and leave only the value
{
   $temp=explode(':',$element);
   $element=trim($temp[1]);
   return trim($element,'"');
}

function ping_mac2($mac,$switch,$port,$vlan)
{
   if (is_mac_on_port($mac,$switch,$port,$vlan))
      return true;
   else
      return false;
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

function snmp_restart_port($port, $switch) {
  global $lastseen_sms_restart;
  if ($lastseen_sms_restart) {
     $answer=syscall("./restart_port.php $port $switch");
     debug1($answer);
     logit("snmp_restart_port: $answer");
  }
}

function snmp_restart_port_id($port_id)
{
   if (is_numeric($port_id) && ($port_id>0))
   {
      $query="select p.name as port, s.ip as switch from port p inner join switch s on p.switch=s.id where p.id='$port_id' limit 1;";
      $result=mysql_fetch_one($query);
      $port=$result['port'];
      $switch=$result['switch'];
      snmp_restart_port($port,$switch);
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

function array_isearch($str,$array)                     //Search the array for a given value and return its key
{
   foreach($array as $k => $v)
   {
      if (strcasecmp($v,$str)==0)
      {
         return $k;
      }
   }
   return false;
}

function array_multi_isearch($str,$array)
{
   foreach($array as $k)
   {
      if (array_isearch($str,$k))
         return $k;
   }
   return false;
}

function array_find_key($str,$array,$token,$number)     //Search the array for a given key and return its value, but using tokenizers
{
   foreach($array as $k => $v)
   {
      if (strcasecmp(str_get_last($k,$token,$number),str_get_last($str,$token,$number))==0)
      {
         return $v;
      }
   }
   return false;
}

function array_find_value($str,$array,$token,$number)   //Search the array for a given value and return it, but using tokenizers
{
   foreach($array as $k => $v)
   {
      if (strcasecmp(str_get_last($v,$token,$number),str_get_last($str,$token,$number))==0)
      {
         return $v;
      }
   }
   return false;
}


function str_get_last($string,$token,$number)           //Return the last parts of a tokenized string
{
   $temp=explode($token,$string);
   $tokens=count($temp);
   for ($i=$tokens-$number;$i<$tokens;$i++)
      $final.=$token.$temp[$i];
   return $final;
}

function is_mac_on_port($mac,$switch,$port,$vlan)       //Tell whether a MAC address is on a certain port using SNMP
{
   global $snmp_ro;                                     //Read Only community

   $macs_on_vlan=@snmprealwalk($switch,"$snmp_ro@$vlan",'1.3.6.1.2.1.17.4.3.1.1');      //Obtain MAC address table
   if (empty($macs_on_vlan))
   {
      logit("Couldn't establish communication with $switch using the SNMP_RO community.");
      return false;
   }
   $macs_on_vlan=array_map("remove_type",$macs_on_vlan);
   $macs_on_vlan=array_map("normalise_mac",$macs_on_vlan);
   $mac_on=array_isearch($mac,$macs_on_vlan);                                           //Is this MAC in this switch?
   if (empty($mac_on))
      return false;                                                                     //No, return

   $bridge_port_number=@snmprealwalk($switch,"$snmp_ro@$vlan",'1.3.6.1.2.1.17.4.3.1.2'); //Yes, get bridge port number for vlan
   if (empty($bridge_port_number))
      return false;
   $bridge_port_number=array_map("remove_type",$bridge_port_number);
   $bridge_port=array_find_key($mac_on,$bridge_port_number,'.',5);                      //Where is this MAC?
   if (empty($bridge_port))
      return false;

   $map_bridge_port=@snmprealwalk($switch,"$snmp_ro@$vlan","1.3.6.1.2.1.17.1.4.1.2");   //Map the bridge port to the ifIndex
   if (empty($map_bridge_port))
      return false;
   $map_bridge_port=array_map("remove_type",$map_bridge_port);
   $map_bridge=array_find_key($bridge_port,$map_bridge_port,'.',1);                   //Get the one that is of interest to us
   if (empty($map_bridge))
      return false;

   $port_names=@snmprealwalk($switch,"$snmp_ro@$vlan","1.3.6.1.2.1.31.1.1.1.1");        //Get the name of the interfaces
   if (empty($port_names))
      return false;
   $port_names=array_map("remove_type",$port_names);
   $port_learnt=array_find_key($map_bridge,$port_names,'.',1);                          //What is the port name of this interface?
   if (strcmp($port_learnt,$port)==0)                                                   //Is this name equal to the one we provided??
      return true;                                                                      //Yes, the MAC is on this port
   else
      return false;                                                                     //No, MAC is not using this port
}


## old: delete later #############
function mailit($switch, $msg) {
  ## These should be set in config.inc
  global $conf;

  if ($conf->mail_user!=="") {
    debug1("Sending email alert to ".$conf->mail_user);
    mail($conf->mail_user, "VMPS alert on $switch", $msg);
  }
  # Send an email to super user, for the relevant switch
  # each switch has its own email alias "vmps.SWITCH@domain"
  if ($conf->maildomain!=="0" ) {
    debug1("Sending email alert to vmps.$switch@".$conf->maildomain);
    mail("vmps.$switch@".$conf->maildomain, "VMPS notification on $switch", $msg);
  }
}

function notify2($switch, $msg, $subject) {
  global $connect, $conf;
  db_connect();

  ## 1. Sent email to sysadmin 
  if ($conf->mail_user!=="") {    # usually for root
    debug1("Sending email alert to ".$conf->mail_user);
    #mail($mail_user, "VMPS alert on $switch", $msg);
    mail($conf->mail_user, $subject, $msg);
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
  global $connect, $conf;
  db_connect();

  if ($conf->mail_user!=="") {    # usually for root
    debug1("Sending email alert to ".$conf->mail_user);
    mail($conf->mail_user, "VMPS alert on $switch", $msg);
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
function insert_user ($username) {
    ## Is this user already in out "users" table?
    $query="SELECT username from users WHERE username='".$username."' ";
      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());

    ## The select query had no effect, so assume its a new user.
    if (mysql_affected_rows()==0) {

      ## TBD: Is this new user in our organisation? We should
      ##      really only set manual_direx_sync for "foreign" users.
      $query="INSERT INTO users SET LastSeenDirectory=now(), manual_direx_sync='1', "
        .      "username='".$username."'";
      #$query="INSERT INTO users SET LastSeenDirectory=now(),  "
      #  .      "username='".$username."'";
      debug2("$query");
      $res = mysql_query($query) OR die("Error in DB-Query: " . mysql_error());

      $str = "New user added for Directory: $username" ;
        debug2($str);
        log2db('info', $str);
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
