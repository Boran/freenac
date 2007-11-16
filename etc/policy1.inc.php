<?php

/* Sample Policy File
 *
 * Note: after you make changes here, verify that the php syntax will actually run, by
 *       calling './vmpsd_external'
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
	public function reportDecision($vlan=0)
        {
           if (is_numeric($vlan))
           {
              $PORT=$GLOBALS['PORT'];
              $HOST=$GLOBALS['HOST'];
              $this->logger->logit("Note: Device {$HOST->getmac()}({$HOST->gethostname()},{$HOST->getusername()}) on switch {$PORT->getswitch_ip()}({$PORT->getswitch_name()}), port {$PORT->getport_name()}, office {$PORT->getoffice()}@{$PORT->getbuilding()} has been placed in vlan ".vlanId2Name($vlan));
           }
        }
        
	public function preconnect() { 
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
		else if ($HOST->isActive())
		{
			$vlan=$HOST->getVlanId();   // 1. Get the Vlan normally attributed

                	// 2. Check for VMs: special case, use vlan of VM host
#	                if ($HOST->isVM())
#	                {
#       		            if ($vlan=$PORT->getVMVlan())
#       		            {
#      		                $this->logger->logit("Device {$HOST->getMAC()} on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()} is a VM. Assigning vlan of previous authenticated host");
#                 		}
#                	}


			// 3. Is the Vlan dependant on location?
			if ($vlanlc=$PORT->vlanBySwitchLocation())
			{
				$this->logger->logit("Exception. Assigning vlan by switch location");
				ALLOW($vlanlc);
			}
			else
				ALLOW($vlan);     // else use the vlan from step 1. or 2.
		} 
		else if ($HOST->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   $this->logger->logit("Unmanaged device {$HOST->getMAC()}({$HOST->getHostName()}) on port {$PORT->getPortInfo()}, switch {$PORT->getSwitchInfo()}",LOG_WARNING);
                } 


		### Unknown and unmanaged systems only get to here

                #Check for VMs: special case, use vlan of VM host
		## TO DO: add VM vlan checking

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
		$this->reportDecision();
		DENY('Default policy reached. Unknown or unmanaged device and no default_vlan specified');
	}

	function catch_ALLOW($vlan) 
	{
		$this->reportDecision($vlan);
		return $vlan;
	}

	public function postconnect()
        {
           $CONF=$GLOBALS['CONF'];
           $PORT=$GLOBALS['PORT'];
           $RESULT=$GLOBALS['RESULT'];
 	   #$HOST=$GLOBALS['HOST'];
           $SMS_HOST=new CallWrapper(new SMSEndDevice($RESULT));
 
	   $PORT->insertIfUnknown();
           $PORT->update();
	   
           #Passing of information between objects
           $SMS_HOST->setPortID($PORT->getPortID());
           $SMS_HOST->setOfficeID($PORT->getOfficeID());
           $SMS_HOST->setVlanID($PORT->getLastVlanID());
           /*$HOST->setPatchInfo($PORT->getPatchInfo());
           $HOST->setSwitchInfo($PORT->getSwitchInfo());
           $HOST->setPortInfo($PORT->getPortInfo());*/
           $SMS_HOST->setPortInfoForAlertSubject($PORT->getPortInfoForAlertSubject());
           $SMS_HOST->setPortInfoForAlertMessage($PORT->getPortInfoForAlertMessage());
           $SMS_HOST->setNotifyInfo($PORT->getNotifyInfo());
	   
           $SMS_HOST->insertIfUnknown();
	   $SMS_HOST->update();
	}
}
 
?>
