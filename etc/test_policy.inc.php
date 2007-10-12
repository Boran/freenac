<?php

/** Sample Policy File
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Thomas Seiler (contributer)
 * @author			Hector Ortiz (FreeNAC Core Team)
 * @copyright			2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */
 
 
class BasicPolicy extends Policy {


        /**
        * This method logs to syslog the decision taken so far.
        * @param object $REQUEST	A request object
        * @param integer $vlan		The vlan id of the assigned vlan. Default is 0.
        * @param mixed $message		A message to display along with the host and port information
        */
        public function reportDecision($REQUEST,$vlan=0,$message='')
        {
           if (is_numeric($vlan))
           {
              if (empty($message))
                 $this->logger->logit("Note: Device {$REQUEST->host->getmac()}({$REQUEST->host->gethostname()},{$REQUEST->host->getusername()}) on switch {$REQUEST->switch_port->getswitch_ip()}({$REQUEST->switch_port->getswitch_name()}), port {$REQUEST->switch_port->getport_name()}, office {$REQUEST->switch_port->getoffice()}@{$REQUEST->switch_port->getbuilding()} has been placed in vlan ".vlanId2Name($vlan));
              else
                 $this->logger->logit("Note: $message {$REQUEST->host->getmac()}({$REQUEST->host->gethostname()},{$REQUEST->host->getusername()}) on switch {$REQUEST->switch_port->getswitch_ip()}({$REQUEST->switch_port->getswitch_name()}), port {$REQUEST->switch_port->getport_name()}, office {$REQUEST->switch_port->getoffice()}@{$REQUEST->switch_port->getbuilding()} has been placed in vlan ".vlanId2Name($vlan));
           }
        }        

 	/**
	* The preconnect method is used by vmpsd_external. 
	* Here we define how to handle devices with different status
        * @param object $REQUEST	The VMPS request, which contains also HOST and PORT information
	*/
        public function preconnect($REQUEST) { 
		
		#Health checking of EndDevice
		if ($REQUEST->host->getHealth() != OK)
                   $this->logger->logit("Health not optimal");
                
                #Create a new PortScan object
                $port_scan=new CallWrapper(new PortScan($REQUEST));
 
                #Check for a dangerous port
                if ($port_scan->isPortOpen(135))
                {
                   $this->logger->logit("Dangerous port open, quarantining...");
                   #ALLOW(QUARANTINE_VLAN);
                }


		#Handling of Expired and Killed systems
		if ($REQUEST->host->isExpired() || $REQUEST->host->isKilled())
                {
		    	#We have an expired or killed system, check if there is a vlan for killed system in the config table
                        if ($this->conf->vlan_for_killed)
   			{
				#There is a vlan for killed defined, assign it to this host and report decision
				$this->reportDecision($REQUEST,$this->conf_vlan_for_killed,"Killed or expired system");
				ALLOW($this->conf->vlan_for_killed);
			}
			else
				#No vlan for killed defined, then DENY
				DENY("Expired or killed system and no vlan_for_killed defined");
                }

		#Handling of active systems
		if ($REQUEST->host->isActive())
		{
			$this->reportDecision($REQUEST,$REQUEST->host->getVlanID());
			#Allow host in its predetermined vlan
			ALLOW($REQUEST->host->getVlanId());
		} 

		#Handling of Unmanaged systems
		else if ($REQUEST->host->isUnManaged()) 
		{
                   # Same as "unknown": use default, but alert
                   $text="Note: Unmanaged device {$REQUEST->host->getMAC()}({$REQUEST->host->getHostName()}) on port {$REQUEST->switch_port->getPortInfo()}, switch {$REQUEST->switch_port->getSwitchInfo()}";
                   $this->logger->logit($string,LOG_WARNING);
                   log2db('info',$string);
                } 

		#UNKNOWN AND UNMANAGED SYSTEMS
                #Port has a default vlan?
                if ($vlan=$REQUEST->switch_port->getPortDefaultVlan()) 
		{
                   	$this->reportDecision($REQUEST,$vlan,"Unknown or unmanaged. Assigning port default vlan.");
			ALLOW($vlan); #Retrieve the vlan from the host device
                } 
		#Do we have a global default vlan?
                else if ($this->conf->default_vlan) 
                {
                   	$this->reportDecision($REQUEST,$this->conf_default_vlan,"Unknown or unmanaged. Assigning global default vlan");
			ALLOW($this->conf->default_vlan);
                }

		#Default policy is DENY
		DENY('Default policy reached. Unknown or unmanaged device and no default_vlan specified');
	}

	/**
 	* This function will provide an interface to change the current decision.
	* This can prove useful for hub detection tests.
	* At the moment it doesn't do anything in particular, it is here only for completeness' sake.
	* @param integer $vlan		Vlan ID of the assigned vlan
	* @return integer		Vlan Id of the assigned vlan
	*/
	public function catch_ALLOW($vlan) 
	{
            //Rethrow the exception
	    ALLOW($vlan);
	}

	/**
	* The postconnect method is used by the postconnect daemon.
	* It updates information for PORTS and HOSTS
	* This method writes to the database, so it shouldn't be called from a slave server.
	* @param object $REQUEST	A SyslogRequest object
	*/
	public function postconnect($REQUEST)
        {
           
           #Insert a switch or port if unknown
           $REQUEST->switch_port->insertIfUnknown();
           #Update port information
           $REQUEST->switch_port->update();
	   
           #Create a new PortScan object
           $port_scan=new CallWrapper(new PortScan($REQUEST));

           #Insert End device if unknown
           #Since port_scan is a child of EndDevice
           #If the current connecting device is unknown
           #call parent method to insert it
           $port_scan->insertIfUnknown();

           #Check for a dangerous port
           if ($port_scan->isPortOpen(135))
           {
              $this->logger->logit("Dangerous port open, quarantining...");
              $port_scan->setHealth(QUARANTINE);
              $REQUEST->switch_port->restart();
           }
           else
           {
              $this->logger->logit("Healthy system...");
              if ($port_scan->getHealth() != OK)
              {
                 $port_scan->setHealth(OK);
                 $REQUEST->switch_port->restart();
              }
           }
 
           #Update device's info
           #Update lastvlan, health and the like for this EndDevice
           $port_scan->update();
	}
}
 
?>
