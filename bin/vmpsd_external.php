#!/usr/bin/php -f
<?php

/**
 * /opt/nac/bin/vmpsd_external
 *
 * Long description for file:
 * FUNCTION:
 *   "external" program called by the vmps daemon "vmpsd". This program
 *   decides what to do, in real time, when access is requested by  a
 *   Switch for a MAC address. Since its is real time perfomance is important,
 *   so some jobs such as document what was last seen, where, or recognising 
 *   Infnet PCs, is done in the vmps_lastseen script, which is not real time.
 *     o If the MAC is active in the DB authorise it.
 *     o If the mac is active on a port where another system has been
 *       active withein the last hour, try to use the vlan last seen on the 
 *       port, nut the vlan assigned to this system. This is to detect hubs and
 *       prevent .flapping.. 
 *       This feature is only allowed if the vlan on the port and assigned to 
 *       the MAC 
 *       are in the same vlan group (otherwise the new MAC is denied)
 *     o If the MAC is unknown, check to see if a default vlan has been 
 *       configured for 
 *       that port and use it, otherwise use the default vlan.
 *     o Log decisions to syslog, and key events to DB (visible in the GUI).
 * 
 *   program input:
 *         <domain> <switch ip> <port> <lastvlan> <mac address>
 *   program output
 *         ALLOW <vlan name>
 *         DENY
 *         SHUTDOWN
 *         DOMAIN
 * 
 *   Important: this script writes to stdout and is captured by vmpsd.
 *              So send debugging output to syslog, not stdout. Or just start
 *              directly from the commandline to check for classical
 *              PHP syntax problems.
 *              Do not log to the DB either (with log2db()), because this
 *              program can also run on a secondary and should NOT write to any
 *              other tables than vmpsauth, which is not replicated.
 *
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package		FreeNAC
 * @author		Sean Boran (FreeNAC Core Team)
 * @copyright	2006 FreeNAC
 * @license		http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version		SVN: $Id$
 * @link		http://www.freenac.net
 *
 */
/* Open Syslog channel for logging */
define_syslog_variables();
openlog("vmpsd_external", LOG_PID , LOG_LOCAL5);

/* include files */
require_once("../lib/exceptions.php");
require_once("../lib/funcs.inc.php");

/* Load the policy file */
require_once "../etc/policy.inc.php";

/* Open stdin and stdout - These connect us to vmpsd */
$in = fopen("php://stdin", "r");
$out = fopen("php://stdout", "w");

/* Loop Forever (we are a daemon) */
$request=VMPSRequest::getInstance();
while ($in && $out) {

	/* Read one line from vmpsd and parse it */
	$line=rtrim( fgets($in, 1024) );

	/* If there are some characters */
	if (strlen($line) > 0) {
		/* Log Request Start and Input */
		trace("----------------------------");
		trace("$line");

		/* split by space */      	
		$splitted = explode(" ", $line);

		/* sanity checks, 5 values */
		if (count($splitted) != 5 || ((strlen($splitted[4]) < 12) || (strlen($splitted[4]) > 17))) {
			// Todo: Complain in the log file
			continue;
		}

		/* extract values */
		list($domain, $switch, $port, $lastvlan, $mac)=$splitted;
		$request->setValues($mac,$switch,$port,$domain,$lastvlan);
		try {

			// Todo, setup policy object 


			/* create System Object */
			$system = new CallWrapper(new EndDevice($request));
			$port = new CallWrapper(new Port($request));
			/* Call Default policy */
			if ($conf->default_policy)
			{
				#$policy=new $conf->default_policy();
				try
				{
				   $policy=new $conf->default_policy();
				   eval($policy->preconnect($system,$port));
				}
				catch(Exception $e)
				{
					if (method_exists($policy,catch_ALLOW))
					{
						ALLOW($policy->catch_ALLOW($e->getDecidedVlan()));
					}
					else 
					{
						ALLOW($e->getDecidedVlan());
					}
				}
                        }
  
 			/* In case there was an error, try fallback policy */
 			if ($conf->fallback_policy) {
				$policy=new $conf->fallback_policy($system,$port);
 	 			trace("Error at default policy, falling back to policy ".
 	 			     $conf->fallback_policy);
				#try {
	 				$policy->preconnect();
				#} catch(Exception $e) {
				#	if(function_exists($policy->catchALLOW)) {
				#	throw new AllowExcetption($policy->catchALLOW($e->getDecidedVlan()));
				#}
	 		}
 	    
 	  		/* This is the default action */
 	  		DENY();
 	    }
 	    catch (DenyException $e) {
 	  		fputs($out, "DENY\n");
 	  		reportException($e);
 	    }
 	    catch (KillException $e) {
 	    	if ($conf->vlan_for_killed) {
 	    		fputs($out, "ALLOW ".vlanId2name($conf->vlan_for_killed)."\n");
 	    	} else {
	 	  		fputs($out, "DENY\n");
	 	  	}
 	  		reportException($e);
 	  		// Todo: let freenac_lastseen know to kill this system via some IPC
 	    }
 	    catch (AllowException $e) {
 	  		fputs($out, "ALLOW ".vlanId2name($e->getDecidedVlan())."\n");
 	  		reportException($e);
 	    }
 	    catch (UnknownSystemException $e) {
		if ($conf->default_vlan)
		   fputs($out,"ALLOW ".vlanId2name($conf->default_vlan)."\n");
		else
		   fputs($out, "DENY\n");
		reportException($e);		
	    }
 	    catch (Exception $e) {
 		    fputs($out, "DENY\n");
 	    	reportException($e);
 	    }
 
		trace("----------------------------");
 
      	//ob_flush();               # log buffered outputs
      	flush();
    }                // strlen >0
  	#sleep(1);                 # wait 1 secs, before retrying
   //ob_flush();               # log buffered outputs
}


exit(0);
// End of Main -----------------------

function reportException(Exception $e) {
 	$t = $e->GetTrace();
 	trace($e->getMessage() ." (at ".basename($t[0]['file']).
 										":". $t[0]['line'].")");
}

function trace($message) {
	syslog(LOG_CRIT, $message);
}


/*function vlanId2Name($vlanID) {
	  // Todo: Proper Error Handling, and use better Database abstraction
      return v_sql_1_select("select default_name from vlan where id='$vlanID' limit 1");
}*/

function __autoload($classname)
{
   require_once "../lib/$classname.php";
}


/* 



define_syslog_variables();
openlog("vmpsd_external", LOG_PID , LOG_LOCAL5);



$request=VMPSRequest::getInstance();
$request->setValues('MAC','SWITCH','PORT','VTP','LASTVLAN');

$syslog=SysLogger::getInstance();
$syslog->setLevel(2);
$syslog->debug(1,"Testing debug level 1");
$syslog->debug(2,"Testing debug level 2");
$syslog->debug(3,"Testing debug level 3");

$db_log=new DBLogger();
$db_log->log("Testing 'DBLOGGER'");

handle_request();	//Defined in policy.inc.php

*/

?>
