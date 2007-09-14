<?php
/**
 * funcs.inc
 *
 * Long description for file:
 * common PHP functions used by several freenac scripts
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

/* __autoload() Trick
 * This tries to autoload a class that has been devined by the user.
 * This way the user does not to add an explicit include statement to the
 * policy file...
 */

require_once '../etc/config.inc';

$conf=Settings::getInstance();

db_connect();



/* Connect to DB */
function db_connect()
{
  global $connect, $dbhost, $dbuser, $dbpass, $dbname;

  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}

function mysql_fetch_one($query){
  #echo "QUERY: $query\n";
  $r=@mysql_query($query);
  if($err=mysql_errno())return false;
  if(@mysql_num_rows($r)==1)
     return mysql_fetch_array($r,MYSQL_ASSOC);
  else 
     return false;
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

function vlanId2Name($vlanID) {
          // Todo: Proper Error Handling, and use better Database abstraction
      return v_sql_1_select("select default_name from vlan where id='$vlanID' limit 1");
}


?>
