<?php
/**
 * funcs.inc
 *
 * Long description for file:
 * common PHP functions used by several freenac scripts
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

/* __autoload() Trick
 * This tries to autoload a class that has been devined by the user.
 * This way the user does not to add an explicit include statement to the
 * policy file...
 */

require_once 'etc/config.inc';

$conf=Settings::getInstance();

db_connect();



/* Connect to DB */
function db_connect()
{
  global $connect, $dbhost, $dbuser, $dbpass, $dbname;

  $connect=mysql_connect($dbhost, $dbuser, $dbpass)
     or die("Could not connect to mysql: " . mysql_error());
  mysql_select_db($dbname, $connect) or die("Could not select database")
     or die("Could not select DB: " . mysql_error());;
}



?>