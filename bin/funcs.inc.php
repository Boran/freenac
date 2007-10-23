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
set_include_path("./:../");

/**
* Load automagically a file containing the class specified by classname
* @param object $classname	Class to load
*/
function __autoload($classname)
{
   require_once "../lib/$classname.php";
}

/**
* Get configuration variables from config file
*/
require_once 'etc/config.inc';

/**
* @global object $conf		Contains configuration parameters from the config table
* @global object $logger	Provides for logging facilities
*/
$conf=Settings::getInstance();
$logger=Logger::getInstance();

/**
* Converts a vlan id to a vlan name
* @param integer $vlanID 	Vlan ID
* @return mixed 		Vlan name
*/
function vlanId2Name($vlanID) {
   // Todo: Proper Error Handling, and use better Database abstraction
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
   $temp=explode('.',$oid);
   return $temp[count($temp)-1];
}

/**
* Turn on a determined port identified by its index.
* @param mixed	$port_index		Port index according to SNMP
* @return boolean			True if port was successfully switched on, false otherwise
*/
function turn_on_port($port_index)                                             
{
   global $switch,$snmp_rw,$snmp_port,$logger;

   $oid='1.3.6.1.2.1.2.2.1.7'.'.'.$port_index;
   if (!snmpset($switch,$snmp_rw,$oid,'i',1))
   {
      $logger->logit("\tCouldn't turn on port $port.\n");
      return false;
   }
   else return true;
}

/**
* Get the snmp port index
* @param mixed $port	Port name to look for.  
* @param mixed $switch	Switch to ask
* @return mixed		Port index if found, false otherwise
*/
function get_snmp_port_index($port,$switch)
{
   global $snmp_rw;
   $ports_on_switch=snmprealwalk($switch,$snmp_rw,'1.3.6.1.2.1.31.1.1.1.1');       //Get the list of ports on the switch
   if (empty($ports_on_switch))
   {
      $logger->logit( "ABORTED: Could not contact switch $switch or unknown Switch.\n");
      return false;
   }
   $ports_on_switch=array_map("remove_type",$ports_on_switch);             //We are only interested in the string
   $port_oid=array_search($port,$ports_on_switch);                         //Is the port in this switch?
   if (empty($port_oid))
   {
      $logger->logit( "Port $port not found on switch $switch\n");
      log2db('info',"Port $port not found on switch $switch");
      return false;
   }

   return get_last_index($port_oid);
}


/**
* Turn off a determined port identified by its index.
* @param mixed  $port_index             Port index according to SNMP
* @return boolean                       True if port was successfully switched off, false otherwise
*/
function turn_off_port($port_index)                                            
{
   global $switch,$snmp_rw,$snmp_port,$logger;
   $oid='1.3.6.1.2.1.2.2.1.7'.'.'.$port_index;
   if (!snmpset($switch,$snmp_rw,$oid,'i',2))
   {
      $logger->logit("\tCouldn't shut down port $port.\n");
      return false;
   }
   else return true;
}

/**
* Returns the difference between 2 dates in secs
* @param mixed $date1			Date to substract from
* @param mixed $date2			Date
* @return mixed				Difference in second between those 2 dates
*/
function time_diff($date1,$date2)
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
    #$logger->logit("$query\n");
    $res = mysql_query($query, $connect);
    if (!$res) { die('Cannot write to vmplog table: ' . mysql_error()); }
  }

  // To view recent entries:
  // select * from naclog ORDER BY datetime DESC LIMIT 5;
}


## log2db3: write to naclog if debug level=3 
function log2db3($msg)
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
    if (!$res) { die('Cannot write to vmplog table: ' . mysql_error()); }
  }
}

/**
* Creates a connection to the MySQL database with the parameters defined in config.inc
*/
function db_connect()
{
  global $connect, $dbhost, $dbuser, $dbpass, $dbname;

  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}

/**
 * Abstract calling of unix commands.
 * Problem: popen does not pass back command success
 * so syscall cannot say if the command works.
 * @param mixed $command	Command to be executed
 * @return mixed		Result from that command
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

/**
* Remove the type of one element and leave only the value.
* This function is to be used when performing SNMP operations
* Example: INTEGER:33
	   Returns: 33
* @param mixed $element		Element to remove type from
* @return mixed 		Value without type
*/
function remove_type($element)                          
{
   return trim(substr($element,strpos($element,':')+1,strlen($element)));
}

/**
* Ping a MAC address on a specific switch port
* @param mixed $mac	MAC address to ping
* @param mixed $switch	Switch we want to query
* @param mixed $port	switch port we want to query
* @param mixed $vlan	VLAN to use to perform the query
* @return boolean	True if MAC has been found on the switch port
*/
function ping_mac2($mac,$switch,$port,$vlan)
{
   if (is_mac_on_port($mac,$switch,$port,$vlan))
      return true;
   else
      return false;
}

/**
* Detect if a hub is attached to a certain port
* If a hub is detected, suggest another vlan to avoid port flapping.
* So far it is only an adaptation from the old algorithm
* It hasn't been tested yet
*/
function detect_hub ($device, $port)
{
   if ($conf->detect_hubs)
   {
      #Get vlan_groups
      $query = "SELECT vlan_group FROM vlan WHERE id='{$device->getNewVlan_id()}';";
      $logger->debug($query,3);
      $new_vlan_group = v_sql_1_select($query);
      
      $query = "SELECT vlan_group FROM vlan WHERE id='{$device->getLastVlan_id()}';";
      $logger->debug($query,3);
      $last_vlan_group = v_sql_1_select($query);

      if ($last_vlan_group == $new_vlan_group)
      {
         #Stay with the existing vlan, to preserve connectivity
         $result = $lvlan_id;
      }
      else
      {
         #Use the normal vlan for this device
         $result = $nvlan_id;
      }

      $query=<<<EOF
SELECT sid, AuthVlan, AuthLast FROM vmpsauth WHERE
   TIME_TO_SEC(TIMEDIFF(NOW(), AuthLast)) < 7500 AND
   sid!='{$device->getSid()}' AND 
   AuthVlan!='{$device->getNewVlan_id()}' AND
   AuthPort='{$port->getPort_id()}' ORDER BY AuthLast DESC;";
EOF;
      $logger->debug($query,3);
      $res = mysql_query($query);
      if (!$res)
         return false;
      if (mysql_num_rows($res) > 0)
      {
         while (list($othersid, $tempvlan, $authlast)=mysql_fetch_array($res,MYSQL_NUM))
         {
            $query = "SELECT mac FROM systems WHERE id='$othersid';";
            $logger->debug($query,3);
            $other_mac = v_sql_1_select($query);

            $query = "SELECT default_id FROM vlan WHERE id='$tempvlan';";
            $logger->debug($query,3);
            $other_vlan = v_sql_1_select($query);

            if (ping_mac2($other_mac, $port->getSwitch_Name(), $port->getPort_Name(),$other_vlan))
            {
               $query = "SELECT vlan_group FROM vlan WHERE id='$other_vlan';";
               $logger->debug($query,3);
               $other_vlan_group = v_sql_1_select($query);
               if ($other_vlan_group == $new_vlan_group)
               {
                  $result = $other_vlan;
                  continue;
               }
               else
               {
                  $result=-1;
               }
            }
         }
         return $result;   
      }
   }
   else 
   {
      return false;
   }
}

function ping_mac($mac)
# Return: true=Ping successful
{
  db_connect();
  global $connect;
  
  $query="SELECT r_ip from systems "
        . " WHERE mac='" . $mac . "'";
        #$logger->logit("$query\n");
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
* Wrapper around the restart_port script.
* Restart a switch port
* @param mixed $port	Port name 
* @param mixed $switch	Switch
*/
function snmp_restart_port($port, $switch) {
  global $lastseen_sms_restart,$logger;
  #if ($lastseen_sms_restart) 
  {
     $answer=syscall("./restart_port.php $port $switch");
     debug1($answer);
     logit("snmp_restart_port: $answer");
  }
}


/**
* Wrapper around snmp_restart_port
* @param integer $port_id	ID of the port we want to restart
*/
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

/**
* Perform a case insensitive search for a given value in an array and return its key
* @param mixed $str	Value to look for
* @param array $array	Array to look in
* @return mixed		Key for that value, or false otherwise
*/
function array_isearch($str,$array)                    
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

/**
* Perform a case insensitive search for a given value in a bi-dimensional array and return its key
* @param mixed $str     Value to look for
* @param array $array   Array to look in
* @return mixed         Key for that value, or false otherwise
*/
function array_multi_isearch($str,$array)
{
   foreach($array as $k)
   {
      if (array_isearch($str,$k))
         return $k;
   }
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
   foreach($array as $k => $v)
   {
      if (strcasecmp(str_get_last($k,$token,$number),str_get_last($str,$token,$number))==0)
      {
         return $v;
      }
   }
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
   foreach($array as $k => $v)
   {
      if (strcasecmp(str_get_last($v,$token,$number),str_get_last($str,$token,$number))==0)
      {
         return $v;
      }
   }
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
   $temp=explode($token,$string);
   $tokens=count($temp);
   for ($i=$tokens-$number;$i<$tokens;$i++)
      $final.=$token.$temp[$i];
   return $final;
}

/**
* Tell whether a MAC address is on a certain port using SNMP
* @param mixed $mac	MAC to look for
* @param mixed $switch	Switch to look on
* @param mixed $port	Switch port to look on
* @param mixed $vlan	Vlan we'll use to look for that MAC address
* @return boolean	True if MAC found on that port, false otherwise
*/
function is_mac_on_port($mac,$switch,$port,$vlan)     
{
   global $snmp_ro,$logger;                                     //Read Only community

   $macs_on_vlan=@snmprealwalk($switch,"$snmp_ro@$vlan",'1.3.6.1.2.1.17.4.3.1.1');      //Obtain MAC address table
   if (empty($macs_on_vlan))
   {
      $logger->logit("Couldn't establish communication with $switch using the SNMP_RO community.");
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

/**
* Send SQL and expect just one row to change
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
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


/**
* Send SQL and expect just one /field/row to return
* @param mixed $query   Query to execute
* @return mixed         Result of the query if successful, or error otherwise
*/
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


/**
*  Normalise mac address format
* Get a MAC address from the from XX:XX:XX:XX:XX:XX and convert it to XXXX.XXXX.XXXX
* @param mixed $old_mac		MAC address to convert
* @return mixed 		MACC address converted
*/
function normalise_mac($old_mac) {
  $mac = $old_mac;

  // Add zero to fill to 2 digits where needed, e.g.
  // convert 0:0:c:7:ac:1 to 00:00:0c:07:ac:01
  $digits=split(':',$old_mac);              # get one string per "part"
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
  $r=@mysql_query($query);
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
  $r=@mssql_query($query);
  if (! $r) { 
    $logger->logit("Cannot execute query " .mssql_get_last_message());
    return -1;
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
function get_mysql_info($linkid = null)
{
    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();

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

function mysql_affected_rows2($linkid = null)
{
    global $logger;

    $linkid? $strInfo = mysql_info($linkid) : $strInfo = mysql_info();
    if (ereg("Records: ([0-9]*)", $strInfo, $count) == false) {
      ereg("Rows matched: ([0-9]*)", $strInfo, $count);
    }
    $logger->debug("mysql_affected_rows2: count=$count[1], $strInfo", 3);
    return $count[1];
}

### EOF ###
?>
