#!/usr/bin/perl
#
# /secure/logcheck/tidy_syslog.pl
#
# <3> Sean Boran, 23.3.04: add virus messages
# <2> Sean Boran, 16.2.04: only check service name. Don't print decor.
# <1> Stefan grundschober, 19.6.2003. Original
#
# If the date/tim/process are identical for consecutive message, then only
# print the message, to make it easier to read.

$old_entry = "";
$old_message = "";

if ($ARGV[0] eq "-h"){
    print("Usage: $0  [-h] ['decor']\n");
    print("\tTakes stdin, and removes similar line headings (like date and process of syslog)\n");
    print("\tIt should handle syslog and apache error_log.\n");
    print("\tIf the input doesn't match the predefined pattern, the line is printed without modifications.\n");
    print("\t-h this info\n");
    print("\t'decor': text prepended to each new input group, like '###'\n");
    print("\tdetails: syslog  match '(^.*\] )(.*\$)' \n");
    print("\t         apache  match '(^[.*\] \[.*\] )(.*\$)'\n");
    exit 0;
}


while($line = <STDIN>) {
    chop($line);
    $found=0;
    if ($line =~ /(^\[.*\] \[.*\] )(.*$)/) {
	# apache regexp: [Mon Jun 16 13:54:38 2003] [error] [client 127.0.0.1] message
	$redundant = $1;
	$message = $2;
	$found=1;

    #}elsif ($line =~ /(^.*\] )(.*$)/) {
    ## <2> Syslog:
    ## pick out the service name /pid as the unique identifier, so that
    ## sequential messages with different times are grouped together.
    ## Syslog messages can vary in format, so be have a few regexps:

    }elsif ($line =~ /^\S+ +\d+ (\d\d:\d\d):\d\d (\S+) .*SEV=\d+ (AUTH.*)/) {
	# syslog regexp0: Feb 16 10:54:56 cisco0 3089 02/16/2004 10:31:30.630 SEV=5 AUTH/36 RPT=1093 193.5.227.81  User [ SNMP ] Protocol [ SNMP ]  attempted ADMIN logon. Status: <ACCESS GRANTED>
	$time=$1; $redundant = $2; $message = $3; $found=1;

    }elsif ($line =~ /\S+ +\d+ (\d\d:\d\d):\d\d (.*\]): \[.*\] *(.*$)/) {
	# syslog regexp1: Feb 16 10:21:59 host2 ss_had[25070]: [ID 728201 daemon.notice] Screen f1 (172.17.17.211) is ACTIVE
	$time=$1; $redundant = $2; $message = $3; $found=1;

    }elsif ($line =~ /\S+ +\d+ (\d\d:\d\d):\d\d (.*\]) (.*$)/) {
	# syslog regexp2: Feb 16 01:25:02 host1 sas_all_monitor.sh: [ID 702911 user.notice]  Connection to host2 closed by remote host
	$time=$1; $redundant = $2; $message = $3; $found=1;

    }elsif ($line =~ /^\S+ +\d+ (\d\d:\d\d):.*\%(.*): *(.*$)/) {
	# syslog regexp3: Feb 16 10:54:56 cisco2 8372: Feb 16 09:54:56: %SNMP-3-AUTHFAIL: Authentication failure for SNMP req from host 193.5.227.16
	$time=$1; $redundant = $2; $message = $3; $found=1;

    }elsif ($line =~ /^\S+ +\d+ (\d\d:\d\d):\d+ +(.*$)/) {
	# <3> syslog regexp4: Mar 23 21:20:22 server1         Found the W32/Netsky.d@MM virus !!!
	$time=$1; $redundant = " "; $message = $2; $found=1;
    }

    if($found){
	if( $old_entry ne $redundant) {
            # message changed, to print full message
	    $old_entry = $redundant;
	    # <2> print("$ARGV[0] $redundant $message\n");
	    print("$time $redundant $message\n");
            $old_message=$message;
            $count=1;

	} else {
            # process is the same, print only message
            # collect duplicates
	    if( $old_message ne $message) {
	      if ($count>1) { print("\tprevious repeated $count times\n") };
	      print("\t$message\n");
              $old_message=$message;
              $count=1;
            } else {
              # Duplicate, so count
              $count++;
	    }
	}
    } else {
        # Didn't understand this line, just print it 
	print("$line\n");
    }
}
