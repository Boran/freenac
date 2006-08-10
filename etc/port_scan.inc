<?php
#$nmap_flags="-A -sS -sU -P0 -R";	#Default, full scan (TCP & UDP, banner grabbing, host resolution
$nmap_flags="-A -sS -R";		#No UDP
$scan_directory="/opt/vmps/scan/";	#Directory where the output from nmap will be placed
$debug_flag1=TRUE;
$debug_to_syslog=TRUE;
?>
