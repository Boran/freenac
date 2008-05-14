#!/bin/perl
#
# gather1.pl
# Test script: what can we query remotely to ID a windows box?
# First argument is the IP
# 2006: Quick hack. S.Boran/FreeNAC

# TBD: check that we do have an argumentr
#$ip="193.5.227.11"; 
$ip=$ARGV[0];
print "Query=$ip: ";

use Net::NBName;
my $nb = Net::NBName->new;
my $ns = $nb->node_status($ip);
         if ($ns) {
             #print $ns->as_string;
                   for my $rr ($ns->names) {
                       if ($rr->suffix == 0 && $rr->G eq "GROUP") {
                           $domain = $rr->name;
                       }
                       if ($rr->suffix == 3 && $rr->G eq "UNIQUE") {
                           $user = $rr->name;
                       }
                       if ($rr->suffix == 0 && $rr->G eq "UNIQUE") {
                           $machine = $rr->name unless $rr->name =~ /^IS~/;
                       }
                   }
                   $mac_address = $ns->mac_address;
                   print "mac=$mac_address domain=$domain\\$machine user=$user";
         }
         print "\n";



