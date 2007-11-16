<?php
/**
 * Common.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright           	2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 */

/**
 * Define this common parent class to ensure consistent logging and access to configuration settings.
 * This class can be extended with 'common' code to simplify derived classes and ensure consistency.
*/
class Common {
   protected $conf;
   protected $logger;
   
   /**
   * Get the current instance of our Settings and Logger classes
   */
   public function __construct()
   {
      $this->conf=Settings::getInstance();
      $this->logger=Logger::getInstance();
   }	
}
