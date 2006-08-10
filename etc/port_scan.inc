<?php
#$nmap_flags="-A -sS -sU -P0 -R";	#Default, full scan (TCP & UDP, banner grabbing, host resolution
$nmap_flags="-A -sS -R";		#No UDP
$scan_directory="/opt/vmps/scan/";	#Directory where the output from nmap will be placed
$debug_flag1=TRUE;			#Debugging level 1. Sends messages to standard output
$debug_to_syslog=FALSE;		        #Set it to TRUE if you want all the messages in syslog	
?>
