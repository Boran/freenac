#!/bin/sh  
# monitor_lastseen.sh
#
# Monitor the VMPS logs and notify the Sysadmin via email of 
# if there at not at least XX "lastseen" per time interval.
# Called from Cron.
# The time interval is typically daily
# 0    7    * * 1-5       /opt/nac/bin/monitor_lastseen.sh
#
# <1> 2006.06.28 Sean Boran
#
#############

subject="VMPS warning: no lastseens"
PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin
tempfile2=/tmp/monitor_allows.$$

messagecount=1;

/opt/vmps/logtail /var/log/messages /var/log/.messages.vmps_lastseen | egrep "vmps_lastseen" | wc -l | awk '{if ($1 < limit) print "Warning, is vmps_lastseen working? Only " $1 " messages below threshold " limit ", since last check." }' limit=$messagecount > $tempfile2 2>&1

# Alert by email and senting to log in DB
if [ -s $tempfile2 ] ; then

  # Log events to vmpslog table, so GUI can see it.
  #cat $tempfile2| /opt/vmps/bin/vmps_log

  mailx -s "`uname -n` $subject" root         < $tempfile2
  #mailx -s "`uname -n` $subject" sean@boran.com < $tempfile2
fi

rm $tempfile2 >/dev/null


