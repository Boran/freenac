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

	public function __construct() {
           parent::__construct();
	}

/*        public function HealthOK() {
		   if ($this->system->HealthOK())   # Health=OK, was checked in the last 24 hours
		     return true;
                     if($this->system->patchOK() && $this->system->AntiVirOK() {
		       $this->system->setHealth(OK);
		     return true;
		else {
		       $this->system->setHealth(QUARANTINE);
			return false;
		}
        }*/

        public function preconnect($system,$port) {
		
/*		#TODO: HUB_DETECTION, VLAN_BY_SWITCH_LOCATION
		#Policy related stuff
		#if ($this->system->isExpired() || $this->system->isKilled()) KILL();

		#Normal case
		if ($this->system->isExpired() || $this->system->isKilled()) {
		     #ALLOW($this->conf->vlan_for_killed(), NOCHANGE);
		     DENY($this->conf->vlan_for_killed());

		} else if ($this->system->isActive()) {	

		   if (HealthOK()   # AV/patch was checked in the last 24 hours
		     ALLOW($this->system->getVlanID(), HEALTHY);
                     
		   else {
		       ALLOW($conf->vlan_quartine, QUARTINE);
		     }

                } else if ($this->system->isQuartined()) {	
		   if (HealthOK()) {
		     # no longer in quartine
		     ALLOW($this->system->getVlanID());
		   } else {
		     ALLOW($conf->vlan_quartine);
		   }

                } else if ($this->system->isUnManaged()) {	
		   # Same as "unknown": use default, but alert
                   log("Unmanaged device on VMPSD port $switch $port", "WARN");

		} else {   # unknown
	  	   ## log warning? 
                      
                }
                       
                ### Unknown and unmanaged systems only get to here

		#Check for VMs: special case, use vlan of VM host
                if ($vlan=$this->system->isVM()) ALLOW($vlan, NOCHANGE); #Retrieve the vlan from the host device

		#Port has a default vlan
                if ($vlan=$this->system->getPortDefaultVlan()) {
 		  ALLOW($vlan, NOCHANGE); #Retrieve the vlan from the host device
		  #if ($this->port->hasDefaultVlan()) ALLOW($this->port->getPortDefaultVlan());

                } else if ($conf->default_vlan) {
		   ALLOW($conf->default_vlan, NOCHANGE);
	        }

		#Handling of unknown systems
		#There can be the case where we have systems sitting in the db with unknown status, Handle those systems
		#if ($this->system->isUnknown() && $this->port->hasDefaultVlan()) ALLOW($this->port->getPortDefaultVlan());
		#if ($this->system->isUnknown()) UNKNOWN_SYSTEM();
		#if ($this->system->isUnknown()) ALLOW($conf->default_vlan);
		

		}*/
		if ($system->isExpired() || $system->isKilled())
			ALLOW($this->conf->vlan_for_killed);

		if ($system->isActive())
		{
			if ($vlan=$port->vlanBySwitchLocation())
				ALLOW($vlan);
			else
				ALLOW($system->getVlanId());
		} 
		else if ($system->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   #log("Unmanaged device on VMPSD port $switch $port", "WARN");

                } 
		else 
		{   
		   #UNKNOWN SYSTEMS
		   #Check for VMs: special case, use vlan of VM host
	           if ($vlan=$system->isVM()) ALLOW($vlan); #Retrieve the vlan from the host device

                   #Port has a default vlan
                   if ($vlan=$port->getPortDefaultVlan()) {
                      ALLOW($vlan); #Retrieve the vlan from the host device
                   } 
                   else if ($this->conf->default_vlan) 
                   {
                      ALLOW($this->conf->default_vlan);
                   }
		}
		#Default policy
		DENY();

	}

	function catch_ALLOW($vlan) {
		return $vlan;
	}
}
 
?>
