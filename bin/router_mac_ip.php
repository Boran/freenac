#!/usr/bin/php
<?php
/**
 * bin/router_mac
 *
 * Long description for file:
 * Get MAC / IP table of active hosts from core routers
 * - update the IP for known MACs, with a time stamp
 * - lookup the DNS name for MACs called "unknown"
 * - insert all new unknown MACs, with IP, DNS name, and make as status "unmanaged"
 * - save the last DNS name corresponding to the current ip (r_ip) in last_hostname (v3)
 *
 * On IOS do "show ip arp"
 *        or "sh ip arp vrf insec"
 * Further reading: 
 *    http://www.cisco.com/public/sw-center/netmgmt/cmtk/mibs.shtml
 *    The "getif" tool for exploring MIBs.
 *    http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&objectInput=ipNetToMediaPhysAddress
 *    MIB Directory: ~ip/ipNetToMediaTable/ipNetToMediaEntry/ipNetToMediaPhysAddress
 *    OID: 1.3.6.1.2.1.4.22.1.2
 *    Which mibs are supported by which Cisco devices ?
 *      http://www.cisco.com/public/sw-center/netmgmt/cmtk/mibs.shtml
 *    Could not get it to work with Cisco PIX
 * To walk a router:
 *    snmpwalk -v 1 -c COMMUNITY IP_ADDRESS
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


require_once "funcs.inc.php";               # Load settings & common functions
# Debugging
$logger->setDebugLevel(0);
$logger->setLogToStdOut(false);
$mysql_write1=true;                    # Just test or actually write DB changes??
$mysql_write2=true;                    # Just test or actually write DB changes??



// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;

db_connect();
global $connect;


if ( !$conf->core_routers ) {   // no results, error?
   $logger->logit("no routers specified in core_routers variable in the config table");
   log2db('info',"no routers specified in core_routers variable in the config table");
   exit -1;
}


// Get the mac addresses of all unknown devices
// for use the autoupdating of DNS names, see below
if ( $conf->router_mac_ip_update_from_dns ) {   // feature enabled?

  #$sql="SELECT mac FROM systems WHERE name='unknown'";
  $sql="SELECT mac FROM systems WHERE name LIKE '%unknown%'";
  $result=mysql_query($sql,$connect);
  if (!$result) { die('Invalid query: '.mysql_error()); }
  $i=0;
  $uk_mac=array();
  while($row=mysql_fetch_row($result)){
        $uk_mac[$i]=$row[0];
        $i++;
  }
  $logger->debug("router_mac_ip_update_from_dns: $i unknowns noted\n");
}


// Connect to each router and download its ARP table
foreach (split(' ', $conf->core_routers) as $router) {
  $count_updates=0;

  // query interface list and split into an array
  // Ignore warnings on stderr
  #$results=snmprealwalk($router, $snmp_ro, 'ipNetToMediaPhysAddress');
  $results=@snmprealwalk($router, $snmp_ro, 'ipNetToMediaPhysAddress');
  if ( !$results ) {   // no results, error?
    $logger->logit("No results retrieved from router $router: SNMP errors?");
    continue;
  }
  else {
    $results=array_map("remove_type",$results);  // remove type prefix
  }

  // go through each pair and update the SYSTEMS table
  foreach ($results as $k => $v) {
  $logger->debug("Pre-match results: " .$results[$i], 2);

     $ip=ltrim(str_get_last($k,'.',4),'.');
     #$logger->debug("$ip - $matches[3] ",2);
     $mac=normalise_mac($v);

     #Check for an invalid mac
     if (strcasecmp($mac,'ffff.ffff.ffff')==0)
        continue;
     $logger->debug("$ip - $mac ",2);

     if ( preg_match($conf->router_mac_ip_ignore_ip, $ip) ) {
       $logger->debug("Ignore Non relevant Networks: $ip - $mac ", 2);
       continue;
     }
     if ( preg_match($conf->router_mac_ip_ignore_mac, $mac) ) {
       $logger->debug("Ignore Non relevant macs: $mac ", 2);
       continue;
     }


     // v3: get DNS name for all IPs
     $fqdn=gethostbyaddr($ip);
       if($fqdn!=$ip) { // We got the last host name, now save it
         $fqdn = strtolower($fqdn);
         // Sometime a DHCP server adds a 12 digit number with the IP to thename, we strip it here.
         // myhost-193005232074.mydomain.net
         $fqdn=preg_replace('/-\d\d\d\d\d\d\d\d\d\d\d\d\./', '.', $fqdn);
         $query_dns=", last_hostname='$fqdn' ";
         list($hostname_only) = split('[.]', $fqdn);  // strip domain name
         $logger->debug("Save last DNS of $mac as $hostname_only, $fqdn", 2);

       } else {
         $query_dns='';     // don't set last_hostname
         $hostname_only='';
         $fqdn='';
       }


     // suggestion from PB: (Reply from SB: elegant as its just one query, simpler, but very mysql specific?)
     #$query1="INSERT INTO systems SET  mac='$mac', vlan='1', status=3, r_timestamp=NOW(), r_ip='$ip', comment='Auto discovered by router_mac_ip'";
     #$query2=" ON DUPLICATE KEY UPDATE r_timestamp=NOW(), r_ip='$ip'";
     $query1="UPDATE systems SET r_timestamp=NOW(), r_ip='$ip' $query_dns";
     $query2='';
     $where= " WHERE mac='$mac'";

          // if this mac has no associated name i.e. 'unknown', try to update its 'name'
          if ( $conf->router_mac_ip_update_from_dns ) {   // feature enabled?
            if (in_array($mac,$uk_mac)) {
              $logger->debug("router_mac_ip_update_from_dns FQDN=$fqdn IP=$ip MAC=$mac", 1);
              if(strlen($hostname_only)>0) { // We got the host name, now update it
                $query2=", name='$hostname_only' ";
                $logger->logit("Change name of $mac to its DNS name $hostname_only");
                #if (!mysql_query($sql,$connect)) { die('Invalid query: '.mysql_error()); }
              }
            }
          }

        $query=$query1 . $query2 . $where;
        $rowcount=0;
        if ($mysql_write1) {
          $res = mysql_query($query, $connect);
          if (!$res) { die('Invalid query:' . mysql_error()); }
          #$rowcount=mysql_affected_rows($connect);
          $rowcount=mysql_affected_rows2($connect);
          $logger->debug($query ."==> rows:" .$rowcount, §2);
        } else {
          $logger->logit("QUERY DRYRUN: $query\n");
        }

        // Analyse results by checking rowcount
        if ($rowcount==1) {          # it worked
          $logger->debug("Router $router: $ip, $mac, $fqdn: updated in systems table");
          $count_updates++;

        } else if (($rowcount==0) && ($conf->router_mac_ip_discoverall)) {   
          // New unmanaged systems have been discovered, lets insert/document them
          // TBD: make sure that all IPs come from our networks? So far, only local
          //      IPs were visible
          $logger->debug("$ip - $mac - $fqdn: new, so insert into systems",2);
          # TBD: What vlan should we use? In theory it makes no difference, since these device should only be unmanaged,
          # but if they connect to a VMPS port saome day??
          # We could use $conf->set_vlan_for_unknowns, or set to '1' which is the default. For now use the latter.
          #$query1="INSERT INTO systems SET  mac='$mac', vlan='1', status=3, r_timestamp=NOW(), r_ip='$ip', comment='Auto discovered by router_mac_ip'";
          $query1="INSERT IGNORE INTO systems SET  mac='$mac', vlan='1', status=3, r_timestamp=NOW(), r_ip='$ip', comment='Auto discovered by router_mac_ip' $query_dns";
          $query2='';
          if ( $conf->router_mac_ip_update_from_dns ) {   // resolve NAME from DNS
            if(strlen($hostname_only)>0) { // We got the host name, now update it
              $query2=", name='$hostname_only' ";
            } else {
              $query2=", name='unknown' ";
            }
          }

          $logger->logit("New unmanaged end-device: mac=$mac ip=$ip dns=$hostname_only");
          $query=$query1 . $query2;
          if ($mysql_write2) {
            if (!mysql_query($query,$connect)) { die('Invalid query: '.mysql_error()); }
          } else {
            $logger->logit("QUERY DRYRUN: $query\n");
          }
        } else if ($rowcount == -1) {   # problem
          $logger->logit("Error query failed: $query");

        } else if ($rowcount > 1) {   # problem: duplicates
          #$logger->logit("$query");
          $logger->logit("$ip - $mac : duplicates in systems table - ERROR");

        }   

 }

 # Don't write to the GUI logging table any more, its too noisy/frequent
 #log2db('info',"Update $count_updates mac/ip tables from router $router");
 $logger->logit("Update $count_updates mac/ip tables from $router");
}

  // measure performance
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   $logger->debug("Time taken= ".$totaltime." seconds\n");

###
# vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent
#vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent
###
?>
