#!/usr/bin/php
<?php
/**
 * enterprise/epo_sync
 *
 * Long description for file:
 * 
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Wolfram Strauss, Sean Boran (FreeNAC Core Team)
 * @copyright                   2006 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     CVS: $Id:$
 * @link                        http://www.freenac.net
 *
 */




/*************************************
		MAIN
**************************************/

$EPO_VERSION = 4;	// either 3 or 4


// Php weirdness: change to script dir, then look for includes
chdir(dirname(__FILE__));
set_include_path("../:./");


/**
* Load settings and common functions
*/
require_once "./funcs.inc.php";     

global $logger;
$logger->setLogToStdOut(false);
$output=true;
set_time_limit(0);
$logger->setDebugLevel(0);

#  epo version checking
if( $EPO_VERSION == 3 )
{
	$epo_db_prefix = '';
}
elseif( $EPO_VERSION == 4 )
{
	$epo_db_prefix = 'EPO';
}
else
{
	$logger->logit("Version $EPO_VERSION is not supported!", $LOG_ERROR);
	exit;
}

// this timestamp is used as the sync date for data synced from the wsus db to the freenac db
$timestamp=date('Y-m-d H:i:s');
$logger->logit("Starting EPO sync job");

db_connect();	// vmps db connection (mysql)
$enabled=v_sql_1_select("select value from config where name='epo_enabled'");

if( $enabled )
{
	dbepo_connect();	// epo db connection (mssql)

	// warning!! this should run within a transaction ...
	
	if( !empty_tables() )
	{
		$logger->logit("Failed to empty all epo tables, logical status may be inconsistend!", LOG_ERROR);
		cleanup();
	}
	
	if( !get_systems() )
	{
		$logger->logit("Failed to sync systems list", LOG_ERROR);
		cleanup;
	}
	
	if( !get_versions() )
	{
		$logger->logit("Failed to sync versions list", LOG_ERROR);
		cleanup;
	}
	
	// ... end of suggested transaction
	
	cleanup();
		
}
else
{
	$logger->logit("EPO support not enabled", LOG_WARNING);
}



/*******************************
	FUNCTIONS
*******************************/
	

/**
*  This function converts the datetime retrieved from MSSQL into MySQL datetime format
*/
function convert_date($date)
{
   $date_array=getdate(strtotime($date));
   $date=$date_array['year'].'-';
   $date_array['mon'] < 10 ? $date.='0'.$date_array['mon'].'-' : $date.=$date_array['mon'].'-';
   $date_array['mday'] < 10 ? $date.='0'.$date_array['mday'].' ' : $date.=$date_array['mday'].' ';
   $date_array['hours'] < 10 ? $date.='0'.$date_array['hours'].':' : $date.=$date_array['hours'].':';
   $date_array['minutes'] < 10 ? $date.='0'.$date_array['minutes'].':' : $date.=$date_array['minutes'].':';
   $date_array['seconds'] < 10 ? $date.='0'.$date_array['seconds'] : $date.=$date_array['seconds'];
   return $date;
}


/**
* Ensures that $string is mysql safe
*/
function validate($string)
{
   rtrim($string,' ');
   if (get_magic_quotes_gpc()) {
      $value=stripslashes($string);
   }
   if (!is_numeric($string)) {
      $string= mysql_real_escape_string($string);
   }
   return $string;
}


/**
* Connect to the EPO server
*/
function dbepo_connect()
{
   global $conf,$epo_dbuser,$epo_dbpass, $logger;
   $logger->debug("Connecting to ".$conf->epo_dbalias." ".$conf->epo_db, 2);
   $msconnect = mssql_connect($conf->epo_dbalias, $epo_dbuser, $epo_dbpass);
   if ( !$msconnect )
   {
     $logger->logit("Cannot connect to EPO server ".$conf->epo_dbalias.":" . mssql_get_last_message(), LOG_ERROR);
     return false;
   }
   $db = mssql_select_db($conf->epo_db, $msconnect);
   if ( !$db )
   {
      $logger->logit("Couldn't open database ".$conf->epo_db." ".mssql_get_last_message(), LOG_ERROR);
      return false;
   }
}


/**
* Returns the hostname part of an fqdn thus everything before the first dot
*/
function get_hostname($fqdn)
{
	global $logger;
	
	$dot_pos = strpos($fqdn, '.');
	$hostname;
	if( $dot_pos )
	{
		$hostname = substr($fqdn, 0, $dot_pos);
	}
	else
	{
		$hostname = $fqdn;
	}
	
	$logger->debug("Converting $fqdn to $hostname", 2);
	
	return $hostname;
}

/**
* Look up a wsus hostname in the vmps table and return the vmps id if and only if there's exactly one entry
*/
function get_vmps_id($mac)
{
	global $logger;
	
	$query = "select id from systems where mac = '$mac';";
	$logger->debug("Executing $query", 3);
	$result = mysql_query($query);
	if( !$result )
	{
		$logger->logit("Could not obtain vmps id for mac $mac, " . mysql_error(), LOG_WARNING);
		return false;
	}
	$num_rows = mysql_num_rows($result);	//TODO: exception handling
	if( $num_rows == 0 )
	{
		$logger->logit("No vmps id for mac $mac found", LOG_WARNING);
		return false;
	}
	elseif( $num_rows == 1 )
	{
		$row = mysql_fetch_row($result);	//TODO: exception handling
		$logger->debug("System with mac $mac matches vmps id $row[0]", 2);
		return $row[0];	
	}
	else
	{
		$logger->logit("mac $mac is not unique in vmps", LOG_WARNING);
		return false;
	}
}


/**
* Convert mac address obtain from epo into vmps format
*/
function convert_mac($epo_mac)
{
	global $logger;
	
	$vmps_mac = preg_replace('/(\w{4})(\w{4})(\w{4})/', '$1.$2.$3', $epo_mac);
	
	$logger->debug("Mac format conversion: $epo_mac -> $vmps_mac", 2);
	
	return $vmps_mac;
}

/**
* Empty all epo tables to get ready for fresh sync
*/
function empty_tables()
{
	global $logger;

	$logger->debug("Emptying tables", 1);
	if( !mysql_query('truncate table epo_systems;') ) {
		$logger->logit("Could not empty epo_systems, " . mysql_error(), LOG_ERROR);
		return false;
	}
	if( !mysql_query('truncate table epo_versions;') ) {
		$logger->logit("Could not empty epo_versions, " . mysql_error(), LOG_ERROR);
		return false;
	}
	
		
	return true;
}


/**
* Obtain list of systems managed by the epo server
*/
function get_systems()
{
	global $logger, $timestamp, $epo_db_prefix;

	$query = "select l.nodename, l.lastupdate, l.agentversion, c.ostype, c.netaddress, c.ipaddress, c.freediskspace, p.enginever, p.datver, p. productversion, p.hotfix, c.domainname, c.ostype, c.osversion, c.osservicepackver, c.osbuildnum, c.username from dbo.${epo_db_prefix}leafnode l left join dbo.${epo_db_prefix}computerproperties c on l.autoid = c.parentid left join dbo.${epo_db_prefix}productproperties p on l.autoid = p.parentid where p.productcode = 'VIRUSCAN8600'";
	
	$logger->debug("Executing $query", 3);
	$result = mssql_query($query);
	if( !$result )
	{
		$logger->logit("Failed to obtain systems from epo, " . mssql_get_last_message(), LOG_ERROR);
		return false;
	}
	
	while( $sys_row = mssql_fetch_assoc($result) )
	{
		// TODO: exception handling
		$hostname = get_hostname($sys_row['nodename']);
		$mac = convert_mac($sys_row['netaddress']);
		$id = get_vmps_id($mac);
		if( !$id )
		{
			continue;
		}
		
		// insert system into epo_systems
		$query = sprintf("insert into epo_systems values('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');", $id, $hostname, validate($sys_row['domainname']), validate($sys_row['ipaddress']), $mac, validate($sys_row['agentversion']), convert_date($sys_row['lastupdate']), validate($sys_row['productversion']), validate($sys_row['enginever']), validate($sys_row['datver']), validate($sys_row['hotfix']), validate($sys_row['ostype']), validate($sys_row['osversion']), validate($sys_row['osservicepackver']), validate($sys_row['osbuildnum']), validate($sys_row['freediskspace']), validate($sys_row['username']), $timestamp);
		
		$logger->debug("Executing: $query", 3);
		if( !mysql_query($query) )
		{
			$logger->logit("Could not insert system $hostname, " . mysql_error(), LOG_WARNING);
			continue;
		}
		
	}
	
	return true;
}


/**
* Obtain list of product actual product version checked into the epo server
*/
function get_versions()
{
	global $logger, $timestamp, $EPO_VERSION;
	
	if( $EPO_VERSION == 3 )
	{
		$query = "select type, version from dbo.latestupdates";
		
		$logger->debug("Executing: $query", 3);
		$result = mssql_query($query);
		
		if( !$result )
		{
			$logger->logit("Failed to obtain product versions from epo, " . mssql_get_last_message(), LOG_ERROR);
			return false;
		}
	
		while( $row = mssql_fetch_assoc($result) )
		{
			$query = sprintf("insert into epo_versions(product, version, hotfix, lastsync) values('%s', '%s', '%s', '%s');", validate($row['type']), validate($row['version']), '', $timestamp);
		
			$logger->debug("Executing: $query", 3);
			if( !mysql_query($query) )
			{
				$logger->logit("Could not insert product " . $row['type'] . ", " . mysql_error(), LOG_WARNING);
				continue;
			}
		}
	}
	elseif( $EPO_VERSION == 4 )
	{
		$query = "select productcode, productversion, hotfixversion from dbo.epomastercatalog";
		
		$logger->debug("Executing: $query", 3);
		$result = mssql_query($query);
		
		if( !$result )
		{
			$logger->logit("Failed to obtain product versions from epo, " . mssql_get_last_message(), LOG_ERROR);
			return false;
		}
	
		while( $row = mssql_fetch_assoc($result) )
		{
			$query = sprintf("insert into epo_versions(product, version, hotfix, lastsync) values('%s', '%s', '%s', '%s');", validate($row['productcode']), validate($row['productversion']), validate($row['hotfixversion']), $timestamp);
		
			$logger->debug("Executing: $query", 3);
			if( !mysql_query($query) )
			{
				$logger->logit("Could not insert product " . $row['productcode'] . ",  " . mysql_error(), LOG_WARNING);
				continue;
			}
		}
	}
	else
	{
		$logger->logit("Wrong epo version $EPO_VERSION. The variable must have changed during execution -> very bad!", LOG_ERROR);
		exit(1);
	}
	
	return true;
}


/**
*
*/
function cleanup($exit_code = 0)
{
	global $logger;

	// TODO: exception handling	
	
	mssql_close();
	mysql_close();
	
	$logger->logit("Done syncing EPO");
	
	exit($exit_code);
}
	
	
?>	
