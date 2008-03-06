#!/usr/bin/perl -w
#
# contrib/vm_mac_ip.pl
#
# Sean Boran, 31.Jan.2008
#
# The idea is to query all running Virtual Machines on
# VirtualCentre, and then update their relevant records in
# FreeNAC, or insert new records. This allows FreeNAC to have a 
# more complete inventory of devices on the network.
#
# use perl doc to see USAGE, or read the bottom.
# YOu must install the Vmware perl API, and of course have Virtual Centre.
# test with VC v2.5 on Ubuntu 7.10, Perl v5.8.8
#
# Example to list all running VMs with MAC & IP address:
# vm_mac_ip.pl --server inomgmt1 --username MYUSER --password MYPASSWORD  --powerstatus poweredOn --fields vmname,guestFullName,hostName,ipAddress,guestFamily,annotation,macAddress1 --out /tmp/vmlist.xml
# 
# Cron example, update the DB, log problems to syslog:
# 0 3 * * 1-5 /MYPATH/vm_mac_ip.pl --server MY_VC_SERVER --username MYUSER --password MYPASSWORD --powerstatus poweredOn  --out /tmp/2.xml --db opennac |logger -t vm_mac_ip.pl
#
######################################

use strict;
use warnings;

use FindBin;
use lib "$FindBin::Bin/../";
use lib "/usr/lib/vmware-viperl/apps"; # Absolute path needed

# FreeNAC DB
$Main::dbuser="MYUSER";    # adapt this
$Main::dbpass="PASSWORD1"; # adapt this
#require "/opt/inoit1/bin/config.inc.pl";  # or, import your passwords

use VMware::VIRuntime;
use AppUtil::VMUtil;
use DBI;                         # freenac
my $debug3=0;                # Live
#my $debug3=1;                # Show SQL, but do not change DB


$SIG{__DIE__}=sub{Util::disconnect()};
$Util::script_version = "1.0";

sub create_hash;
sub get_vm_info;
sub print_log;

my %field_values = (
   'macAddress1'  => 'macAddress1',
   'ipAddress1'  => 'ipAddress1',
   'guestFamily'  => 'guestFamily',
   'annotation'  => 'annotation',

   'vmname'  => 'vmname',
   'numCpu'  =>  'numCpu',
   'memorysize' => 'memorysize' ,
   'virtualdisks' => 'virtualdisks',
   'template' => 'template',
   'vmPathName'=> 'vmPathName',
   'guestFullName'=> 'guestFullName',
   'guestId' => 'guestId',
   'hostName' => 'hostName',
   'ipAddress' => 'ipAddress',
   'toolsStatus' => 'toolsStatus',
   'overallCpuUsage' => 'overallCpuUsage',
   'hostMemoryUsage'=> 'hostMemoryUsage',
   'guestMemoryUsage'=> 'guestMemoryUsage',
   'overallStatus' => 'overallStatus',
);

my %toolsStatus = (
   'toolsNotInstalled' => 'VMware Tools has never been installed or has '
                           .'not run in the virtual machine.',
   'toolsNotRunning' => 'VMware Tools is not running.',
   'toolsOk' => 'VMware Tools is running and the version is current',
   'toolsOld' => 'VMware Tools is running, but the version is not current',
);

my %overallStatus = (
   'gray' => 'The status is unknown',
   'green' => 'The entity is OK',
   'red' => 'The entity definitely has a problem',
   'yellow' => 'The entity might have a problem',
);

my %opts = (
   'vmname' => {
      type => "=s",
      help => "The name of the virtual machine",
      required => 0,
   },
   'guestos' => {
      type => "=s",
      help => "The guest OS running on virtual machine",
      required => 0,
   },
   'ipaddress' => {
      type => "=s",
      help => "The IP address of virtual machine",
      required => 0,
   },
   'datacenter' => {
      type     => "=s",
      variable => "datacenter",
      help     => "Name of the datacenter",
      required => 0,
   },
   'pool'  => {
      type     => "=s",
      variable => "pool",
      help     => "Name of the resource pool",
      required => 0,
   },
   'host' => {
      type      => "=s",
      variable  => "host",
      help      => "Name of the host" ,
      required => 0,
   },
   'folder' => {
      type      => "=s",
      variable  => "folder",
      help      => "Name of the folder" ,
      required => 0,
   },
   'powerstatus' => {
      type     => "=s",
      variable => "powerstatus",
      help     => "State of the virtual machine: poweredOn or poweredOff",
   },
   'fields' => {
      type => "=s",
      help => "To specify vm properties for display",
      required => 0,
   },
   'out'=>{
      type => "=s",
      help => "The file name for storing the script output",
      required => 0,
   },
   'db'=>{
      type => "=s",
      help => "Update the FreeNAC database with VM details",
      required => 0,
   },
);

Opts::add_options(%opts);
Opts::parse();
Opts::validate(\&validate);

my @valid_properties;
my $filename;
my $dbh;

Util::connect();

if (defined (Opts::get_option('db'))) {
  my $dbname=Opts::get_option('db');  # are we already connected?
  $dbh = DBI->connect( "DBI:mysql:$dbname", $Main::dbuser, $Main::dbpass,
                        { RaiseError => 1, AutoCommit => 0 }
                      ) || die "Database error: $DBI::errstr";

  get_vm_info();
  $dbh->disconnect();

} else {
  get_vm_info();
}
Util::disconnect();


sub get_vm_info {
   my $filename;
   my %filter_hash = create_hash(Opts::get_option('ipaddress'),
                              Opts::get_option('powerstatus'),
                              Opts::get_option('guestos'));

   my $vm_views = VMUtils::get_vms ('VirtualMachine',
                                      Opts::get_option ('vmname'),
                                      Opts::get_option ('datacenter'),
                                      Opts::get_option ('folder'),
                                      Opts::get_option ('pool'),
                                      Opts::get_option ('host'),
                                     %filter_hash);
   if ($vm_views) {
      if (defined (Opts::get_option('out'))) {
         $filename = Opts::get_option('out');
         my $extension = lc substr($filename, length($filename)-4, 4);
         if($extension ne '.xml') {
            $filename =$filename.'.xml';
         }
      }

   my $vms_found='';  # track list of VMs updated in freenac
   foreach (@$vm_views) {
      my $vm_view = $_;
      if (defined (Opts::get_option('out'))) {
         print OUTFILE "<VM>\n";
      }
      else {
         Util::trace(0,"\nInformation of Virtual Machine ". $_->name." \n\n");
      }

      ##
      ## FreeNAC DB update
      ## 
      if (defined (Opts::get_option('db'))) {
        my $dbname=Opts::get_option('db');  # are we already connected?

        my $sql='';
        my $sql1='';
        my $sql2='';
        my $sql3='';
        my $trimmed_name=$_->name;
        $trimmed_name =~ s/^\s*(.*\S)\s*$/$1/;

        ## 1. Does this VM have an active MAC address (to key into FreeNAC)
        if (defined ($vm_view->guest->net)) {
           my $net_len = @{$vm_view->guest->net};
           my $cnt = 0;

           while ($cnt < $net_len) {  # interate though network interfaces
             if ( (defined $vm_view->guest->net->[$cnt]->macAddress)
                && ($vm_view->guest->net->[$cnt]->connected==1)) {

                # we have MAC: this is our key (lower case)
                # MAC is lowercase, and format 0050.5683.5964
                my $mac=lc($vm_view->guest->net->[$cnt]->macAddress);
                $mac =~ tr/://d;    # remove colon
                $mac = substr($mac, 0,4) . '.' .substr($mac, 4,4). '.' .substr($mac, 8,4);

                # Insert a new record, if not update the current one
                $sql1 = "INSERT into systems SET r_timestamp=NOW(), mac='" .$mac ."'"
                  .", name='" .$trimmed_name ."'";
                $sql3 =" ON DUPLICATE KEY UPDATE r_timestamp=NOW() ";
         
                #$sql2 =", name='" .$trimmed_name ."'";   # should name be updated?
                $sql2 =", last_hostname='" .$trimmed_name ."'";
                $sql2.=", class=20, status=3";   # 20=VM, 3=unmanaged

                # IP address
                if (defined $vm_view->guest->net->[$cnt]->ipAddress) {
                  my $ip_len1 = @{$vm_view->guest->net->[$cnt]->ipAddress};
                  # only take the first IP on that interface
                  if ($ip_len1 > 0) {
                    $sql2.= ", r_ip='" .$vm_view->guest->net->[$cnt]->ipAddress->[0] ."'";
                  }
                  #my $cnt_ip = 0;
                  #while ($cnt_ip < $ip_len1) {
                  #  $sql.=", rip='" $vm_view->guest->net->[$cnt]->ipAddress->[$cnt_ip] ."'";
                  #  $cnt_ip++;
                  #}
                }                            

                if (defined ($vm_view->summary->guest->guestFullName)) {
                  $sql2.=", os4='" .$vm_view->summary->guest->guestFullName ."'";
                }
                my $comment='';
                ## next is the network
                if (defined $vm_view->guest->net->[$cnt]->network) {
                  $comment=$vm_view->guest->net->[$cnt]->network .", ";
                  # to do: compare the network with vlan names & set the lastvan in freeNAC
                }   
                if (defined ($vm_view->summary->config->annotation)){
                  my $anno=$vm_view->summary->config->annotation;
                  $anno =~ tr/\'\"\n\r\!/./;       # remove characters that might cause SQL issues
                  $sql2.=", comment='AutoUpdated from VC:" .$comment .$anno ."'";
                }

              } else {
               #print_log("Not Known","macAddress1","MAC Address$cnt");
              }

              $sql= $sql1 . $sql2 .$sql3 .$sql2;
 	      my $sth=$dbh->prepare($sql);
              if ($debug3) {
                Util::trace(0, "DEBUG MODE (sql not executed): $sql \n\n");
              } else {
 	        $sth->execute();
              }
              $cnt++; # next interface

            }         # while net
          }           # if net      

          if ($sql eq '') {   
            Util::trace(0, "WARNING: Unable to retrieve Settings for $trimmed_name: ");
                if (defined ($vm_view->summary->config->annotation)){
                  Util::trace(0, $vm_view->summary->config->annotation);
                }
            Util::trace(0, "\n");
          } else {
            $vms_found.= $trimmed_name .' ';
          }
         

      }
      else {
         #Util::trace(0,"\nDB option not enabled \n");
      }

      foreach (@valid_properties) {
         if ($_ eq 'vmname') {
            if (defined ($vm_view->config->name)) {
               print_log($vm_view->config->name,"Name","Name");
            }
            else {
               print_log("Not Known","Name","Name");
            }
         }
         elsif($_ eq 'numCpu') {
            if (defined ($vm_view->summary->config->numCpu)) {
               print_log($vm_view->summary->config->numCpu,
                                     "noCPU","No. of CPU(s)");
            }
            else {
               print_log("Not Known","noCPU","No. of CPU(s)");
            }
         }
         elsif($_ eq 'memorysize') {
            if (defined ($vm_view->summary->config->memorySizeMB)) {
               print_log($vm_view->summary->config->memorySizeMB,
                                            "memorySize","Memory Size");
            }
            else {
               print_log("Not Known","memorySize","Memory Size");
            }
         }
         elsif($_ eq 'virtualdisks') {
            if (defined ($vm_view->summary->config->numVirtualDisks)) {
               print_log($vm_view->summary->config->numVirtualDisks,
                                           "virtualDisks","Virtual Disks");
            }
            else {
               print_log("Not Known","virtualDisks","Virtual Disks");
            }
         }
         elsif($_ eq 'template') {
            if (defined ($vm_view->summary->config->template)) {
               print_log($vm_view->summary->config->template,"template","Template");
            }
            else {
               print_log("Not Known","template","Template");
            }
         }
         elsif($_ eq 'vmPathName') {
            if (defined ($vm_view->summary->config->vmPathName)) {
               print_log($vm_view->summary->config->vmPathName,
                                         "vmPathName","vmPathName");
            }
            else {
               print_log("Not Known","vmPathName","vmPathName");
            }
         }
         elsif($_ eq 'guestFullName') {
            if (defined ($vm_view->summary->guest->guestFullName)) {
               print_log($vm_view->summary->guest->guestFullName,"guestOS","Guest OS");
            }
            else {
               print_log("Not Known","guestOS","Guest OS");
            }
         }
         elsif($_ eq 'guestId') {
            if (defined ($vm_view->summary->guest->guestId)) {
               print_log($vm_view->summary->guest->guestId,"guestId","guestId");
            }
            else {
               print_log("Not Known","guestId","guestId");
            }
         }
         elsif($_ eq 'hostName') {
            if (defined ($vm_view->summary->guest->hostName)) {
               print_log($vm_view->summary->guest->hostName,"hostName","Host name");
            }
            else {
               print_log("Not Known","hostName","Host name");
            }
         }

         elsif($_ eq 'annotation') {
            if (defined ($vm_view->summary->config->annotation)){
               print_log($vm_view->summary->config->annotation,"annotation","annotation");
            }
            else {
               print_log("Not Known","annotation","annotation");
            }
         }

         elsif($_ eq 'guestFamily') {
            if (defined ($vm_view->guest->guestFamily)){
               print_log($vm_view->guest->guestFamily,"guestFamily","guestFamily");
            }
            else {
               print_log("Not Known","guestFamily","guestFamily");
            }
         }

         elsif($_ eq 'ipAddress1') {
	    if (defined ($vm_view->guest->net)) {
               print_log($vm_view->guest->net->[0]->ipAddress,"ipAddress1","IP Address1");
            }
            else {
               print_log("Not Known","ipAddress1","ipAddress1");
            }
         }

         elsif($_ eq 'macAddress1') {
   	 if (defined ($vm_view->guest->net)) {
           my $net_len = @{$vm_view->guest->net};
           my $cnt = 0;
           while ($cnt < $net_len) {  # interate though network interfaces
             print_log($vm_view->guest->net->[$cnt]->connected,"connected","connected$cnt");
             #print_log($vm_view->guest->net->[$cnt]->deviceConfigId,"deviceConfigId","deviceConfigId$cnt");
             if (defined $vm_view->guest->net->[$cnt]->macAddress) {
               print_log($vm_view->guest->net->[$cnt]->macAddress,"macAddress1","MAC Address$cnt");
             }
             else {
               print_log("Not Known","macAddress1","MAC Address$cnt");
             }

             if (defined $vm_view->guest->net->[$cnt]->network) {
               print_log($vm_view->guest->net->[$cnt]->network,"network","network$cnt");
             }
             else {
               print_log("Not Known","network","network$cnt");
             }

             if (defined $vm_view->guest->net->[$cnt]->ipAddress) {
               my $ip_len = @{$vm_view->guest->net->[$cnt]->ipAddress};
               my $cnt_ip = 0;
               while ($cnt_ip < $ip_len) {
                 print_log($vm_view->guest->net->[$cnt]->ipAddress->[$cnt_ip],"ipAddress$cnt-$cnt_ip","ipAddress$cnt-$cnt_ip");
                 $cnt_ip++;
               }
             }
             else {
               print_log("Not Known","network","network$cnt");
             }
             $cnt++;
           }
         }
           #    print_log($vm_view->guest->net->[0]->macAddress,"macAddress1","MAC Address1");
            else {
               print_log("Not Known","macAddress1","macAddress1");
            }
               #print_log($vm_view->guest->net->macAddress,"macAddress","MAC Address");
               #print_log($vm_view->guest->net->[0]->macAddress,"macAddress","MAC Address")
	#		if defined ($vm_view->guest->net);
               #print_log($vm_view->guest->net->[1]->macAddress,"macAddress","MAC Address")
	#		if defined $vm_view->guest->net->[1]->macAddress;
               #foreach ($vm_view->guest->net) {
               #  my $mac_view = $_;
               #  print_log($mac_view->macAddress,"macAddress","MAC Address");
               #}
         }

         #print_log($vm_view->guest->guestState,"guestState","Guest state");

         elsif($_ eq 'ipAddress') {
            if (defined ($vm_view->summary->guest->ipAddress)) {
               print_log($vm_view->summary->guest->ipAddress,"ipAddress","IP Address");
            }
            else {
               print_log("Not Known","ipAddress","IP Address");
            }
         }
         elsif($_ eq 'toolsStatus') {
            if (defined ($vm_view->summary->guest->toolsStatus)) {
               my $status = $vm_view->summary->guest->toolsStatus->val;
               print_log($toolsStatus{$status},"VMwareTools","VMware Tools");
            }
         }
         elsif($_ eq 'overallCpuUsage') {
            if (defined ($vm_view->summary->quickStats->overallCpuUsage)) {
               print_log($vm_view->summary->quickStats->overallCpuUsage.
                                           " MHz","cpuUsage","Cpu usage");
            }
            else {
               print_log("Not Known","cpuUsage","Cpu usage");
            }
         }
         elsif($_ eq 'hostMemoryUsage') {
            if (defined ($vm_view->summary->quickStats->hostMemoryUsage)) {
               print_log($vm_view->summary->quickStats->hostMemoryUsage.
                               " MB","hostMemoryUsage","Host memory usage");
            }
            else {
               print_log("Not Known","hostMemoryUsage","Host memory usage");
            }
         }
         elsif($_ eq 'guestMemoryUsage') {
            if (defined ($vm_view->summary->quickStats->guestMemoryUsage)) {
               print_log($vm_view->summary->quickStats->guestMemoryUsage.
                             " MB","guestMemoryUsage","Guest memory usage");
            }
            else {
               print_log("Not Known","guestMemoryUsage","Guest memory usage");
            }
         }
         elsif ($_ eq 'overallStatus') {
            my $overall_status = $vm_view->summary->overallStatus->val;
            print_log($overallStatus{$overall_status},"overallStatus","Overall Status");
         }
         else {
            Util::trace(0, "$_ Not Supported\n");
         }
       }
       if (defined (Opts::get_option('out'))) {
          print OUTFILE  "</VM>\n";
       }
    }
    if (defined (Opts::get_option('out'))) {
       print OUTFILE  "</Root>\n";
    }

    Util::trace(0, "Vm details updated in FreeNAC DB: $vms_found");
  }
}

sub print_log {
   my ($propvalue, $xmlprop, $prop) = @_;
   if (defined (Opts::get_option('out'))) {
      print OUTFILE  "<".$xmlprop.">" . $propvalue
                     ."</".$xmlprop.">\n";
   }
   else {
      Util::trace(0, $prop.":\t\t ".$propvalue." \n");
   }
}

sub create_hash {
   my ($ipaddress, $powerstatus, $guestos) = @_;
   my %filter_hash;
   if ($ipaddress) {
      $filter_hash{'guest.ipAddress'} = $ipaddress;
   }
   if ($powerstatus) {
      $filter_hash{'runtime.powerState'} = $powerstatus;
   }
   if ($guestos) {
      $filter_hash{'config.guestFullName'} ='.*' . $guestos . '.*';
   }
   return %filter_hash;
}


# validate the host's fields to be displayed
# ===========================================
sub validate {
   my $valid = 1;
   my @properties_to_add;
   my $length =0;

   if (Opts::option_is_set('fields')) {
      my @filter_Array = split (',', Opts::get_option('fields'));
      foreach (@filter_Array) {
         if ($field_values{ $_ }) {
            $properties_to_add[$length] = $field_values{$_};
            $length++;
         }
         else {
            Util::trace(0, "\nInvalid property specified: " . $_ );
         }
      }
      @valid_properties =  @properties_to_add;
      if (!@valid_properties) {
         $valid = 0;
      }
   }
   else {
      @valid_properties = ("vmname",
                           "numCpu",
                           "memorysize",
                           "virtualdisks",
                           "template",
                           "vmPathName",
                           "guestFullName",
                           "guestId",
                           "hostName",
                           "ipAddress",
                           "toolsStatus",
                           "overallCpuUsage",
                           "hostMemoryUsage",
                           "guestMemoryUsage",
                           "overallStatus",
  
         		   'macAddress1', 'guestFamily', 'annotation',
                            );
   }
   if (Opts::option_is_set('out')) {
     my $filename = Opts::get_option('out');
     if ((length($filename) == 0)) {
        Util::trace(0, "\n'$filename' Not Valid, zero length:\n$@\n");
        $valid = 0;
     }
     else {
        #if ( !(-e $filename && -r $filename && -T $filename)) {
        #   # file exist, readable, text
        #   Util::trace(0, "\n'$filename' Not Valid: exists/readable/text\n$@\n");
        #   $valid = 0;
        #}
        #elsif ($filename eq 'stdout') {  # send to stdout
        if ($filename eq 'stdout') {  # send to stdout
          open(OUTFILE, ">&STDOUT");
          print OUTFILE  "<?xml version='1.0'?>\n";
          print OUTFILE  "<Root>\n";
        }
        else {
          open(OUTFILE, ">$filename") || die "Cannot open $filename";
          print OUTFILE  "<?xml version='1.0'?>\n";
          print OUTFILE  "<Root>\n";
        }
     }
  }
  return $valid;   
}   

__END__

=head1 NAME

vminfo.pl - List the properties of the virtual machines.

=head1 SYNOPSIS

 vminfo.pl [options]

=head1 DESCRIPTION

This VI Perl command-line utility provides an interface for displaying
the specified attributes of the virtual machine(s). If none are specified
then the predefined parameters are displayed.

=head1 OPTIONS

=head2 GENERAL OPTIONS

=over

=item B<vmname>

Optional. The name of the virtual machine. It will be used to select the
virtual machine.

=item B<guestos>

Name of the operating system running on the virtual machine. For example,
if you specify Windows, all virtual machines running Windows are displayed. 

=item B<ipaddress>

Optional. ipaddress of the virtual machine.

=item B<datacenter>

Optional. Name of the  datacenter for the virtual machine(s). Parameters of the
all the virtual machine(s) in a particular datacenter will be displayed

=item B<pool>

Optional. Name of the resource pool of the virtual machine(s).
Parameters of the all the virtual machine(s) in the given pool will be displayed.

=item B<folder>

Optional. Name of the folder which contains the virtual machines

=item B<powerstatus>

Optional. Powerstatus of the virtual machine. If e.g. poweron is given
parameters of all the virtual machines which are powered on will be displayed

=item B<host>

Optional. Hostname for selecting the virtual machines. Parameters of all
the virtual machines in a particular host will be displayed.

=item B<fields>

Optional. Name of the fields whose value is to be displayed. The fields
are name, numCpu, guestFullName, guestId, hostName, ipAddress, toolsStatus,
memorysize, hostMemoryUsage, guestMemoryUsage, overallCpuUsage, vmPathName.
If the fields option is not specified then all the properties will be
displayed.

=item B<out>

Optional. Filename in which output is to be displayed. If the file option
is not given then output will be displayed on the console.

=back

=head1 EXAMPLES

Displays all the attributes the virtual machine myVM:

 vminfo.pl --url https://<ipaddress>:<port>/sdk/webService
           --username myuser --password mypassword --vmname myVM

Displays all the attributes of all the virtual machines in folder myFolder:

 vminfo.pl --url https://<ipaddress>:<port>/sdk/webService
           --username myuser --password mypassword --folder myFolder

Displays specified attributes of all them virtual machines in pool myPool:

 vminfo.pl --url https://<ipaddress>:<port>/sdk/webService --username myuser --password mypassword --pool myPool
           --fields vmname,numCpu,guestFullName,hostName,ipAddress,toolsStatus,memorysize,hostMemoryUsage,guestMemoryUsage

Send the output in a file

 vminfo.pl --url https://<ipaddress>:<port>/sdk/webService --username myuser
           --password mypassword  --host myHost --out output.xml

Sample Output

 Name:                    007
 No. of CPU(s):           1
 Memory Size:             784
 virtualdisks:            0
 template:                0
 vmPathName:              [storage1] 007/007.vmx
 Guest OS:                Microsoft Windows XP Professional
 Host name:               VM10.abc.info
 IP Address:              127.0.0.1
 VMware Tools:            VMware Tools is running and the version is current
 Host memory usage:       161 MB
 Guest memory usage:      23 MB
 Cpu usage:               45 MHz

=head1 SUPPORTED PLATFORMS

All operations work with VMware VirtualCenter 2.0.1 and VMware ESX server 3.0.1

