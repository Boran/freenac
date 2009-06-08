#!/usr/bin/php
<?php
/**
 * bin/clear_mac.php
 *
 * Long description for file:
 *
 * This script telnets to a switch and clears a mac address
 *
 * TESTED:
 *      IOS ??
 *
 * USAGE :
 *      bin/clear_mac.php   MAC
 * Switch telnet authentication
 * It is assumed that sw_user, sw_pass, sw_en_pass are set in config.inc
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      sean Boran (FreeNAC Core Team)
 * @copyright                   2009 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 *
 */

require_once "funcs.inc.php"; # Load settings & common functions
$logger->setDebugLevel(2);    # Default 0, 3 for maximum debugging
$logger->setLogToStdOut();


function clear_mac($mac)
{
  $sw="sw0403";
  #$sw="sw04xx";  // getaddrinfo failed
  #$sw="10.1.1.1";  // Connection timed out
  #$sw="sw0412";  // Access denied

  $socket_timeout=15;  # 15 seconds connection
  $telnet_time1=10000;    // 10ms
  global $sw_user, $sw_pass,$sw_en_pass;
  #$sw_user="vmpsoperator"; $sw_pass="vmps%operator";  $sw_en_pass="vmps%operator";
  #$cmd ="show ip arp";
  $cmd ="clear mac address-table dynamic address $mac";
  if (!isset($mac) || !isset($sw_user) || !isset($sw_pass) || !isset($sw_en_pass)) {
    logit("clear_mac(): mac,sw_user,sw_pass or sw_en_pass not set ($mac,$sw_user,$sw_pass,$sw_en_pass)");
    return -1;
  } 
  if (strlen($mac)<12 || strlen($sw_user)<1 || strlen($sw_pass)<1 || strlen($sw_en_pass)<1) {
    logit("clear_mac(): mac,sw_user,sw_pass or sw_en_pass too short");
    return -1;
  } 

  $output= "Connecting to $sw and sending [$cmd]\n\n";
  if ( ! $fp=fsockopen($sw, 23, $errno, $errstr, $socket_timeout) ) { // port 23=telnet
  //if (! expect_popen("telnet $sw") )
    echo "Error: cannot connect to $sw, err=$errstr";
    exit -1;
  }
  get_result($fp);
  send_sw($fp,"$sw_user"); 
  #wait_result($fp);

/*$cases = array (
  array (0 => "Password:", 1 => PASSWORD),
  array (0 => "yes/no)?", 1 => YESNO)
);

while (true) {
 switch (expect_expectl ($fp, $cases))
 {
  case PASSWORD:
   send_sw($fp,"$sw_pass"); 
   break;

  case YESNO:
   send_sw($fp,"YES"); 
   break;

  case EXP_TIMEOUT:
  case EXP_EOF:
   break 2;
 
  default:
   die ("Expect Error has occurred!\n");
 }
}*/

  send_sw($fp,"$sw_pass"); 
  get_result($fp);

  send_sw($fp,"enable"); 
  send_sw($fp,"$sw_en_pass"); 
  get_result($fp);
  get_result($fp);

  send_sw($fp,"$cmd"); 
  send_sw($fp,"exit"); 

  $output.= ">> Results:\n";
  while (!feof($fp)) {
    $output.= fgets($fp, 128);
  }
  fclose($fp); 

  if ( preg_match('/Access denied|Invalid input detected/', $output) ){
    echo ">> Switch Error!\n";
  }
  return($output);
}                               // clear_mac()

function send_sw($fp, $cmd)
{
  #echo "SEND: $cmd\n";
  fputs($fp,"$cmd\r"); 
  usleep($telnet_time1);    // 10ms
}

function get_result($fp)
{ 
  #$result= "";
  #while (!feof($fp)) {
  #  $result.= fgets($fp, 128);
  #}
  $result= fgets($fp, 128);
  #echo "ANSWER: $result\n";
}

function wait_result($fp)
{ 
  $result= "";
  $i=0;
  while (1) {
    $i++;
    $result.= fgets($fp, 128);
    if ($result=="Password" )
	break;
    if ($i>5) {
	echo "Abort, answer not found. $result\n";
	break;
    } else {
	echo "answer $result\n";
    }
  }
  echo "ANSWER: $result\n";
}



$mac='0001.0001.0001';
$result= clear_mac($mac);
$logger->debug($result, 2);
if ($result !=-1) {
  $logger->logit("Clear mac $mac from switch $switch");
  log2db('info',"Clear mac $mac from switch $switch");
}

?> 
