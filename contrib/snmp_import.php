#!/usr/bin/php -- -f
<?php
/**
 * contrib/snmp_import.php
 *
 * Long description for file:
 * To be used during the deployment phase to add
 *   ports to the DB that we expect to be managed by NAC.
 * - get the actual configuration of non-trunk ports of switches and 
 *   populate the port table.  
 * - ignore ports with vlan=0, and take the current vlan so that it can be used as a port 
 *   default vlan
 * - the output is SQL that you should review before executing
 * see also README:snmp_import, snmp_defs.inc, config.inc
 * Enable $debug_flag1 and $debug_flag2 the first time you use this.
 *
 * On IOS do "show ip arp" - "show vlan"
 *        or "sh ip arp vrf insec"
 * On CatOS so "show port status"
 * Further reading: 
 *    http://www.cisco.com/public/sw-center/netmgmt/cmtk/mibs.shtml
 *    The "getif" tool for exploring MIBs.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Thomas Dagonnier (FreeNAC Core Team)
 * @copyright			2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link			http://www.freenac.net
 *
 */


# Php weirdness: change to script dir, then look for includes
chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "bin/funcs.inc";               # Load settings & common functions
require_once "snmp_defs.inc";

define_syslog_variables();              # not used yet, but anyway..
openlog("snmp_import.php", LOG_PID, LOG_LOCAL5);

// Enable debugging to understand how the script works
  $debug_flag1=false;
  $debug_flag2=false;
  $debug_to_syslog=FALSE;

if ($snmp_dryrun) {
  $debug_flag2=true;
  $domysql=false;
} else {
  $domysql=true;
};


debug2("Checking for SNMP: " . SNMP_OID_OUTPUT_FULL); // we'll gte a number if PHP SNMP is working
snmp_set_oid_numeric_print(TRUE);
snmp_set_quick_print(TRUE);
snmp_set_enum_print(TRUE); 

// allow performance measurements
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;

db_connect();

function print_usage() {
	echo "snmp_import.php - Usage\n";
	echo " -switch name- only scan given switch (requires switch name)\n";
	echo " -new A.B.C.D : to insert a new switch (requires switch IP Address)\n";
	echo "\n";

};

function validate_input($type,$input) {
// type : string, email, emaillist, IP
// retrun : TRUE/FALSE
	$valid = FALSE;
	switch($type) {
		case 'ip':
			if (($binIp = ip2long($input)) === false) {
#			if ($substr_count($input,'.') != 3) {
				$valid = FALSE;
			} else {
				$valid = TRUE;
			};
		break;
		case 'emaillist':
			$emails = explode(',',$input);
			$valid = TRUE;
			foreach ($emails as $key => $email) {
				$valid = $valid && validate_input('email',$email);
			};
		break;
		case 'email':
			if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $input)) {
  				$valid = TRUE;
			} else {
				$valid = FALSE;
			};
		break;
		case 'string':
			if (is_string($input)) {
				$valid = TRUE;
			} else {
				$valid = FALSE;
			};
	};

	return($valid);
};

function ask_user($question,$default,$type) {
print "$question ? [$default] : ";
   $out = "";
   $key = "";
   $key = fgetc(STDIN);        //read from standard input (keyboard)
   $validated = FALSE;
   while (!$validated) {
	   while ($key!="\n") {       //if the newline character has not yet arrived read another
	       $out.= $key;
	       $key = fread(STDIN, 1);
	   };
	   if ($out == '') { $out = $default; };
	   $validated = validate_input($type,$out);
	   if (!$validated) {
	       print "Invalid answer.\n$question ? [$default] : ";
		   unset($key);
           unset($out);
	   };
   };

   if ($type = 'string') { $out = mysql_real_escape_string($out); };

   return($out);
};

// command line:
//   Look for: snmp_import.php -switch sw0503
// or		   snmp_import.php -new A.B.C.D
//   otherwise scan all switches
// TODO switch instead of if, make sure there are 3 args!

	$single = FALSE;
	$newswitch = FALSE;


	for ($i=0;$i<$argc;$i++) {
	   if ($argv[$i]=='-switch') {   // even if user gives --switch we see -switch
		  $single = TRUE;
		  $singleswitch = mysql_real_escape_string($argv[$i+1]);
		};
	   if ($argv[$i]=='-new') {   // even if user gives --switch we see -switch
		  $new = TRUE;
		  $single = TRUE;
		  $newswitch = $argv[$i+1];
		};
	   if ($argv[$i]=='-help') {   // even if user gives --switch we see -switch
			print_usage();
		};
	};


// preliminary : create new switch
 // we have ip - we need name, location, comment, swgroup, contact, ap (1x - not used)
if ($new === TRUE) {
		// ugly IP Validation
	if (!validate_input('ip',$newswitch)) {
		echo "$newswitch $count\n";
		exit("Invalid IP Address\n");
	} else {
		$sql_ip = $newswitch;
	};

		// check if switch doesn't exist
	if (switch_exist('ip',$sql_ip)) {
		echo "Error : This switch exists.\nIf you want to update its ports' default vlan, please use :\n snmp_import.php -switch NAME\n";
#	} else {
	}; 
	if (1 == 1) {
		echo "Discovering the switch ($sql_ip) using SNMP ...\n";
		list($sql_name,$cisco_hw,$cisco_sw,$catos) = get_cisco_info($sql_ip,$snmp_ro);
		echo "It looks like it is a $cisco_hw running $cisco_sw.\n";

		$sql_name = ask_user("What is the hostname",$sql_name,'string');
		$sql_location = ask_user("What is the switch location",snmpget($sql_ip,$snmp_ro,$snmp_sw['location']),'string');
		$sql_contact = ask_user("Who should be notified of changes (email adress)",snmpget($sql_ip,$snmp_ro,$snmp_sw['contact']),'emaillist');

		$sql_comment = "$cisco_hw - $cisco_sw";

		$sql_comment = ask_user("What would you like for comment",$sql_comment,'string');


		$sql_swgroup = 1;
		$sql_ap = 0;

		$query = "INSERT INTO switch(ip,name,location,comment,swgroup,notify,ap) VALUES ";
		$query .= "('$sql_ip','$sql_name','$sql_location','$sql_comment',$sql_swgroup,$sql_contact,$sql_ap);";

		debug2("MySQL : $query");
		if($domysql) { mysql_query($query) or die("unable to query"); };
		$singleswitch = $sql_name;
	};
};


// Main part : import
if (!$single) {
    $switches =  mysql_fetch_all("SELECT * FROM switch");
	debug1("Scanning all switches in the Database");
} else {
    $switches =  mysql_fetch_all("SELECT * FROM switch WHERE name='$singleswitch'");
	debug1("Scanning one switch: $singleswitch");
};


if (is_array($switches)) {

	foreach ($switches as $switchrow) {
		$switch = $switchrow['ip'];
		$switchname = $switchrow['name'];
		$location = $switchrow['location'];
        	debug2("snmpwalk $switch,$switchname,$location for interfaces");

		$ifaces = walk_ports($switch,$snmp_ro);
  	    if (count($ifaces) > 0) {
		 foreach ($ifaces as $idx => $myiface) {
			if ($myiface['vmps'] && $myiface['vlan'] > 0) {
                		debug2("Vmps candidates vlan>0: $switchname interfaces " .$myiface['name'] .', vlan=' .$myiface['vlan'] );
				if (iface_exist($switch,$myiface['name'])) {
					$query = "UPDATE port SET default_vlan='".$myiface['vlan']."' WHERE ";
					$query .= "switch='$switch' AND name='".$myiface['name']."';";
				} else {
					$query = "INSERT INTO port(switch,name,default_vlan,location) VALUES (";
					$query .= "'$switch','".$myiface['name']."','".$myiface['vlan']."','$location');";

				};
				debug2("MySQL : $query");
				if($domysql) { mysql_query($query) or die("unable to query"); };
				unset($query);
			};
		 };
        };
		unset($ifaces);
	};
};

  // measure performance
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   debug1("Time taken= ".$totaltime." seconds\n");
   #logit("Time taken= ".$totaltime." seconds\n");


?>
