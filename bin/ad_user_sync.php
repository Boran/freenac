#!/usr/bin/php -f
<?php
/**
 * /opt/nac/bin/ldap
 *
 * Long description for file:
 * Query Microsoft Active Directory to obtain user information.
 *
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Wolfram Strauss (FreeNAC Core Team)
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
*/


# main

# initialize
require_once "funcs.inc.php";  
# configure logging
$logger->setDebugLevel(0);
$logger->setLogToStdOut();

debug1("variables from configfile: ".$conf->ad_server." | ".$ad_user." | ".$ad_password." | " . print_r($conf->ad_base_user_dn, TRUE));


#####################
#                   #
#      MAIN         #
#                   #
#####################


# Process command line  arguments
#
# There are two arguments accepted. 'test' for testing the connection to the AD server obmitting a db connection
# and 'exchange' to query certain additional attributes form the MS Exchange AD schema extension

debug1('Command line paramters: ' . print_r($argv, TRUE));

# flags
$test = FALSE;
$exchange = FALSE;

if( $argc == 2 ) {
	switch ($argv[1]) {
	case 'test':
		$test = TRUE;
		break;
	case 'exchange':
		$exchange = TRUE;
		break;
	default:
		usage();
	}
}
elseif( $argc == 3 ) {
	if( ($argv[1] == 'test' and $argv[2] == 'exchange') or ($argv[2] == 'test' and $argv[1] == 'exchange') ) {
		$test = TRUE;
		$exchange = TRUE;
	}
	else {
		usage();
	}
}
elseif( $argc > 3 ) {
	usage();
}


# start main tasks
if( $test ) {
	$logger->setDebugLevel(1);
	logit('Testing AD server connection only');
	query_AD();	
}
else {
	db_connect();
	log2db('info', 'Starting user synchronistation with Active Directory');
	$users = query_AD();
	if( $users ) { fill_db($users); }	# if $users is empty this indicates that there's been an error connecting to the AD
	else {
		logit('No data processed for DB due to LDAP errors!');
	}
}


####################
#                  #
#    FUNCTIONS     #
#                  #
####################


# Query AD and return assoc. array
function query_AD() {
	global $conf, $exchange,$ad_user,$ad_password;
	
	logit('Querying user information from AD');
	
	$connect = ldap_connect($conf->ad_server,$conf->ad_port) or logit('Could not connect to Active Directory, ' . ldap_error($connect));
	$bind = ldap_bind($connect, $ad_user, $ad_password) or logit('Could not bind to Active Directory, ' . ldap_error($connect));

	#$filter = "objectClass=person";	# There might be objects of other classes around (e.g. groups) we don't want to fetch
	$filter = "(&(objectClass=person)(!(objectClass=computer)))"; #We don't want computers either
	# AD attribute names 
	# username: sAMAccountName
	# prename: givenName
	# family name: sn
	$fetch_attributes = array('sAMAccountName','givenName','sn');
	
	# Additional Exchange schema attributes
	# department: department
	# rfc822mailbox: mail
	# houseidentifier: -
	# physicalDeliveryOfficeName: physicalDeliveryOfficeName
	# telephoneNumber: telephoneNumber
	# mobile: mobile
	if( $exchange ) {
		$fetch_attributes = array_merge($fetch_attributes, array('department', 'mail', 'physicalDeliveryOfficeName', 'telephoneNumber', 'mobile'));
	}

	$info = array();
	# loop through all dn with user information and fetch data
	foreach( $conf->ad_base_user_dn as $base_dn ) {
		#$results = ldap_list($connect, $base_dn, $filter, $fetch_attributes) or logit('Could not retrieve data, ' . ldap_error($connect));
 		//ldap_list performs the query only on one level, ldap_search searches through the whole tree
                $results = ldap_search($connect, $base_dn, $filter, $fetch_attributes) or logit('Could not retrieve data, ' . ldap_error($connect));
		$info = array_merge($info, ldap_get_entries($connect, $results)) or logit('Could not get entries, ' . ldap_error($connect));
	}
	
	ldap_close($connect);   

	debug1(print_r($info, TRUE));
	return $info;
}


# Insert new or update existing user information
function fill_db($users) {
	global $exchange,$logger;
	
	logit('Updating user table');
	
	$updates = 0;	# count number of updates done
	$inserts = 0;	# count number of inserts done
	
	foreach( $users as $i ) {
		$account = $i['samaccountname'][0];
		$given_name = $i['givenname'][0];
		$surname = $i['sn'][0];
		debug1("User attributes: $account|$given_name|$surname");
		
		# optional exchange attributes
		if( $exchange ) {
			$department = $i['department'][0];
			$rfc822mailbox = $i['mail'][0];
                        $physical_delivery_office_name=mysql_real_escape_string($i['physicaldeliveryofficename'][0]);
                        if (!empty($physical_delivery_office_name))
                        {
                           $location_id=v_sql_1_select("select id from location where name like '$physical_delivery_office_name';");
                           if (!$location_id)
                           {
                              $query="insert into location set name='$physical_delivery_office_name';";
                              $res=mysql_query($query);
                              if ($res)
                              {
                                 $location_id=v_sql_1_select("select id from location where name like '$physical_delivery_office_name';");
                              }
                              else
                                 $location_id=1;
                           }
                        }
                        else $location_id=1;
			$telephone_number = $i['telephonenumber'][0];
			$mobile = $i['mobile'][0];
			debug1("Exchange attributes: $department|$rfc822mailbox|$physical_delivery_office_name|$telephone_number|$mobile");
		}
		
		
		if( $account ) {	# make sure each entry processed has an account name
			#$logger->logit($account);
			$logger->logit(mysql_real_escape_string($account));

			$check_query = sprintf("SELECT username from users where username = '%s'",
				mysql_real_escape_string($account)
			);
			debug1($check_query);
			$entry_exists = mysql_query($check_query) or logit('Check query failed, ' . mysql_error());
			
			if( mysql_num_rows($entry_exists) > 0 ) {	# does entry alread exist? then update ....
				debug1("$account exists already, will update it");
				if( $exchange ) {
					$update_query = sprintf("UPDATE users SET Surname = '%s', GivenName = '%s', department = '%s', rfc822mailbox = '%s', physicalDeliveryOfficeName = '%s', telephoneNumber = '%s', mobile = '$mobile', LastSeenDirectory = NOW(),location='%s' WHERE username = '%s'",
						mysql_real_escape_string($surname),
						mysql_real_escape_string($given_name),
						mysql_real_escape_string($department),
						mysql_real_escape_string($rfc822mailbox),
						$physical_delivery_office_name,
						mysql_real_escape_string($telephone_number),
                                                $location_id,
						mysql_real_escape_string($account)
					);
				}
				else { 
					$update_query = sprintf("UPDATE users SET Surname = '%s', GivenName = '%s', LastSeenDirectory = NOW() WHERE username = '%s'",
						mysql_real_escape_string($surname),
						mysql_real_escape_string($given_name),
						mysql_real_escape_string($account)
					);
				}
				debug1($update_query);
				mysql_query($update_query) or logit('Update query failed, ' . mysql_error());
				$updates++;
			}
			else {	# ... otherwise insert it
				debug1("$account dosen't exist yet, will insert it");
				if( $exchange ) {
					$insert_query = sprintf("INSERT INTO users (username, Surname, GivenName, department, rfc822mailbox, physicalDeliveryOfficeName, telephoneNumber, mobile, LastSeenDirectory,location) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW(),'%s')",
						mysql_real_escape_string($account),
						mysql_real_escape_string($surname),
						mysql_real_escape_string($given_name),
						mysql_real_escape_string($department),
						mysql_real_escape_string($rfc822mailbox),
						$physical_delivery_office_name,
						mysql_real_escape_string($telephone_number),
						mysql_real_escape_string($mobile),
						$location_id
					);
				}
				else {
					$insert_query = sprintf("INSERT INTO users (username, Surname, GivenName, LastSeenDirectory) VALUES('%s', '%s', '%s', NOW())",
						mysql_real_escape_string($account),
						mysql_real_escape_string($surname),
						mysql_real_escape_string($given_name)
					);
					
				}
				debug1($insert_query);
				mysql_query($insert_query) or logit('Insert query failed, ' . mysql_error());
				$inserts++;
			}
		}
		
	}
	log2db('info', "Active Directory synchronisation completed. $inserts new users inserted and $updates users updated.");
}


# Print usage information to stdout and exit
function usage() {
	print("\nWrong command line paramters\n\n");
	print("Usage:\n\n");
	print("ad_user_sync [test] [exchange]\n");
	print("\ttest: Test AD server connection and dump results. Do not connect to DB\n");
	print("\texchange: Query additonal attributes from MS Exchange schema extension\n");
	exit();
}
?>
