#!/bin/sh  
# /opt/nac/bin/monitor_allows.sh
#
# Monitor the VMPS logs and notify the Sysadmin via email of 
# if there at not at least XX "ALLOWS" per time interval.
# Called from Cron.
# The time ionterval is typicall one hour, otherwise there may not be any reconfirms, and 
# the network might be just quiet.
#
# <1> 06.01.06 Sean Boran
#
#  Copyright (C) 2006 
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
#############

subject="FreeNAC warning: new ALLOWS"
PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin
tempfile2=/tmp/monitor_allows.$$

messagecount=10;

/opt/nac/bin/logtail /var/log/messages /var/log/.messages.vmps_allows | egrep "vmpsd: ALLOW" | wc -l | awk '{if ($1 < limit) print "Warning, is Vmps working? Only " $1 " Successful authentications below threshold " limit ", since last check." }' limit=$messagecount > $tempfile2 2>&1

# tail -500 /var/log/messages | egrep "vmpsd: ALLOW" | wc -l | awk '{if ($1 < limit) print "Warning, is Vmps working? " $1 " Successful authentications since last check are below threshold " limit "." }' limit=$messagecount 

# Alert by email and senting to log in DB
if [ -s $tempfile2 ] ; then

  # Log events to vmpslog table, so GUI can see it.
  cat $tempfile2| /opt/nac/bin/vmps_log

  echo " " >> $tempfile2
  echo "This email was generated from the root cron on `uname -n` by $0" >> $tempfile2

  mailx -s "`uname -n` $subject" root < $tempfile2
fi

rm $tempfile2 >/dev/null

