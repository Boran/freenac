#!/usr/bin/php
<?php
/**
 * /opt/nac/bin/flap_detect
 *
 * Long description for file:
 * program input:  Read syslog every few minutes via cron
 *     Count events and see if over threshold, if yes, count vlans
 *     and systems and send a detailed email to the switch notify person
 *     and conf->email_user, restart the port, and block a system, depending on the 
 *     vlan/system count.
 *     Note: make sure you run this 7x24, since its measures the delta between calls.
 *           so if it does not run from 22:00 to 06:00 for example, it may give a
 *           false alert at 06:00.
 * program output: Send email alerts
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
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

$subject="FreeNAC port flapping";
require_once "funcs.inc.php";               # Load settings & common functions
require_once "snmp_defs.inc.php";

$logger->setDebugLevel(0);    # Default 0, 3 for maximum debugging
#$logger->setLogToStdOut();

$tmp1="$argv[0].tmp1";
$message="";
$logs="";

## Count the number of VMPS authentications per switch-port
## Example:
#tail -2000 /var/log/messages | egrep 'vmpsd: (ALLOW|DENY)'|awk '{print $11, $12, $  13}'|sort | uniq -c
#      1 192.168.245.106 port Fa0/1
#      5 192.168.245.159 port 2/3
#      1 192.168.245.159 port 2/35
#      4 192.168.245.159 port 2/36
#      2 192.168.245.159 port 2/37
#      7 192.168.245.159 port 2/40
# testing:
#$tail="tail -500 /var/log/messages > $tmp1";
$tail="/opt/nac/bin/logtail /var/log/messages /var/log/.messages.flap_detect > $tmp1";

#$cmd="$tail| egrep 'vmpsd: (ALLOW|DENY)' | |awk '{print $11, $12, $13}'|sort | uniq -c | awk '{if ($1 > 40) print $1, " ", $2, " port ", $4}'";
$cmd="egrep 'vmpsd: (ALLOW|DENY)' $tmp1 |awk '{print $11, $12, $13}'|sort | uniq -c  ";


## --- functions ---
function msg($msg1)
{
  global $message;
  $message .= $msg1;
}

function react($cnt, $switch, $port)
{
  global $tmp1, $connect, $message, $logs, $subject,$logger;
  $arr1=array(); $arr2=array();
  $best_vlan=0;  $best_count=0;
  $logs="";
  $email_alert=true;
  db_connect();

  msg("Flapping count=$cnt, switch=$switch $port\n");
  #$query="SELECT location,comment, (SELECT name FROM switch WHERE ip=switch), (SELECT notify FROM switch WHERE ip=switch) FROM inventory.port "
  #  . "WHERE switch='$switch' AND name='$port'";
  $query="select l.name, s.comment, s.name, s.notify from port p inner join switch s on p.switch=s.id and s.ip='$switch' and p.name='$port' inner join location l on l.id=s.location;";
    $logger->debug($query, 2);
    $res = mysql_query($query);
    if (!$res) 
    { 
       $logger->logit('Invalid query: ' . mysql_error(), LOG_ERR);
       exit(1); 
    }
  $row=mysql_fetch_array($res, MYSQL_NUM);

  msg("Location: $row[0], Description: $row[1]\n");
  msg("Email notify for $row[2]: $row[3]\n\n");
  $email_user=$row[3];
  $email_subject="$subject $row[2] port $port, Office $row[0]";

  // open & analyse logs
  $in = fopen ($tmp1,'r');
  while (! feof($in)) {
    $line=rtrim( fgets($in, 1024) );
    if (strlen($line)==0) {
      continue;
    }

      // get all "ALLOW" entries for this switch/port
      # Mar 13 07:51:15 INOCESvmps1 vmpsd: ALLOW: 00123f18f768 -> sec230, switch 192.168.245.68 port 2/30
      $regs=array();         
      #if (ereg("(.*) vmpsd: .*(ALLOW|DENY): (.*) -> (.*), switch (.*) port (.*)", $line, $regs) ) {
      if (ereg("(.*) vmpsd: .*(ALLOW|DENY): (.*) -> (.*), switch $switch port $port", $line, $regs) ) {
        #$logger->logit( "FOUND: {$regs[1]} {$regs[2]} $regs[3]\n");
        $success=$regs[2];
        $mac=$regs[3];     $vlan=$regs[4];
        $details="$regs[1]";
        $mac2="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";
        $logger->debug("vmpsd log matches: details=$details mac=$mac2 vlan=$vlan (switch=$switch port=$port)", 2);

        #msg("$regs[1] $mac2 vlan=$vlan\n\n");
        $logs .= "$regs[1] $mac2 vlan=$vlan\n\n";

        // now get unique entries and store in an array
        if (isset($arr1{$vlan})) {
          $arr1{$vlan}++;  // should we react if it happen twice?
        } else {
          $arr1{$vlan}=1;
        }
        if ( $arr1{$vlan} > $best_count ) {
          $best_vlan=$vlan;                // remember most frequent vlan
          $best_count=$arr1{$vlan};
        }

        if (isset($arr2{$mac2})) {
          $arr2{$mac2}=$vlan;  // should we react if it happen twice?
        } else {
          $arr2{$mac2}=$vlan;
        }
      }     //if
  }
  fclose($in);



  // document system details
  msg("System details:\n");
  foreach ($arr2 as $mac => $vlan) {
      $query="select s.name,s.comment,s.description,l.name,s.r_ip,CONCAT(u.Givenname,' ',u.Surname,' ',u.Department,' ',u.Mobile) from systems s inner join users u on u.id=s.uid and s.mac='$mac' left join location l on l.id=s.office;";
        $logger->debug($query, 2);
        $res = mysql_query($query);
        if (!$res) 
        {
           $logger->logit('Invalid query: ' . mysql_error(),LOG_ERR);
           exit(1);
        }
      $row=mysql_fetch_array($res, MYSQL_NUM);
      msg("$row[0] $mac $row[4], Office=$row[3] $row[2], $row[5]\n");
  }
  msg("\n");


  // how many systems? 
  if ( count($arr2) == 1 ) {
    $email_alert=false;      # wasn't a big problem, so syslog but no email alert
    logit("ACTION: Only one system $mac stormed on this port, DECISION: automatically restart port");
    #msg("\nACTION: Only one system $mac stormed on this port, DECISION: automatically restart port\n\n\n");
    ## TBD: we could query the auth table for others, and use that to count vlans & decide?
    snmp_restart_port($port, $switch);
    log2db('warn', "$email_subject: automatically restart port since $mac frequently re-authenticating");
    

  } else if ( count($arr1) < 2 ) {   // only one vlan
    $email_alert=false;      # wasn't a big problem, so syslog but no email alert
    logit("ACTION: Only one vlan storming on this port, restart port $port on $switch");
    #msg("\nACTION: There is only one vlan involved, its probably a false, alert: just a heavily used port?. Use your intuition, restarting the port now, this may help. \n\n\n");
    log2db('warn', "$email_subject: automatically restart port to try and stop flapping");
    snmp_restart_port($port, $switch);

  } else if ( count($arr2) < 3 ) {   // only 1 or two systems
    msg("\nACTION: There is less than three systems on this port, so no recommendation made on how to solve the flapping. Use your intuition. Restarting the port now, this may help. \n\n\n");
    log2db('warn', "$email_subject: automatically restart port to try and stop flapping");
    snmp_restart_port($port, $switch);

  } else {

  // We now want to force all systems to use $best_vlan, or disable
  //
  msg("\nDeciding on the 'best' vlan for this port:");
  foreach ($arr1 as $vlan => $cnt) { msg(" $vlan $cnt times,"); }
  msg("\nNo. vlans=". count($arr1));
  msg("\nMost frequent vlan: $best_count => $best_vlan \n\n");

  foreach ($arr2 as $mac => $vlan) {
    if ($vlan != $best_vlan ) {
       $query="select s.name,s.comment,s.description AS obsolete1,l.name,s.r_ip,CONCAT(u.Givenname,' ',u.Surname,' ',u.Department,' ',u.Mobile) from systems s inner join users u on u.id=s.uid and s.mac='$mac' left join location l on l.id=s.office;";
        $res = mysql_query($query);
        if (!$res) 
        {
           $logger->logit('Invalid query: ' . mysql_error(),LOG_ERR); 
           exit(1);
        }
      $row=mysql_fetch_array($res, MYSQL_NUM);
      $logger->debug("Problem: $row[0] $mac $row[4], Office=$row[3] r_ip=$row[4], user=$row[5]", 2);
      msg("Problem: $row[0] $mac, $row[5]\n");

      // Lookup vlan groups
      $new_vlan_group=v_sql_1_select("SELECT vlan_group FROM vlan WHERE default_name='$best_vlan'");
      $othervlangroup=v_sql_1_select("SELECT vlan_group FROM vlan WHERE default_name='$vlan'");

      if (($new_vlan_group !== false) && ($othervlangroup==$new_vlan_group)) {

        ## DO SOMETHING: switch vlan
        $best_vlan_num=v_sql_1_select("SELECT id FROM vlan WHERE default_name='$best_vlan'");
        if ( ! $best_vlan_num )
           $best_vlan_num = 0;
        $query="UPDATE systems SET vlan='$best_vlan_num' WHERE mac='$mac2'";
          #$logger->logit($query ."\n");
          $res = mysql_query($query);
          if (!$res)
          {
             $logger->logit("Error in UPDATE DB-Query: " . mysql_error(),LOG_ERR);
             exit(1);
          } 
          if (mysql_affected_rows()!=1) {
            $logger->logit("Query error: $query\n"); 
            msg("Query error: $query\n"); 
          }

        #$logger->logit("DECISION: Change $mac vlan to $best_vlan - $best_vlan_num since $best_vlan and $vlan are in the same group $othervlangroup\n");
        msg("==> DECISION: automatically changing $mac vlan to $best_vlan  - $best_vlan_num since $best_vlan and $vlan are in the same group $othervlangroup\n");
        log2db('warn', "$email_subject: automatically changing $mac to $best_vlan - $best_vlan_num since $best_vlan and $vlan are in the same group $othervlangroup");

      } else {
        #$logger->logit("DECISION: Disable $mac since $best_vlan and $vlan are not in the same group ($new_vlan_group-$othervlangroup)\n");
        msg("\n==> RECOMMENDATION: Disable $mac since $best_vlan and $vlan are not in the same group ($new_vlan_group-$othervlangroup)\n");
        log2db('warn', "$email_subject: RECOMMENDATION emailed to superuser: Disable $mac since $best_vlan and $vlan are not in the same group");
      }

    } else {
        msg("\n==> No action taken or recommended for $mac on $vlan.");

    }
  }
  }   // if ( count ($arr2)


  if (strlen($message) > 0 ) {    ## Need to send an alert

      msg("\nLog entries that caused this flap detection:\n");
      msg($logs);               ## Add logs at the end

      msg("\n\n");

      msg("List of other systems recently authenticated on this port:\n");
      #$query="SELECT mac,AuthLast,vlan_group,AuthVlan from vmpsauth WHERE AuthPort='$port' AND AuthSw='$switch' AND TIME_TO_SEC(TIMEDIFF(NOW(),AuthLast))<7500 ORDER BY AuthLast DESC ";
      $query="select s.mac,vm.authlast,v.vlan_group,v.default_id from systems s inner join vmpsauth vm on s.id=vm.sid inner join vlan v on v.id=vm.authvlan inner join port p on p.id=vm.authport inner join switch sw on p.switch=sw.id and sw.ip='$switch' and p.name='$port' AND TIME_TO_SEC(TIMEDIFF(NOW(),vm.AuthLast))<7500 ORDER BY vm.AuthLast DESC ";
      $res = mysql_query($query, $connect);
      if (!$res) 
      { 
         $logger->logit('Invalid query: ' . mysql_error(),LOG_ERR); 
         exit(1);
      }
      if (mysql_num_rows($res)==0) {
        #
      } else {
        while ( list($f1, $f2, $f3, $f4)=mysql_fetch_array($res, MYSQL_NUM) ) {
          msg("$f1  $f2  $f3  $f4\n");
        }
      }
      msg("\n\n");


      msg("List of other systems recently seen on this port:\n");
      #$query="select name,mac,r_ip,r_timestamp,lastseen from systems WHERE port='$port' AND switch='$switch' AND TIME_TO_SEC(TIMEDIFF(NOW(),lastseen))<7500";
      $query="select s.name,s.mac,s.r_ip,s.r_timestamp,s.lastseen from systems s inner join port p on s.lastport=p.id and p.name='$port' inner join switch sw on p.switch=sw.id and sw.ip='$switch' AND TIME_TO_SEC(TIMEDIFF(NOW(),s.lastseen))<7500";
      $res = mysql_query($query, $connect);
      if (!$res) 
      {
         $logger->logit('Invalid query: ' . mysql_error(),LOG_ERR);
         exit(1);
      }
      if (mysql_num_rows($res)==0) {
        #
      } else {
        while ( list($f1, $f2, $f3, $f4, $f5)=mysql_fetch_array($res, MYSQL_NUM) ) {
          msg("$f1  $f2  $f3  $f4 $f5 \n\n");
        }
      }

      if ( ! empty($email_user) ) {
        if ( $email_alert===TRUE ) {
          msg("\n\nThis alert was generated by: $argv[0]");
          if (strlen($conf->mail_user)>1) {
             debug1("Email alert to $email_user, {$conf->mail_user}");
             #$logger->mailit($email_subject, $message,"$email_user,{$conf->mail_user}");
             $logger->mailit($email_subject, $message,"$email_user,{$conf->mail_user}");
	  } else {
             debug1("Email alert to $email_user");
             $logger->mailit($email_subject, $message,$email_user);
          }
        }
        $message='';             ## empty for the next alert
        $email_alert=TRUE;

      } else {
          debug1("Sending email alert to mail user");
          msg("\n\nThis alert was generated by: $argv[0]");
          if ($conf->mail_user)
             $logger->mailit($email_subject, $message,$conf->mail_user);
          else
             $logger->mailit($email_subject, $message,"root");
        #logit("No email alert user configured on switch $switch");
      }
  }
}    // function()



## --- main () ----

  # first get the most resent log messags
  #$logger->logit $tail;
  unset($answer);
  #$answer=explode("\n", syscall("$tail"));  # save all log entries in $tmp1
  #$answer=syscall("ls");  # save all log entries in $tmp1
  #$answer=syscall("TEST_ERROR");  # save all log entries in $tmp1
  $answer=syscall("$tail");  # save all log entries in $tmp1
  if (! isset($answer) ) { 
    logit("syscall failed");  # will never happen, see syscall()
    exit;

  } 
  #else if (strlen($answer) >1) {
    # Error handling is not  easy, but if there is a non-zero message,
    # we probably have an error.
  #  logit("Possible error, answer= $answer");
  #}

  # do a grep on the newest log messages
  #unset($answer);
  $answer=explode("\n", syscall("$cmd")); 
  #$logger->logit($cmd);
  for ($j = 0; $j < count($answer); $j++){
      if ( empty($answer[$j]) )
        continue;
      #$logger->logit("$answer[$j]\n");
      debug("answer=" .$answer[$j], 3);

      # 2 192.168.245.85 port Fa0/4
      #if (preg_match("/\s+(\d+) ([1-9\.]+) port (.*)/", $answer[$j], $matches)) {
      if (preg_match("/\s+(\d+) ([0-9\.]+) port (.*)$/", $answer[$j], $matches)) {

        debug("match: count=$matches[1], switch=$matches[2], port=$matches[3]\n", 3);
        if ((int) $matches[1] > (int) $conf->flap_limit) {
          ## so now trawl the log entries in detail
          $message='';      # empty the buffer
          react($matches[1], $matches[2], $matches[3]);

        } else {
          debug("OK: count below threshold " .$conf->flap_limit, 3);
        }
      } else {
        debug("answer not matched", 3);
      }
  }

# delete $tmp1;  // in fact leave it aroud for debugging, overwritten anyway..

?>
