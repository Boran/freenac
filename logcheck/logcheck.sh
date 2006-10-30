#!/bin/sh 
#
# logcheck.sh: Log file checker
#
# Originally written by Craig Rowland <crowland@psionic.com>
# Heavily adapted by Sean Boran <sean@boran.com>
#
#	This file needs the program logtail.c to run
#	This script checks logs for unusual activity and blatant
#	attempts at hacking. All items are mailed to administrators
# 	for review. This script and the logtail.c program are based upon 
#       the frequentcheck.sh script idea from the Gauntlet(tm) Firewall
#	(c)Trusted Information Systems Inc. The original authors are 
#	Marcus J. Ranum and Fred Avolio.
#
#	Version Information
#	1.0 	9/29/96  -- Initial Release
#	1.01	11/01/96 -- Added working /tmp directory for symlink protection
#			    (Thanks Richard Bullington (rbulling@obscure.org)
#	1.1	1/03/97	 -- Made this script more portable for Sun's.
#		1/03/97	 -- Made this script work on HPUX
#               5/14/97  -- Added Digital OSF/1 logging support. Big thanks
#                           to Jay Vassos-Libove <libove@compgen.com> for
#                           his changes. 
#       1.1a    Oct26'99 sb Sean@Boran.com
#                           Allow comments and blank lines in config files
#                           Change dir from /usr/local to /secure/logcheck
#                           use mailx. Add DEBUG variable to check report
#                           via view rather than Email. Tested on Solaris2
#                           Supress the VIOLATIONS report (too many false positives)
#               03Mar'00 sb Send logcheck email with appropriate subject even if empty.
#               19.Feb.01 sb First external site
#       1.1b    08.Mar.01 sb Compress & uuencode report if great than 100k
#               09.Apr.01 sb Monitor Netscape error logs
#       1.2     07.May.01 sb Complete rewrite.
#       <2>     23.Aug.01 sb Adapt for a linux/SUSE server.
#       <3>     02.Oct.01 sb Use a general list of log files, so we don't
#                            have to customise it for different servers.
#                            Tested on Solaris 7/8, Suse 7.1
#       <4>     26.Dec.01 sb Separate general ignore pattern for all sites
#			     into a generic files logcheck.ignore.gen. Which
# 			     can be common to many sites.
#       <5>     02.Jan.02 sb Use common config file secure.conf
#       <6>     18.Sep.02 sb Add -prune option to rotate logs after analysis
#       <7>     15.jun.03 sb Change subject
#       <8>     16.feb.04 sb Add tidy_syslog to filter repeated lines
#                            (From Stephane Grundschober). Add IPv6 proxy,
#                            and FreeBSD daily logs.
#       <9>     15.sep.04 sb Tell syslog after pruning
#       <10>    17.Apr.05 sb Tell syslog-ng after pruning
#       <11>    31.Oct.06 sb Tidy up and integrate for FreeNAC
#
#########################################################

## CONFIGURATION SECTION

## Debug script with lots of additional error messages?
DEBUG=0
#DEBUG=1
[ "$DEBUG" -eq 1 ] && SYSADMIN="debug@boran.com";
#set -x


## Alerts are reported via email.
## set recipients and title of email:
SYSADMIN=root
tool="FreeNAC logcheck"

umask 077
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/ucb:/usr/local/bin
HOSTNAME=`hostname`
DATE=`date +%m/%d/%y:%H.%M`

## -- process arguments <6>
arg1="$1";
USAGE="USAGE: $0  [-h | -prune ] ";
rotatelog=0;
if   [ "$arg1" = "-prune" ]      ; then rotatelog=1;
elif [ "$arg1" = "-help" ]        ; then echo $USAGE; exit 1;
elif [ "$arg1" = "-h" ]           ; then echo $USAGE; exit 1;
fi


FROM="root"
SUBJECT1="$group $tool: no entries";
SUBJECT2="$group $tool";
SUBJECT3="$group $tool: known patterns found";

# Full path to SECURED (non public writable) /tmp directory.
# Prevents Race condition and potential symlink problems.
cd /opt/nac/logcheck
TMPDIR=/opt/nac
ROTATE="/opt/nac/logcheck/rotate_log -n 52 "
#SSH=/usr/bin/ssh
#SSH=/usr/local/bin/ssh
#SSH=/opt/OBSDssh/bin/ssh
SSH=ssh

###### Advanced config: changes rarely needed #############
temp1=$0.raw$$
temp2=$0.ns.$$;
temp3=$0.rep.$$;
temp4=$0.out.$$;

## Paths to programs:
GREP=egrep
MAIL=mailx
# Full path to logtail program.
# This program is required to run this script and comes with the package.
LOGTAIL=/opt/nac/bin/logtail
#LOGTAIL=logtail
# Debugging: use "cat" so that all entries are reported:
#LOGTAIL=cat

# Full path and arguments to tidy_syslog tool
TIDY_SYSLOG='/opt/nac/logcheck/tidy_syslog.pl ###'

# If report is larger that 800 blocks (400k), compress it before emailing.
COMPRESS_LIMIT=800
COMPRESS="gzip"

# What comments do we allow in the expression config files?
SPACE="^#|^ *$"

############ end of config section ###################

##### Parse the configuration files, i.e.
##### remove spaces and comments

HACKING_FILE1=/opt/nac/logcheck/logcheck.hacking
HACKING_FILE=/opt/nac/logcheck/.logcheck.hacking
$GREP -v "$SPACE" $HACKING_FILE1 > $HACKING_FILE

# File of security violation patterns to specifically look for.
# This file should contain keywords of information administrators should
# probably be aware of. May or may not cause false alarms sometimes.
# Generally, anything that is "negative" is put in this file. It may miss
# some items, but these will be caught by the next check. Move suspicious
# items into this file to have them reported regularly.

VIOLATIONS_FILE1=/opt/nac/logcheck/logcheck.violations
VIOLATIONS_FILE=/opt/nac/logcheck/.logcheck.violations
$GREP -v "$SPACE"  $VIOLATIONS_FILE1 > $VIOLATIONS_FILE

# File that contains more complete sentences that have keywords from
# the violations file. These keywords are normal and are not cause for 
# concern but could cause a false alarm. An example of this is the word 
# "refused" which is often reported by sendmail if a message cannot be 
# delivered or can be a more serious security violation of a system 
# attaching to illegal ports. 

VIOLATIONS_IGNORE_FILE1=/opt/nac/logcheck/logcheck.violations.ignore
VIOLATIONS_IGNORE_FILE=/opt/nac/logcheck/.logcheck.violations.ignore
$GREP -v "$SPACE"  $VIOLATIONS_IGNORE_FILE1 > $VIOLATIONS_IGNORE_FILE

# This is the name of a file that contains patterns that we should
# ignore if found in a log file. If you have repeated false alarms
# or want specific errors ignored, you should put them in here.
# Once again, be as specific as possible, and go easy on the wildcards

IGNORE_FILE1=/opt/nac/logcheck/logcheck.ignore
IGNORE_FILE=/opt/nac/logcheck/.logcheck.ignore
$GREP -v "$SPACE"  $IGNORE_FILE1 > $IGNORE_FILE

# <4>
# When several sites are managed with logcheck, we group
# together common ignore patterns for all sites in one general file:
IGNORE_GEN1=/opt/nac/logcheck/logcheck.ignore.gen
# And we append the general patterns to the site specific ones:
$GREP -v "$SPACE"  $IGNORE_GEN1 >> $IGNORE_FILE


# The files are reported in the order of hacking, security 
# violations, and unusual system events. Notice that this
# script uses the principle of "That which is not explicitely
# ignored is reported" in that the script will report all items
# that you do not tell it to ignore specificially. Be careful
# how you use wildcards in the logcheck.ignore file or you 
# may miss important entries.

## Make sure we really did clean up from the last run.
## Also this ensures that people aren't trying to trick us into
## overwriting files that we aren't supposed to. This is still a race
## condition, but if you are in a temp directory that does not have
## generic luser access it is not a problem. Do not allow this program
## to write to a generic /tmp directory where others can watch and/or
## create files!!
rm -f $temp1 $temp2 $temp3 
if [ -f $temp1 -o -f $temp2 -o -f $temp3 ]; then
  echo "Log files exist in $TMPDIR directory that cannot be removed. This 
may be an attempt to spoof the log checker." \
    | $MAIL -s "$SUBJECT3" $SYSADMIN
    #| $MAIL -s "$SUBJECT3" -r $FROM $SYSADMIN
  exit 1
fi

analyse_log () {
  [ "$DEBUG" -eq 1 ] && echo "Does $1 exist? \c"
  if [ -s "$1" ] ; then
    [ "$DEBUG" -eq 1 ] && echo " Yes. let's read it .."
    #$LOGTAIL "$1"   >> $temp1  2>/dev/null
    #$LOGTAIL "$1"   >> $temp1
    $LOGTAIL "$1" "$1.offset_logcheck1"  >> $temp1
  fi
}

# <6> prune log?
prune_log () {
  if [ -s "$1" -a $rotatelog = 1 ] ; then
    [ "$DEBUG" -eq 1 ] && echo " $1 exists. let's prune it .."

    # syslog-ng is sensitive, stop during rotate
    if [ -f /var/run/syslog-ng.pid ] ; then
      #echo "Syslog-ng running, stop, rotate, start"
      /etc/init.d/syslog-ng stop
      $ROTATE -L `dirname $1` `basename $1` 
      /etc/init.d/syslog-ng start

    else 
      $ROTATE -L `dirname $1` `basename $1` 
      if [ -f /etc/syslog.pid ] ; then
        /bin/kill -1 `cat /etc/syslog.pid`
      elif [ -f /var/run/syslog.pid ] ; then
        /bin/kill -1 `cat /var/run/syslog.pid`
      else
        # try and find pkill by path
        pkill -1 syslogd;
      fi

    fi     # else syslog-ng

  fi     # if $1
}        # function

###### LOG FILE CONFIGURATION SECTION

## Begin of Sean's log files: go through a long
## list, ignore log files not found.
analyse_log "/var/log/messages";
prune_log   "/var/log/messages";
analyse_log "/var/log/authlog";
prune_log   "/var/log/authlog";
analyse_log "/var/log/cronlog";
prune_log   "/var/log/cronlog";
analyse_log "/var/log/daemonlog";
prune_log   "/var/log/daemonlog";
analyse_log "/var/log/kernlog";
prune_log   "/var/log/kernlog";
analyse_log "/var/log/local0log";
prune_log   "/var/log/local0log";
analyse_log "/var/log/local2log";
prune_log   "/var/log/local2log";
analyse_log "/var/log/local5log";
prune_log   "/var/log/local5log";
analyse_log "/var/log/lprlog";
prune_log   "/var/log/lprlog";
analyse_log "/var/log/maillog";
prune_log   "/var/log/maillog";
analyse_log "/var/log/newslog";
prune_log   "/var/log/newslog";
analyse_log "/var/log/userlog";
prune_log   "/var/log/userlog";
# Ignore alert log, since it's covered above
#analyse_log "/var/log/alertlog";
#analyse_log "/var/log/yule_log";

# Sun:
#analyse_log "/var/adm/messages";
#prune_log   "/var/adm/messages";

# Linux:
# /var/log/messages already above
analyse_log "/var/log/localmessages";
analyse_log "/var/log/mail";
# analyse_log "/var/log/portscan.log";
# analyse_log "/var/log/SuSEconfig.log";
# analyse_log "/var/log/httpd/error_log";
# #analyse_log "/var/log/warn"
# analyse_log "/var/log/firewall";
# analyse_log "/var/log/boot.msg";
# analyse_log "/var/log/y2logRPM";
# analyse_log "/var/log/y2logRPMShort";
# analyse_log "/var/log/ftpd";
# analyse_log "/var/log/faillog";
# #analyse_log "/var/log/rsyncd.log";

#echo " "     >> $temp1
#echo "Vmps1 MySQL logs .."     >> $temp1
$LOGTAIL /mysqldata/mysqld.log /mysqldata/mysqld.log.offset.logcheck1 |egrep -v "^$" >> $temp1

#echo "Vmps2 MySQL logs .."     >> $temp1
$SSH vmps2  "$LOGTAIL /mysqldata/mysqld.log /mysqldata/mysqld.log.offset.logcheck1" |egrep -v "^$" >> $temp1

# FreeRadius
analyse_log "/usr/local/var/log/radius/radius.log";

# main analysis ##########
# Set the flag variables
FOUND=0
ATTACK=0

# See if the tmp file exists and actually has data to check, 
# if it doesn't we should erase it and exit as our job is done.
if [ ! -s $temp1 ]; then
  rm -f $temp1
  # Always send, even if empty
  #$MAIL -s "$SUBJECT1" $SYSADMIN </dev/null
  ## (for VMPS we don't want empty mails)
  exit 0
fi

###### Perform Searches

## a) Check for "positive list" = "known patterns"
if [ -f "$HACKING_FILE" ]; then
  if $GREP -i -f $HACKING_FILE $temp1 > $temp4; then
    echo                               >> $temp3
    echo "Positive Alerts" >> $temp3
    echo "=-=-=-=-=-=-=-=" >> $temp3
    cat $temp4 | $TIDY_SYSLOG              >> $temp3
    FOUND=1; ATTACK=1;
  fi
fi

## b) Check for security violations
#if [ -f "$VIOLATIONS_FILE" ]; then
#  if $GREP -i -f $VIOLATIONS_FILE $temp1 > $temp4; then
#    echo                         >> $temp3
#    echo "Security Violations"   >> $temp3
#    echo "=-=-=-=-=-=-=-=-=-="   >> $temp3
#    cat $temp4                   >> $temp3
#    FOUND=1
#  fi
#fi

## c) Do reverse grep on patterns we want to ignore
if [ -f "$IGNORE_FILE" ]; then
  if $GREP -v -f $IGNORE_FILE $temp1 > $temp4; then
    echo                         >> $temp3
    echo "Unusual System Events" >> $temp3
    echo "=-=-=-=-=-=-=-=-=-=-=" >> $temp3
#<8>    cat $temp4               >> $temp3
    cat $temp4 | $TIDY_SYSLOG    >> $temp3
    FOUND=1
  fi
fi

# If there are results, mail them to sysadmin
# if debug is set, only view, not mail results
if [ "$DEBUG" -eq 1 ] ; then
  if [ "$FOUND" -eq 1 ]; then
    view $temp3
  else
    echo "No log changes found."
  fi
else
  if [ "$ATTACK" -eq 1 ]; then
    siz=`ls -s $temp3|awk '{print $1}'`
    if [ $siz -gt $COMPRESS_LIMIT ] ; then     # 200 blocks = 100k
      #echo "Report is large, so lets compress it"
      $COMPRESS <$temp3 |uuencode $HOSTNAMEreport$$.txt.gz |\
        #$MAIL -s "$SUBJECT3" -r $FROM $SYSADMIN
        $MAIL -s "$SUBJECT3" $SYSADMIN
    else
      #cat $temp3 | $MAIL -s "$SUBJECT3" -r $FROM $SYSADMIN
      cat $temp3 | $MAIL -s "$SUBJECT3" $SYSADMIN
    fi
  elif [ "$FOUND" -eq 1 ]; then
    echo " " >> $temp3
    echo " " >> $temp3
    echo "This email was generated by `uname -n`:$0" >> $temp3
    echo " " >> $temp3
    siz=`ls -s $temp3|awk '{print $1}'`
    if [ $siz -gt $COMPRESS_LIMIT ] ; then     # 200 blocks = 100k
      #echo "Report is large, so lets compress it"
      $COMPRESS <$temp3 |uuencode $HOSTNAME$$.txt.gz |\
        $MAIL -s "$SUBJECT2" $SYSADMIN
        #$MAIL -s "$SUBJECT2" -r $FROM $SYSADMIN
    else
      #$MAIL -s "$SUBJECT2" -r $FROM $SYSADMIN  < $temp3
      $MAIL -s "$SUBJECT2" $SYSADMIN  < $temp3
    fi
  #else
  #  # Always send, even if empty
  #  #$MAIL -s "$SUBJECT1" -r $FROM $SYSADMIN </dev/null
  #  $MAIL -s "$SUBJECT1" $SYSADMIN </dev/null
  fi
fi


### Clean Up
rm -f $temp1 $temp2 $temp3 $temp4 >/dev/null

#eof
