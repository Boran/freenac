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
  $fp=fsockopen($sw, 23); // port §23=telnet
  fputs($fp,"$sw_user\r"); 
  usleep(10000);    // 10ms
  fputs($fp,"$sw_pass\r"); 
  usleep(10000);    // 10ms
  fputs($fp,"enable\r"); 
  usleep(10000);    // 10ms
  fputs($fp,"$sw_en_pass\r"); 
  usleep(10000);    // 10ms
  fputs($fp,"$cmd\r"); 
  usleep(10000);    // 10ms
  fputs($fp,"exit\r"); 

  $output.= ">> Results:\n";
  while (!feof($fp)) {
    $output.= fgets($fp, 128);
  }
  fclose($fp); 
  return($output);
}                               // clear_mac()

$mac='0001.0001.0001';
$result= clear_mac($mac);
$logger->debug($result, 2);
if ($result !=-1) {
  $logger->logit("Clear mac $mac from switch $switch");
  log2db('info',"Clear mac $mac from switch $switch");
}

?> 
