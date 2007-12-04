<?php

/** Sample Policy File
 *
 * This policy file checks first the health status of EndDevices connecting to the network.
 * If the health of the connecting device was set to QUARANTINE, it will be placed in the 
 * quarantine vlan.
 * For other systems whose health status is not OK, only send a message to syslog saying so.
 * Let's say that for example, there is a worm spreading through port 135, we then check for
 * systems with port 135 open. If such a port is open on the EndDevice, we'll place it in the
 * quarantine vlan where it can fix its problems.
 * If this system doesn't have a dangerous port open, then allow access to known devices into 
 * the network and place them in the vlan assigned to ead device.
 * If the switch port where the device is connecting to has a vlan assigned to it, the 
 * EndDevice will be placed in that vlan. 
 * If an unknown device connects to the network, it will be placed in the global default vlan if defined.
 * If neither a port vlan or a global default vlan have been defined, the connecting device will be denied.
 * In postconnect, information for the EndDevice and the port where the EndDevice got connected to,
 * are stored into the database. If the EndDevice or the port are not known, they are inserted into 
 * the database.
 * Also in postconnect, if the dangerous port is open, we set the health status for the connecting device
 * to quarantine. If at some point the system fixes its problem and requests for access, we set back its
 * health status to OK and restart the port in order to be placed in its regular vlan.
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

class BasicPolicy extends Policy 
{
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
   public function preconnect($REQUEST) 
   { 
      #Systems with quarantine status are to be placed in the quarantine vlan
      if ($REQUEST->host->getHealth() == QUARANTINE)
      {
         $this->logger->logit("{$REQUEST->host->getmac()} is in the quarantine state");
         #Allow this system onto the quarantine vlan
         ALLOW($this->conf->quarantine_vlan);
      }

      #Health checking of EndDevice
      if ($REQUEST->host->getHealth() != OK)
         $this->logger->logit("Health not optimal for system {$REQUEST->host->getmac()}");

      #Create a new PortScan object, which contains information about open ports on the End Devices
      $port_scan=new CallWrapper(new PortScan($REQUEST));

      #Check for a dangerous port, in this case, port 135
      if ($port_scan->isPortOpen(135))
      {
         $this->logger->logit("Dangerous port open, quarantining...");
         #Allow this system onto the quarantine vlan
         #Postconnect will take care of changing its health status to quarantine
         ALLOW($this->conf->quarantine_vlan);
      }
		
      #Handling of active systems
      if ($REQUEST->host->isActive())
      {
         $this->reportDecision($REQUEST,$REQUEST->host->getVlanId());
         #Allow host in its predetermined vlan
         ALLOW($REQUEST->host->getVlanId());
      }

      #Unknown systems 
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

      #Check for a dangerous port
      if ($port_scan->isPortOpen(135))
      {
         $this->logger->logit("Dangerous port open, quarantining...");
         #We set this health's device as not healthy, in this case, quarantine
         $port_scan->setHealth(QUARANTINE);
      }
      else
      {
         #The port is not open on this device
         $this->logger->logit("Healthy system...");
         #If health is different than OK
         if ($port_scan->getHealth() != OK)
         {
            #Then update its new health status
            $port_scan->setHealth(OK);
            #And restart the port in order to allow it in its regular vlan
            $REQUEST->switch_port->restart();
         }
      }


      #Insert End device if unknown
      $REQUEST->host->insertIfUnknown();
      #Update its info
      $REQUEST->host->update();
   }
}
 
?>
