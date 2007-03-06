<?php
/**
 * /opt/nac/etc/config.inc.template
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
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright		2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			CVS: $Id:$
 * @link				http://www.freenac.net
 *
 */

## Enterprise config options
#require_once "/opt/nac/enterprise/config_en.inc";


## MySQL DB settings for all scripts
  $dbhost="localhost";
  $dbname="opennac";
  $dbuser="inventwrite";
  $dbpass="PASSWORD2";              # keep this secret!

## LDAP coniguration settings (Active Directory)
## ad_user_sync
	$ad_server = '192.168.203.187';                                                  # Domain controller
        $ad_user = 'cn=administrator,cn=users,dc=asdf,dc=asdf';    # AD user with read privileges on AD
        $ad_password = 'gggggg';                                                                                # AD users's password
        $ad_base_user_dn = array('cn=users,dc=asdf,dc=asdf');                             # Base DN under which user information is stored

### Write GUI
// MySQL DB settings for all scripts
   $writeuser="webwrite";                     # this user needs write access to the DB
   $writepass="PASSWORD1";                    # keep this secret!

// Variable setup
   $entityname='MyCompany';                # name of your company

// unknown machines in the database
   $unknown='%unknown%';

// Enable XLS export? (requires the PEAR module Spreadsheet_Excel_Writer)
   $xls_output=false; // To enable set it to: true

## Read GUI
## DB connection
  $readuser = 'inventwrite';    # 
  $readpass = 'PASSWORD2';

## vmpsdot.php
  $vmpsdot_querydays=60;   # Show devices seen in last XX days

## hubs.php
  $hubs_querydays=60;      # Show devices seen in last XX days

## statgraph.php
  $jpgraph_path = '/usr/local/share/jpgraph/src';            # path to jpgraph library


#  ## vmpsdot.php
    $dotcmd = 'neato';                                  # graphviz executable (cf `man dot` for choices)

?>
