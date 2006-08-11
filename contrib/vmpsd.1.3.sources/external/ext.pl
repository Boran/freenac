#!/usr/bin/perl

$|=1;

while () {
  chomp;
  ($vtp_domain, $switch_ip, $switch_port, $current_vlan, $mac_address) = split;

  # should be some clever stuff here
  #print "ALLOW\n";
  print "ALLOW $vtp_domain, $switch_ip, $switch_port, $current_vlan, $mac_address\n";
}
