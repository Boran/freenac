<?php
/**
 * Logger.php
 *
 * Long description for file:
 *
 * This file defines the Logger interface:
 *
 * METHOD SUMMARY:
 * *    public abstract log($message);
 *              Define the log method to be implemented by child classes which will provide logging facilities
 *      PARAMETERS:
 *		$message: 	The message to log
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
*/

interface Logger
{
   function log($message='');	//To be implemented in child classes
}

?>
