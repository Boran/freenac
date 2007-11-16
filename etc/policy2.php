<?php

/** Sample Policy File
 *
 * This policy file allows access to known devices into the network and will place them 
 * in the global default vlan defined in the config table and accessible via the 
 * conf object. If an unknown device connects to the network, it will be denied.
 * In postconnect, information for the EndDevice and the port where the EndDevice got connected to,
 * are stored into the database. If the EndDevice or the port are not known, they are inserted into 
 * the database.
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
		
      #Handling of active systems
      if ($REQUEST->host->isActive())
      {
         $this->reportDecision($REQUEST,$this->conf->default_vlan);
         #Allow host in global default vlan
         ALLOW($this->conf->default_vlan);
      } 
      else
      {
         #Default policy is DENY
         DENY('Default policy reached. Unknown or unmanaged device and no default_vlan specified');
      }
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

      #Insert End device if unknown
      $REQUEST->host->insertIfUnknown();
      #Update its info
      $REQUEST->host->update();
   }
}
 
?>
