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
		
		#TODO: HUB_DETECTION, VLAN_BY_SWITCH_LOCATION

		#Check for VMs
                if ($this->system->isVM() && !$this->system->isKilled()) ALLOW(); #Retrieve the vlan from the host device

		#Port has a default vlan
		if ($this->port->hasDefaultVlan()) ALLOW($this->port->getPortDefaultVlan());

		#Handling of unknown systems
		if ($this->system->isUnknown()) UNKNOWN_SYSTEM();
		
		#Policy related stuff
		if ($this->system->isExpired() || $this->system->isKilled()) KILL();

		#Normal case
		if ($this->system->isActive()) 
		{	
			ALLOW($this->system->getvid());
		}
	
		#Default policy
		DENY();

	}
}
 
?>
