#!/bin/sh  
# /opt/nac/bin/monitor2.sh
#
# <1> 09.07.07 Sean Boran
#
# @package             FreeNAC
# @author              Sean Boran (FreeNAC Core Team)
# @copyright           2006 FreeNAC
# @license             http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
# @version             SVN: $Id$
# @link                http://www.freenac.net
# 
# 

subject="FreeNAC warning: switch communication"
PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/sbin:/usr/local/bin
tempfile2=/tmp/monitor2.$$

messagecount=10;

/opt/nac/bin/logtail /var/log/messages /var/log/.messages.logtail1 | egrep "MACNOT|00000000" > $tempfile2 2>&1

# Alert by email and senting to log in DB
if [ -s $tempfile2 ] ; then

  # Log events to vmpslog table, so GUI can see it.
  #echo "warning: switch communication" | /opt/nac/bin/vmps_log

  echo " " >> $tempfile2
  echo "This email was generated from the root cron on `uname -n` by $0" >> $tempfile2

  mailx -s "`uname -n` $subject" root < $tempfile2
fi

rm $tempfile2 >/dev/null

