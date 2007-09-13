<?php

/*
 * Common Class
 *
 * This class should be used as a parent class, since it allows for access to the config
 * singleton and should alse provide for logging facilities.
 *
 *
 *
 *
 * @package                     FreeNAC
 * @author                      Sean Boran (FreeNAC Core Team)
 * @author                      Seiler Thomas (contributer)
 * @copyright           2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 */

class Common {
   protected $conf;
   protected $logger;
   
   public function __construct()
   {
      $this->conf=Settings::getInstance();
      $this->logger=Logger::getInstance();
   }	
}
