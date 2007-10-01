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
		$CONF=$GLOBALS['CONF'];
		$PORT=$GLOBALS['PORT'];
		$REQUEST=$GLOBALS['REQUEST'];
 		$HOST=$GLOBALS['HOST'];

		if ($HOST->isExpired() || $HOST->isKilled())
                {
		    	if ($CONF->vlan_for_killed)
   			{
				$this->logger->logit("Killed or expired system {$HOST->getMAC()}({$HOST->getHostName()}) on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()}. Assigning vlan ". vlanId2Name($CONF->vlan_for_killed));
				ALLOW($CONF->vlan_for_killed);
			}
			else
				DENY("Expired or killed system and no vlan_for_killed defined");
                }
		if ($HOST->isActive())
		{
			if ($vlan=$PORT->vlanBySwitchLocation())
			{
				$this->logger->logit("Exception. Assigning vlan by switch location");
				ALLOW($vlan);
			}
			else
				ALLOW($HOST->getVlanId());
		} 
		else if ($HOST->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   $this->logger->logit("Unmanaged device {$HOST->getMAC()}({$HOST->getHostName()}) on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()}",LOG_WARNING);
                } 
		#UNKNOWN AND UNMANAGED SYSTEMS
		#Check for VMs: special case, use vlan of VM host
	        if ($HOST->isVM()) 
                {
                   if ($vlan=$PORT->getVMVlan())
		   {
		      $this->logger->logit("Device {$HOST->getMAC()} on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()} is a VM. Assigning vlan of previous authenticated host");
                      ALLOW($vlan); #Retrieve the vlan from the host device
		   }
                }

                #Port has a default vlan
                if ($vlan=$PORT->getPortDefaultVlan()) 
		{
                   	$this->logger->logit("Device {$HOST->getMAC()} on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()} is unknown or unmanaged. Assigning port default vlan");
			ALLOW($vlan); #Retrieve the vlan from the host device
                } 
                else if ($CONF->default_vlan) 
                {
                   	$this->logger->logit("Device {$HOST->getMAC()} on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()} is unknown or unmanaged. Assigning global default vlan");
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
           $CONF=$GLOBALS['CONF'];
           $PORT=$GLOBALS['PORT'];
           $RESULT=$GLOBALS['RESULT'];
	   $SMS_HOST=new CallWrapper(new SMSEndDevice($RESULT));
 
           #Passing of information between objects
           $SMS_HOST->setPortID($PORT->getPortID());
           $SMS_HOST->setOfficeID($PORT->getOfficeID());
           $SMS_HOST->setVlanID($PORT->getLastVlanID());
           $SMS_HOST->setPatchInfo($PORT->getPatchInfo());
           $SMS_HOST->setSwitchInfo($PORT->getSwitchInfo());
           $SMS_HOST->setPortInfo($PORT->getPortInfo());
	   
	   $PORT->insertIfUnknown();
           $PORT->update();
	   
	   $SMS_HOST->insertIfUnknown();
	   $SMS_HOST->update();
	}
}
 
?>
