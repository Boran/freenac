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
 
 
class BasicPolicy extends Policy {

	#public function __construct($HOST,$PORT) {
        #   parent::__construct($HOST,$PORT);
	#}

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

        public function preconnect() { 
		
/*		#TODO: HUB_DETECTION, VLAN_BY_SWITCH_LOCATION
		#Policy related stuff
		#if ($this->system->isExpired() || $this->system->isKilled()) KILL();

		#Normal case
		if ($this->system->isExpired() || $this->system->isKilled()) {
		     #ALLOW($CONF->vlan_for_killed(), NOCHANGE);
		     DENY($CONF->vlan_for_killed());

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
                   log("Unmanaged device on VMPSD port $switch $PORT", "WARN");

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
		$HOST=$GLOBALS['HOST'];
		$CONF=$GLOBALS['CONF'];
		$PORT=$GLOBALS['PORT'];
		$REQUEST=$GLOBALS['REQUEST'];
		if ($HOST->isExpired() || $HOST->isKilled())
			ALLOW($CONF->vlan_for_killed);
		if ($HOST->isActive())
		{
			if ($vlan=$PORT->vlanBySwitchLocation())
				ALLOW($vlan);
			else
				ALLOW($HOST->getVlanId());
		} 
		else if ($HOST->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   #log("Unmanaged device on VMPSD port $switch $PORT", "WARN");

                } 
		else 
		{   
		   #UNKNOWN SYSTEMS
		   #Check for VMs: special case, use vlan of VM host
	           if ($HOST->isVM()) 
                   {
                      if ($vlan=$PORT->getVMVlan())
                         ALLOW($vlan); #Retrieve the vlan from the host device
                   }

                   #Port has a default vlan
                   if ($vlan=$PORT->getPortDefaultVlan()) {
                      ALLOW($vlan); #Retrieve the vlan from the host device
                   } 
                   else if ($CONF->default_vlan) 
                   {
                      ALLOW($CONF->default_vlan);
                   }
		}
		#Default policy
		DENY();
	}

	function catch_ALLOW($vlan) 
	{
		return $vlan;
	}

	public function postconnect()
        {
	   $HOST=$GLOBALS['HOST'];
           $CONF=$GLOBALS['CONF'];
           $PORT=$GLOBALS['PORT'];
           $REQUEST=$GLOBALS['REQUEST'];
	   echo "Inside postconnect\n";
	   echo "PORT:\n";
           print_r($PORT->getAllProps());
           echo "HOST:\n";
           print_r($HOST->getAllProps());
	   if (!$HOST->inDB())
	      echo "HOST SHOULD BE INSERTED IN DB\n";
           else
	      echo "HOST IS ALREADY IN DB\n";
           if (!$PORT->isSwitchInDB())
              echo "SWITCH SHOULD BE INSERTED INTO DB\n";
           if (!$PORT->isPortInDB())
              echo "PORT SHOULD BE INSERTED INTO DB\n";
	}
}
 
?>
