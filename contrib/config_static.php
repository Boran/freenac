<?php
include_once('../web1/config.inc');
include_once('../web1/functions.inc');
db_connect();

$sel = "select distinct(switch) from port where default_vlan > 0;";

$res = mysql_query($sel) or die("Unable to make query");

if (mysql_num_rows($res) < 1) { die("No switch in the DB !?"); };

while ($switch = mysql_fetch_array($res)) {

	$switch_ip = $switch['switch'];
	$switch_name = get_switch_name($switch_ip);
	$script_file = "../web1/tmp/$switch_name";

	$script = '#!/usr/bin/expect -f'."\n";
	$script .= 'set timeout -1\n\n'."\n";
	$script .= "# connect to switch"."\n";
	$script .= 'spawn telnet '.$switch_ip."\n";

// authentication - only ok for lab
	$script .= "\n#Authenticate\n";
	$script .= 'expect "Username: $"'."\n";
	$script .= 'send "ADMINUSER\n"'."\n";
	$script .= 'expect "Password: $"'."\n";
	$script .= 'send "ADMINPASSWORD\n"'."\n";

// the two prompts we should expect 
	$expect_cmd = 'expect "'.$switch_name;
	$expect_config = $expect_cmd.'(config)#$"'."\n";
	$expect_config_if = $expect_cmd.'(config-if)#$"'."\n";
	$expect_cmd .= '#$"'."\n";

// wait
	$script .= $expect_cmd;
	$script .= 'send "conf t\n"'."\n";

// now, let's query all ports

	$selp = "SELECT * FROM port WHERE switch='$switch_ip' AND default_vlan > 0;";
	$resp = mysql_query($selp) or die("Unable to make query");
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
