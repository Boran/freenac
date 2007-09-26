<?php
/** 
 * Policy.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation. 
 * 
 * @package			FreeNAC
 * @author			Thomas Seiler (contributer)
 * @copyright			2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */

/**
 * Base Policy class
 * This is the basic policy implementation
 * The real policy implementation can override what happens by simply
 * providing an implementation of the pre and post connect methods.
 * This class extends the {@link Common} class.
*/
class Policy extends Common {
   /**
   * Default policy is to deny everything 
   * @throws 	Deny
   */
   public function preconnect() {
      DENY();
   }
	
   /**
   * Default policy is to deny everything 
   * @throws	Deny
   */
   public function postconnect() {
      DENY();
   }
}

?>
