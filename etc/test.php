#!/usr/bin/php
<?php
/**
 * Script to test a policy with sample input.
 * This simulates a vmpsd_external or postconnect.
 * It needs to be adapted for each test scenario.
 */


# The test policy: adapt as needed
$policy_file='../etc/policy_sean1.php';

chdir(dirname(__FILE__));
set_include_path("./:../");
require_once("../lib/exceptions.php");
require_once("../bin/funcs.inc.php");
require_once "$policy_file";

## adapt these, and the VMPSRequest line below too
$logger=Logger::getInstance();
$logger->setDebugLevel(2);
$logger->setLogToStdErr(true);


## Create the policy, request object and process with preconnect()
$policy=new BasicPolicy;
$request=new VMPSRequest("0016.e3ea.4a43","192.168.245.90", "Fa0/16", "x","y"); // $mac,$switch,$port,$domain,$lastvlan
$policy->preconnect($request);

?>
