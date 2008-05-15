#!/usr/bin/perl
#
#
# contrib/gather1.pl
#
# Sean Boran, sometime in 2007
#
#  what can we query remotely to ID a windows box?
#  First argument is the IP
#
# You'll need the NBName package, e.g. on Ubuntu:
# apt-get install libnet-nbname-perl
#
###########################################################


$verbose=0;            ## 1=humanreadable, 0=CSV for parsing


# TBD: check that we do have an argumentr
$ip=$ARGV[0];         #  e.g. 10.10.10.11

if ($verbose==1) {print "Query=$ip: ";} else {print "$ip,";}

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
                   if ($verbose==1) {
                     print "mac=$mac_address domain=$domain\\$machine user=$user\n";
                   } else {
                     print "$mac_address,$domain\\$machine,$user";
                   }

         } else {
           if ($verbose==1) {
             print "details=unknown\n";
           } else {
             print ",,";
           }
         }



