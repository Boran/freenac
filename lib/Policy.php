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
 
class Policy extends Common {
	#protected $system;
	#protected $port;
	
	// Todo __construct($system)
	#public function __construct() {
	   #$this->system=$system;
	   #$this->port=$port;
	#   parent::__construct();
	#}

	// Todo change_system()
	
	/* default policy is to deny everything */
	public function preconnect() {
		DENY();
	}
	
	/* default policy is to deny everything */
	public function postconnect() {
		DENY();
	}
}

?>
