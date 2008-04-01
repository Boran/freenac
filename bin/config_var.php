#!/usr/bin/php
<?php
/**
 * bin/config_var.php
 *
 * Long description for file:
 *
 * This script retrieves the value associated to a config variable from the config table.
 *
 * USAGE :
 *      config_var.php variable
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * package                     FreeNAC
 * author                      Hector Ortiz (FreeNAC Core Team)
 * copyright                   2008 FreeNAC
 * license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * version                     SVN: $Id$
 * link                        http://www.freenac.net
 *
 */

chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "./funcs.inc.php";               # Load settings & common functions
$logger->setDebugLevel(0);
$logger->setLogToStdOut(true);

function print_usage($code)
{
   global $logger;
   $usage=<<<EOF
USAGE: config_var.php variable [OPTIONS]

        Web:      http://www.freenac.net/
        Email:    opennac-devellists.sourceforge.net

DESCRIPTION: Retrieve the value associated to the config variable specified in the command line from the 
             FreeNAC database. 
             [NOTE] This script should be used by all non PHP scripts that are part from FreeNAC, since those
                    scripts don't have a way to query the database directly.

OPTIONS:
        -h              Display this help screen
EOF;
   $logger->logit($usage);
   exit($code);
}

db_connect();

if ($argc>1)
   $options=getopt('h');

if ($options)                                           //Simple command line parsing
{
   if (array_key_exists('h',$options))
      print_usage(0);
}

if ($argc>2)
   print_usage(1);

$variable = trim($argv[1]);

if ($conf->$variable)
{
   $value = $conf->$variable;
   echo $value;
   exit(0);
}
else
   exit(1);
?>
