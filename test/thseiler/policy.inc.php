<?php

/* Sample Policy File
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
 
 
class InoPolicy extends Policy {

	public function __construct($system,$port) {
           parent::__construct($system,$port);
	}

        public function preconnect() {
		
		if ($this->system->isExpired()) KILL();
		ALLOW($this->system->getvid());

	}
}
 
?>
