#!/bin/sh  
# nac/bin/monitor_allows_count.sh
#
# Performance/load measurement: list the number of times on the day
# when there were more than 15 authentications in a second, i.e. when we were
# under load.
#
# Typically this would be run from cron, just before purging the syslogs.
# If you want to run it at regular time intervals, not linked to
# log purging, then use "logtail", not "cat" (see below).
#
# 2008.11.21 Sean Boran
#   Do not sort by count, time is better. Threshold: 10>15
# 2006.05.18 Sean Boran
#
# To do: make the threshold (15) variable, rewrite this script in PHP,
#        and read the threshold value from config.inc, the email destination too.
#
#  Copyright (C) 2006 
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
#############

subject="VMPS authentication statistics"
PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin
tempfile2=/tmp/monitor_allows.$$

#/opt/vmps/logtail /var/log/messages /var/log/.messages.vmps_count | egrep "vmpsd: (ALLOW|DENY)" | awk '{print $1 " " $2 " " $3}' | uniq -c | sort -n >> $tempfile2

## authentications per minute:
# cat /var/log/messages | egrep "vmpsd: (ALLOW|DENY)" | awk '{print $1 "-" $2 "-" $3}' | cut -d: -f 1-2 | sort -n | uniq -c | more

#/opt/vmps/logtail /var/log/messages /var/log/.messages.vmps_count | egrep "vmpsd: (ALLOW|DENY)" | awk '{if (($1+0) > 15) print $1 " " $2 " " $3}' | uniq -c | sort -n >> $tempfile2

## extract vmpsd authentication messages per second and count, 
## then sort by number and show lines with more than XX/auths/sec
## Input lines are like:
##   May 18 15:50:02 INOCESvmps1 vmpsd: ALLOW: 001111b0efc3 -> sec225, switch 192.168.245.74 port 2/15
##   May 18 15:55:26 INOCESvmps2 vmpsd: ALLOW: 001111896bdf -> sec225, switch 192.168.245.87 port Fa0/1
##
## Output should look like:
## 11 May-17-13:05:59 INOCESvmps1
## 11 May-17-14:19:49 INOCESvmps2
## 11 May-17-15:06:00 INOCESvmps1
## 11 May-17-16:48:49 INOCESvmps2
## 11 May-18-03:22:56 INOCESvmps1
## 11 May-18-13:02:31 INOCESvmps1
## 11 May-18-13:06:12 INOCESvmps1
## 11 May-18-15:06:13 INOCESvmps1

#cat /var/log/messages | egrep "vmpsd: (ALLOW|DENY)" | awk '{print $1 "-" $2 "-" $3}' | uniq -c | awk '{if (($1+0) > 15) print $1 " " $2}' | sort -n

#cat /var/log/messages | egrep "vmpsd: (ALLOW|DENY)" | awk '{print $1 "-" $2 "-" $3 " " $4}' | uniq -c | awk '{if (($1+0) > 15) print $1 " " $2 " " $3}' | sort -n     >> $tempfile2
cat /var/log/messages | egrep "vmpsd: (ALLOW|DENY)" | awk '{print $1 "-" $2 "-" $3 " " $4}' | uniq -c | awk '{if (($1+0) > 15) print $1 " " $2 " " $3}'     >> $tempfile2

if [ -s $tempfile2 ] ; then
  echo " " >> $tempfile2
  echo "Performance/load measurement: list the number of times on the day when there were more than 15 authentications in a second, i.e. under load. " >> $tempfile2
  echo " " >> $tempfile2
  echo "This email was generated on `uname -n` by $0" >> $tempfile2
  echo "It reports when there were more than 15 Vmps authentications in one second." >> $tempfile2
  echo "Column1= count/sec, Column2=date, Column3=vmps server" >> $tempfile2

  logger -t vmps_authentic_statistics < $tempfile2
  MAIL_RECIPIENT=`/opt/nac/bin/config_var.php mail_user`
  if [ -n "$MAIL_RECIPIENT" ]
  then
     mailx -s "`uname -n` $subject" "$MAIL_RECIPIENT" < $tempfile2
  else
     echo "No mail_user value has been defined in the config table, dumping report on screen"
     cat $tempfile2
     exit 1;
  fi

fi

rm $tempfile2 >/dev/null

