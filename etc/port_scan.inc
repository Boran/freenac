<?php
/**
 * /opt/nac/etc/port_scan.inc
 *
 * Long description for file:
 * ... 
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Héctor Ortiz (FreeNAC Core Team)
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link				http://www.freenac.net
 *
 */

$nmap_flags="-A -sS -sU -P0 -R";	#Default, full scan (TCP & UDP, banner grabbing, host resolution
#$nmap_flags="-A -sS -R";		#No UDP
$scan_directory="/opt/vmps/scan/";	#Directory where the output from nmap will be placed
$debug_flag1=TRUE;			#Debugging level 1. Sends messages to standard output
$debug_to_syslog=FALSE;		        #Set it to TRUE if you want all the messages in syslog	
?>
