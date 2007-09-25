<?php

/**
 * Common.php
 *
 * This class should be used as a parent class, since it allows for accessing the config
 * singleton and logging facilities.
 *
 * @package                     FreeNAC
 * @author                      Sean Boran (FreeNAC Core Team)
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright           	2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
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
