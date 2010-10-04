#!/bin/sh  
# /opt/nac/bin/monitor_denies.sh
#
# Monitor the VMPS logs and notify the Sysadmin via email of 
# if there are more than $messagecount "DENY" per time interval. Called from Cron.
# The time interval is typically ~15 mins.
#
# Example cron usage:
#   */15 8-18 * * 1-5 /opt/nac/bin/monitor_denies.sh
#
# TO DO: which are linefeeds disappearing from the email? Its all on one line
#
# CHANGELOG:
#    2008.11.11 Sean Boran
#
# @package             FreeNAC
# @author              Sean Boran (FreeNAC Core Team)
# @copyright           2006 FreeNAC
# @license             http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
# @version             SVN: $Id$
# @link                http://www.freenac.net
# 
# #################################3

subject="FreeNAC warning: DENYs "
PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin
tempfile1=/tmp/monitor_denies.1.$$
tempfile2=/tmp/monitor_denies.$$

messagecount=10;      # Nr. denies per interval called from cron. Tune per site.
macstoignore="000d.b914.d220";    # Troublesome PCs to ignore when watching for DENYs

/opt/nac/bin/logtail /var/log/messages /var/log/.messages.vmps_denies | egrep "DENY" | egrep -v "$macstoignore" > $tempfile1
cat $tempfile1| wc -l | awk '{if ($1 > limit) print "Warning, is Vmps working? " $1 " DENYs above threshold " limit ", since last check." }' limit=$messagecount > $tempfile2 2>&1

#/opt/nac/bin/logtail /var/log/messages /var/log/.messages.vmps_denies | egrep "vmpsd: .*DENY" > $tempfile1
#/opt/nac/bin/logtail /var/log/messages /var/log/.messages.vmps_denies | egrep "vmpsd: .*DENY" | wc -l | awk '{if ($1 > limit) print "Warning, is Vmps working? " $1 " DENYs above threshold " limit ", since last check." }' limit=$messagecount > $tempfile2 2>&1

# tail -500 /var/log/messages | egrep "vmpsd: ALLOW" | wc -l | awk '{if ($1 < limit) print "Warning, is Vmps working? " $1 " Successful authentications since last check are below threshold " limit "." }' limit=$messagecount 

# Alert by email and senting to log in DB
if [ -s $tempfile2 ] ; then

  # Log events to vmpslog table, so GUI can see it.
  cat $tempfile2| /opt/nac/bin/vmps_log.php

  echo " " >> $tempfile2
  echo " " >> $tempfile2
  echo "DENY log entries found:" >> $tempfile2
  cat $tempfile1 >> $tempfile2
  echo " " >> $tempfile2
  echo " " >> $tempfile2
  echo "This email was generated from the root cron on `uname -n` by $0" >> $tempfile2
  MAIL_RECIPIENT=`/opt/nac/bin/config_var.php mail_user`
  #MAIL_RECIPIENT="sean@boran.com"
  if [ -n "$MAIL_RECIPIENT" ] ; then
     mailx -s "`uname -n` $subject" "$MAIL_RECIPIENT" < $tempfile2
  else
     echo "No mail_user value has been defined in the config table, dumping report on screen"
     cat $tempfile2
     exit 1;
  fi
fi

rm $tempfile1 $tempfile2 >/dev/null

