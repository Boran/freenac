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

	#public function __construct($SMS_HOST,$PORT) {
        #   parent::__construct($SMS_HOST,$PORT);
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
		$CONF=$GLOBALS['CONF'];
		$PORT=$GLOBALS['PORT'];
		$REQUEST=$GLOBALS['REQUEST'];
                $SMS_HOST=new CallWrapper(new SMSEndDevice($REQUEST));   // initialise the SMS module
		if ($SMS_HOST->isExpired() || $SMS_HOST->isKilled())
			ALLOW($CONF->vlan_for_killed);
		if ($SMS_HOST->isActive())
		{
			if ($vlan=$PORT->vlanBySwitchLocation())
				ALLOW($vlan);
			else
				ALLOW($SMS_HOST->getVlanId());
		} 
		else if ($SMS_HOST->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   $this->logger->logit("Unmanaged device {$SMS_HOST->getMAC()}({$SMS_HOST->getHostName()}) on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()}",LOG_WARNING);
                } 
		#UNKNOWN AND UNMANAGED SYSTEMS
		#Check for VMs: special case, use vlan of VM host
	        if ($SMS_HOST->isVM()) 
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
		#Default policy
		DENY('Default policy reached. Unknown or unmanaged device and no default_vlan specified');
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
	   
	   $PORT->insertIfUnknown();
           $PORT->update();
	   
	   $HOST->insertIfUnknown();
	   $HOST->update();
	}
}
 
?>
