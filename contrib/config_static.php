<?php
include_once('../web1/config.inc');
include_once('../web1/functions.inc');
#include_once('config_switch.inc');
db_connect();

$sel = "select * from switch;"; 

$res = mysql_query($sel) or die("Unable to make query");
$write = TRUE;

if (mysql_num_rows($res) < 1) { die("No switch in the DB !?"); };

while ($switch = mysql_fetch_array($res)) {
	echo $switch['name']."\n";
	$switch_id = $switch['id'];
	$switch_ip = $switch['ip'];
	$switch_name = $switch['name'];
	$script_file = "../web1/tmp/$switch_name";

	$script = '#!/usr/bin/expect -f'."\n";
	$script .= 'set timeout -1\n\n'."\n";
	
	
	// protocol specific connection strings
	$ssh_connect = 'spawn ssh -l ' . $switch_user . ' ' . $switch_ip . "\n";
	$ssh_connect .= 'expect "password: $"' . "\n";
	$ssh_connect .= 'send "' . $switch_password . '\n"' . "\n";
	
	$telnet_connect = 'spawn telnet ' . $switch_ip . "\n"; 
	$telnet_connect .= 'expect "Username: $"' . "\n";
	$telnet_connect .= 'send "' . $switch_user . '\n"' . "\n";
	$telnet_connect .= 'expect "Password: $"' . "\n";
	$telnet_connect .= 'send "' . $switch_password . '\n"' . "\n";
	
	
	// choose proper default setting for connection method 
	$script .= "# connect to switch"."\n";
	if ( $switch_default_access == 'ssh' ) {
		$script .= 'if { $argc > 0 &&  [lindex $argv 0] eq "-t" } {' . $telnet_connect . '}' . "\n";
		$script .= 'else { ' . $ssh_connect . '}' . "\n";
	}
	else {
		$script = 'if { $argc > 0 &&  [lindex $argv 0] eq "-s" } {' . $ssh_connect . '}' . "\n";
		$script .= 'else { ' . $telnet_connect . '}' . "\n";
	}
		
	
	// change to privilege level 15 if necessary
	$script .= "expect {\n";
	$script .= "\t" . '"' . $switch_name . '>$" { send "ena\n"; expect "Password: $"; send "' . $switch_enable_password . '\n"; expect "' . $switch_name . '#$"}' . "\n";
	$script .= "\t" . '"' . $switch_name . '#$"' . "\n";
	$script .= '}' . "\n";


	// enter configuration mode
	$script .= 'send "conf t\n"'."\n";


	// the two prompts we should expect 
	$expect_cmd = 'expect "'.$switch_name;
	$expect_config = $expect_cmd.'(config)#$"'."\n";
	$expect_config_if = $expect_cmd.'(config-if)#$"'."\n";
	$expect_cmd .= '#$"'."\n";


	// now, let's query all ports
	$selp = "SELECT * FROM port WHERE switch=$switch_id AND default_vlan > 1;";
	$resp = mysql_query($selp) or die("Unable to make query ($selp)\n");
	if (mysql_num_rows($resp) > 0) {
		while ($port = mysql_fetch_array($resp)) {
			$script .= 'send "interface '.$port['name'].'\n"'."\n";
			$script .= $expect_config_if;
			$script .= 'send "sw ac vl '.$port['default_vlan'].'\n"'."\n";
       			$script .= $expect_config_if;
			$write = TRUE;
		};
	};
	
	$script .= 'send "exit\n"'."\n";
	$script .= $expect_config;
	$script .= 'send "exit\n"'."\n";
	$script .= $expect_cmd;
	$script .= 'send "quit\n"'."\n";
	$script .= 'exit'."\n";

// write to file
	if ($write) {
		$file = fopen($script_file,'w');
		fwrite($file,$script);
		fclose($file);
		debug2("$script_file written");
		$cmd = "chmod 755 $script_file";
		debug1($cmd);
		passthru($cmd);
	};
	
	$write = FALSE;

};

?>
