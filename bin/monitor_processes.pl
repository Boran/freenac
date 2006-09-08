#!/bin/perl 
# 
# name     : /secure/monitor_processes.pl
#
# History :
#            <6> Jan'9'06  (S.Boran) Combine Solaris/linux versions
#            <5> Apr.18'05 (S.Boran) search for syslog, not syslogd
#                so it will work with syslog-ng too.
#            <5> Aug.04'00 (S.Boran)
#                fix $logger
#            <4> Apr.26'00 (S.Boran)
#                Adapt for RedHat6.1 and OpenBSD2.6
#            <3> V1.3 Apr.20'99 (S.Boran)
#                Bug fix: did not detect missing inetd
#            <2> V1.2 Oct.15'93 (S.Boran)
#		 Fix: process running more that 99 mins not seen
#            <1> V1.1 Oct.14'93 (Sean AT Boran DOT com)
#		 FCS: tested on 4.1.3 & 5.2 SunOS
#
# FUNCTION: 	Check to see if a list of processes are running.
#		If not, send a message to syslog (if syslog is not
#		running, send a mail).
#		[syslogd is monitored even if not listed]
#		The list is given on the command line, but also
#		has defaults set below.
 
$user = 'root';

# --- security precautions ---
$ENV{'PATH'} = '/bin:/usr/bin';
$ENV{'SHELL'} = '/bin/sh' if $ENV{'SHELL'} ne '';
$ENV{'IFS'} = '' if $ENV{'IFS'} ne '';
umask(077);				# -rw-------

# ----------------- variable setup  ---------------
$debug = '';			# '1' for debug, '' for no debug info
$debug2 = '';			# very detailed debugging
$host = `uname -n`;
$subject = "Processes dead on $host";
chop($host);
$syslog_priority = 'warning|daemon' ;
$/="\n";                                        # record seperator
$this_process="$0";

@proc_list = @ARGV;
if (@proc_list == 0) {		# are there any args ? set defaults
  @proc_list = ('inetd','sshd');	
}

#push(@proc_list, 'syslogd');    # ALWAYS check for syslog - we use it!
push(@proc_list, 'syslog');    # ALWAYS check for syslog - we use it!

print "Searching for: @proc_list\n" if $debug;
# Reset counters
foreach $process (@proc_list) {
	$event_count{$process} = 0;
}

## OS specific settings
chop($os_ver=`uname -r`);     chop($os=`uname -s`);
print "OS=$os, version $os_ver detected.\n" if $debug;
if ($os =~ /BSD/) {
  $mail='/usr/bin/mailx';
  $ps_options = '/bin/ps -ax';
  $logger='/usr/bin/logger ';
  $pattern = '(.+ +\d+:\d\d.\d\d )(.*)'; 	
}
elsif ($os =~ /Linux/) {
  $mail='/bin/mail';
  $logger='/bin/logger ';
  #$pattern = '(.+ +\d+:\d\d.\d\d )(.*)'; 	
  $pattern = '(.+ +\d+:\d\d )(.*)';
  #$ps_options = '/bin/ps -ax';
  $ps_options = '/bin/ps ax';
}
else {  ## assume Sun
  if ($os_ver =~ /4\.1\.\d/) {
    $mail='/usr/ucb/mail';		
    $logger='/usr/ucb/logger ';
    $ps_options = '/bin/usr/ps -ax';
    $pattern = '(.+ +\d+:\d\d )(.*)'; 	
  }
  elsif ($os_ver =~ /5\.\d/) {	# assume Solaris 2 (SVR4)
    $mail='/usr/bin/mailx';
    $logger='/usr/ucb/logger ';
    $ps_options = '/usr/bin/ps -ef';
    $pattern = '(.+ +\d+:\d\d )(.*)'; 	
    print "OS= Solaris 2.x\n" if $debug;
  }
  else {
    print "OS=$os, version $os_ver is not supported!\n";
    exit -1;
  }
}


print "Searching for: @proc_list\n" if $debug;

# ---------- call "ps" & analyse output  -------------
open(PS, "$ps_options |") || die "can't run ps: __FILE__ $!\n";

while ($_ = <PS>) {
    print "Pattern=$pattern, line=$_" if $debug2;
    # <3> don't want to analyse my own process!!
    #next if (/monitor_processes.pl/);
    next if (/$this_process/);

    # $pattern is defined above.
    # $1 = anything spaces manydigits : digit digit onespace <2>
    # $2 = rest of line
    if (/$pattern/) {		 
      print "Process: '$2'\n" if $debug2;
      foreach $process (@proc_list) {
   	if ($2 =~ /$process/) {
	    $event_count{$process}++;	# count occurrences
            print "Found !\n" if $debug2;
	}
      }
    }
}
close(PS);

	# now inform about any process not running.
	# If syslog is running use 'logger' else
	# send email to root

$tmp_var="";
foreach $process (@proc_list) {
  if ($event_count{$process} == 0) {		
     $tmp_var = $tmp_var . "WARNING: Process '$process' is NOT running!\n";

     print "WARNING: Process '$process' is NOT running!\n" if $debug;
  }
  elsif ($debug) {
    print "Process '$process' occurred $event_count{$process} times\n";
  }
}

if ($event_count{'syslog'} != 0){    # syslog OK!
    # syslog is last in @proc_list
    # &syslog() doesn't work on solaris 2..

    foreach $process (@proc_list) {
	if ($event_count{$process} == 0) {
	   system("$logger -p daemon.err Process: '$process' NOT running!\n");
           ## send an email too:
	   system "echo '$tmp_var' | $mail -s '$subject' $user";
	}
	elsif ($debug) {
	    print "Process '$process' occurred $event_count{$process} times\n";
	}
    }
}
else {							# syslog is dead!
    if (! $debug) {
       system "echo '$tmp_var' | $mail -s '$subject' $user";
       system("$logger -p daemon.err Process: '$process' NOT running!\n");
    }
}

#EOF
