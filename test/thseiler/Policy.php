<?php

/* Base Policy Class
 *
 * This is the basic policy implementation
 * The real policy implementation can override what happens by simply
 * 
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Thomas Seiler (contributer)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */
 
class Policy {
	protected $system;
	
	// Todo __construct($system)
	protected function __construct($system) {
           $this->system=$system;
	}

	// Todo change_system()
	
	/* default policy is to deny everything */
	protected function preconnect() {
		DENY();
	}
	
	/* default policy is to deny everything */
	protected function postconnect() {
		DENY();
	}
}

?>
