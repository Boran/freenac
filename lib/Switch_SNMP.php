<?php
/**
 * Switch_SNMP.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 */

/**
 * This class is intended to perform SNMP queries to all kind of switches.
 * This class extends the {@link Common} class.
 */
class Switch_SNMP extends Common
{
   private $props=array(); 
 
   /**
   * Get an OID which returns only a single value, in other words, use this method when you're expecting a single value, not an array
   * @param mixed $community		SNMP community to use
   * @param mixed $oid			OID to query for
   * @return mixed			The value of the OID
   */  
   private function getSingleOID($community,$oid)
   {
      ## Continue if there is a valid ip and host is not down
      if ($this->switch_ip && ! $this->host_down)
      {
         ## This test is to avoid performing SNMP queries on hosts which are not responsive
         if (( ! $this->description ) && (strcmp($oid,'1.3.6.1.2.1.1.1')==0))
         {
            ## Try to get the description. This OID should be available on all switches.
            ## Try 3 times before declaring switch dead
            for ($i = 0; $i < 3; $i++)
            {
               $temp=@snmprealwalk($this->switch_ip,$community,$oid);
               if ($temp)
                  $i=4;
            }
            if ( ! $temp )
            {
               ## We have tried 3 times and we didn't receive a response
               $this->logger->logit("No response from {$this->switch_ip}. Host seems to be down. If it is up, check your SNMP community.");
               ## so mark host as down
               $this->props['host_down'] = true;
               return false;
            }
         }
         else
         {
            ## We already have the description, which means that host is up, so get the OID we are interested in
            $this->logger->debug("Retrieving OID $oid from switch {$this->switch_ip}",3);
            $temp=@snmprealwalk($this->switch_ip,$community,$oid);
         }
         if ($temp)
         {
            ## There is information for the OID we asked for, clean it
            $temp=array_map("remove_type",$temp);
            ## And get the first value of the returned array
            $temp=array_shift($temp);
            return $temp;  
         }
         else
         {
            ## Apparently the host is up, but there is not information available, then this OID is not present on the queried device
            $this->logger->debug("OID $oid is not present on switch {$this->switch_ip}",3);
            return false;
         }
      }
      else
      {
         ## Useless to continue if we don't have a valid IP or host is down
         return false;
      }
   }

   /**
   * Get an OID which returns an array, in other words, use this method when you're expecting an array
   * @param mixed $community            SNMP community to use
   * @param mixed $oid                  OID to query for
   * @return mixed                      The value of the OID, false otherwise
   */
   private function getArrayOID($community,$oid)
   {
      ## Continue if there is a valid ip and host is not down
      if ($this->switch_ip && ! $this->host_down )
      {
         ## This test is to avoid performing SNMP queries on hosts which are not responsive
         if (( ! $this->description ) && (strcmp($oid,'1.3.6.1.2.1.1.1')==0))
         {
            ## Try to get the description. This OID should be available on all switches.
            ## Try 3 times before declaring switch dead
            for ($i = 0; $i < 3; $i++)
            {
               $temp=@snmprealwalk($this->switch_ip,$community,$oid);
               if ($temp)
                  $i=4;
            }
            if ( ! $temp )
            {
               ## We have tried 3 times and we didn't receive a response
               $this->logger->logit("No response from {$this->switch_ip}. Host seems to be down. If it is up, check your SNMP community.");
               ## so mark host as down
               $this->props['host_down'] = true;
               return false;
            }
         }
         else
         {
            ## We already have the description, which means that host is up, so get the OID we are interested in
            $this->logger->debug("Retrieving OID $oid from switch {$this->switch_ip}",3);
            $temp=@snmprealwalk($this->switch_ip,$community,$oid);
         }
         if ($temp)
         {
            ## There is information for the OID we asked for, clean it and return the array
            $temp=array_map("remove_type",$temp);
            return $temp;
         }
         else
         {
            ## Apparently the host is up, but there is not information available, then this OID is not present on the queried device
            $this->logger->debug("OID $oid is not present on switch {$this->switch_ip}",3);
            return false;
         }
      }
      else
      {
         ## Useless to continue if we don't have a valid IP or host is down
         return false;
      }
   }
   
   /**
   * Get the first element of an array.
   * @param array $array_var		Array we want to manipulate
   * @return mixed			First value in the array, false otherwise
   */
   private function getFirstFromArray($array_var)
   {
      if (is_array($array_var))
      {
         $temp_array=array();
         ## Clean out empty values
         foreach ($array_var as $record)
         {
            if ($record)
               $temp_array[]=$record;
         }
         $temp=array_shift($temp_array);
         return $temp;
      }     
      return false;
   }

   /**
   * Get the list of physical interfaces on the switch
   * @param mixed $community		SNMP query to use
   */
   private function pollInterfaces($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Textual name of the interfaces
      $interfaces=$this->getArrayOID($community,'1.3.6.1.2.1.31.1.1.1.1');
      ## Has the interface a physical connector?
      $physical=$this->getArrayOID($community,'1.3.6.1.2.1.31.1.1.1.17');
      ## Value of the instance of the ifIndex object
      #$ports=$this->getArrayOID($community,'1.3.6.1.2.1.17.1.4.1.2');
      $result=array();
      # Ports contains a list of ports which are in the switch
      #if (! $ports || ! $interfaces || ! $physical)
      if (! $interfaces || ! $physical)
         return false;
      foreach ($interfaces as $k => $v)
      {
         $if_index=get_last_index($k);
         if (stristr(array_find_key($if_index, $physical,'.',1),'true'))
         {
            #This is the last test, it means the interface is a physical one
            $result[$k]=$v;
         }
      }
      $this->props['interfaces']=$result;
   }

   /**
   * Get the description of this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollDescription($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description )
      {
         ## Textual description on the entity
         $this->props['description']=$this->getSingleOID($community,'1.3.6.1.2.1.1.1');
      }
   }

   /**
   * Get the switch name
   * @param mixed $community            SNMP query to use
   */
   private function pollName($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system name if we haven't yet done so
      if ( ! $this->name )
      {
         ## An administratively-assigned name for this managed node
         $this->props['name']=$this->getSingleOID($community,'1.3.6.1.2.1.1.5');
      }
   }

   /**
   * Get the switch location defined in the switch
   * @param mixed $community            SNMP query to use
   */
   private function pollLocation($community=false)
   { 
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's location if we haven't yet done so
      if ( ! $this->location )
      {
         ## The physical location of this node
         $this->props['location']=$this->getSingleOID($community,'1.3.6.1.2.1.1.6');
      }
   }  

   /**
   * Get the contact details defined in the switch
   * @param mixed $community            SNMP query to use
   */
   private function pollContact($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      # Query for this system's contact information if we haven't yet done so
      if ( ! $this->contact)
      {
         ## The textual identification of the contact person for this managed node
         $this->props['contact']=$this->getSingleOID($community,'1.3.6.1.2.1.1.4');
      }
   }

   /**
   * Get the serial number of this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollSerial_Number($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's serial number if we haven't yet done so
      if ( ! $this->serial_number)
      {
         ## The vendor-specific serial number string for the physical entity
         $this->props['serial_number']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.11'));
      }
   }

   /**
   * Get the model of this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollModel($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's model if we haven't yet done so
      if ( ! $this->model)
      {
         ## The vendor-specific model name identifier string associated with this physical component.
         $this->props['model']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.13'));
      }
   }

   /**
   * Get the hardware version of this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollHardware($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community); 
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's hardware version if we haven't yet done so
      if ( ! $this->hardware)
      {
         ## Vendor-specific hardware revision
         $this->props['hardware']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.8'));
      }
   }

   /**
   * Get the interface descriptions 
   * @param mixed $community            SNMP query to use
   */
   private function pollInterface_Descriptions($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
       ## Query for this system's interface descriptions if we haven't yet done so
      if ( ! $this->interface_descriptions )
      {
         ## Alias name for an interface, as defined by a network manager
         $this->props['interface_descriptions']=$this->getArrayOID($community,'1.3.6.1.2.1.31.1.1.1.18');
      }
   }

   /**
   * Get the firmware version of this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollFirmware($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's firmware version if we haven't yet done so
      if ( ! $this->firmware )
      {
         ## Vendor-specific firmware revision
         $this->props['firmware']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.9'));
      }
   }

   /**
   * Get the software version present on this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollSoftware($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for this system's software version if we haven't yet done so
      if ( ! $this->software )
      {
         ## Vendor-specific software revision
         $this->props['software']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.10'));
      }
   }
 
   /**
   * Initialize the Switch_SNMP object
   * @param mixed $ip		IP address of the switch
   */
   public function __construct($ip)
   {
      parent::__construct();
      ## Check if there is SNMP support
      if ( ! defined('SNMP_NULL'))
      {
         $this->logger->logit("Your installation of PHP lacks support for SNMP", LOG_ERR);
         $this->props['host_down']=true;
      }
      else
      {
         ## There is SNMP support, validate IP address
         $valid = true;
         $tmp = explode(".", $ip);
         if(count($tmp) < 4)
         {
            $valid = false;
         }
         else
         {
            foreach($tmp AS $sub)
            {
               if ($valid != false)
               {
                  if(!eregi("^([0-9])", $sub))
                  {
                     $valid = false;
                  }
                  else 
                  {
                     $valid = true;
                  }
               }
            }
         }
         if ($valid)
            ## Valid IP address
            $this->props['switch_ip']=$ip;
         else
         {
            ## Invalid IP address
            $this->logger->logit("IP address $ip is invalid");
            $this->props['host_down']=true;
         }
      }
   }

   /**
   * Get the status associate to each interface
   * @param mixed $community            SNMP query to use
   */
   private function pollInterface_Status($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for status of the interfaces present on this switch if we haven't yet done so
      if ( ! $this->interface_status )
      {
         ## The desired state of the interface
         $this->props['interface_status']=$this->getArrayOID($community,'1.3.6.1.2.1.2.2.1.7');
      }
   } 
   
   /**
   * Get the vlans present on the switch
   * @param mixed $community            SNMP query to use
   */
   private function pollVlans($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
            $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query the vlans present on the switch if we haven't yet done so
      if ( ! $this->vlans )
      {
         ## Vlans found on the switch
         if (stristr($this->description,'cisco'))
            ## The name of this vlan
            $this->props['vlans']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.46.1.3.1.1.4');
         else
            ## An administratively assigned string, which may be used to identify the VLAN
            $this->props['vlans']=$this->getArrayOID($community,'1.3.6.1.2.1.17.7.1.4.3.1.1');
      }
   }

   /**
   * Get the the list of vlans present on the ports
   * @param mixed $community            SNMP query to use
   */
   private function pollVlans_On_Ports($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
            $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query for the vlans present on the ports if we haven't yet done so
      if ( ! $this->vlans_on_ports )
      {
         ## Vlans found on the switch
         if (stristr($this->description,'cisco'))
            ## The VLAN ID of the VLAN the port is assigned to when the port is set to static or dynamic
            $this->props['vlans_on_ports']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.68.1.2.2.1.2');
         else
            ## The VLAN ID assigned to untagged frames
            $this->props['vlans_on_ports']=$this->getArrayOID($community,'1.3.6.1.2.1.17.7.1.4.5.1.1');
      }
   }
  
   /**
   * Get the list of trunk ports 
   * @param mixed $community            SNMP query to use
   */
   private function pollTrunk_Ports($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query the list of trunk ports if we haven't yet done so
      if (stristr($this->description,'cisco'))
      {
         ## The type of VLAN membership assigned to this port.
         $this->props['trunk_ports']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.46.1.6.1.1.14');
      }
   }

   /**
   * Get the type of the ports present on this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollPort_Type($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query the port types if we haven't yet done so
      if ( ! $this->port_type)
      {
         if (stristr($this->description,'cisco'))
         {
            ## The type of VLAN membership assigned to this port.
            $this->props['port_type']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.68.1.2.2.1.1');
         }
         else
         {
            ## We don't have yet an OID for non-Cisco switches, get instead the vlans on ports
            if ( ! $this->vlans_on_ports )
               $this->vlans_on_ports($community);
            $this->props['port_type']=true;
         }
      }
   }

   /**
   * Get the list of devices physically attached to this switch
   * @param mixed $community            SNMP query to use
   */
   private function pollConnected_Devices($community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query vlans on this switch if we haven't yet done so
      if ( ! $this->vlans )
         $this->pollVlans($community);
      ## Query list of connected devices if we haven't yet done so
      if ( ! $this->connected_devices )
      {
         ## Loop through each vlan
         foreach ($this->vlans as $vlan)
         {
            ## Vlan ID as declared on the switch
            $vlan_id=$this->getVlan_ID($vlan);
            ## Use that vlan ID to query the switch
            $temp_community=$community.'@'.$vlan_id;
            ## Obtain MAC address table
            $macs = $this->getArrayOID($temp_community,'1.3.6.1.2.1.17.4.3.1.1');
            if ( ! $macs)
               continue;
            foreach ($macs as $k => $v)
            {
               $macs_on_switch[$k]=$v;
               $macs_on_vlan[$v]=$vlan_id;
            }
               
         }
         ## If we didn't find any macs, don't continue
         if ( ! is_array($macs_on_switch))
            return false;
         $counter=count($macs_on_switch);
         if ( ! $counter)
            return false;
         ## Loop through the list of macs found
         for ($i=0; $i < $counter; $i++)
         {
            ## What vlan is using this mac?
            $mac_on_vlan=current($macs_on_vlan);
            $temp_community=$community.'@'.$mac_on_vlan;
            ## Where is this mac?
            $mac_on=array_search(current($macs_on_switch),$macs_on_switch);
            ## Get bridge port number for vlan
            $bridge_port_number=$this->getArrayOID($temp_community,'1.3.6.1.2.1.17.4.3.1.2');
            if ( ! $bridge_port_number )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            ## Where is this MAC?
            $bridge_port=array_find_key($mac_on,$bridge_port_number,'.',5);          
            if ( ! $bridge_port )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            ## Map the bridge port to the ifIndex
            $map_bridge_port = $this->getArrayOID($temp_community,'1.3.6.1.2.1.17.1.4.1.2');
            if ( ! $map_bridge_port)
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            ## Where is this MAC?
            $map_bridge=array_find_key($bridge_port,$map_bridge_port,'.',1);
            if ( ! $map_bridge )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            ## Get list of interfaces if we haven't yet done so
            if ( ! $this->interfaces )
               $this->pollInterfaces();
            ## Find the port where this mac is connected to 
            $port_learnt=array_find_key($map_bridge,$this->interfaces,'.',1);
            if ( ! $port_learnt )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            else
            {
               $mb = explode(' ',current($macs_on_switch));
               $newmac = $mb[0].$mb[1].'.'.$mb[2].$mb[3].'.'.$mb[4].$mb[5];
               ## Mac is connected to one this switch's interfaces, fill up information
               $this->props['connected_devices']['mac'][]=strtolower($newmac);
               $this->props['connected_devices']['vlan'][]=$mac_on_vlan;
               $this->props['connected_devices']['port'][]=$port_learnt;
            }
            next($macs_on_switch);
            next($macs_on_vlan);
         }
      }
   } 

   /**
   * Get the vlan id as declared in the switch for the requested vlan name
   * @param mixed $vlan_name		The name of the vlan we are interested in
   * @param mixed $community		SNMP community to use
   * @return mixed 			The Vlan id if present on this switch, false otherwise
   */ 
   public function getVlan_ID($vlan_name, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc 
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Query the vlans present on this switch if we haven't yet done so
      if ( ! $this->$vlans )
         $this->pollVlans($community);
      ## Get the index for the vlan
      if ( ! $vlan_index=get_snmp_index($vlan_name,$this->vlans))
      {
         return false;
      }
      else 
      {
         return $vlan_index;
      }
   }

   /**
   * Get port type for the specified port
   * @param mixed $port            	The port name 
   * @param mixed $port_index		SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return mixed                      The Vlan id if present on this switch, false otherwise
   */
   public function getPortType($port, $port_index=false, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Don't continue if there is not a port specified
      if ( ! $port )
         return false;
      ## Query for this system description if we haven't yet done so
      if ( ! $this->description)
         $this->pollDescription($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get interfaces
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      ## Get port types
      if ( ! $this->port_type($community))
         $this->pollPort_Type($community);
      ## Get the SNMP port index
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      if (stristr($this->description,'cisco'))
      {
         ## Get trunk ports
         if ( ! $this->trunk_ports ) 
            $this->pollTrunk_Ports($community);
         ## Check if port is a trunk port
         $trunk = array_find_key($port_index,$this->trunk_ports,'.',1);
         if ($trunk==1)
            return 3;
         else
            return array_find_key($port_index,$this->port_type, '.',1);               
      }
      else
      {
         $vlan = array_find_key($port_index, $this->vlans_on_ports, '.', 1);
         if ($vlan > 1)
            ## Port has a vlan assigned to it, therefore it should be static
            return 1;
         else
            ## Port doesn't have a vlan assigned to it, therefore it is a multivlan port
            return 3;
      }
   }

   /**
   * Get the description for the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return mixed                      The description of the port, false otherwise
   */
   public function getPortDescription($port, $port_index=false, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Don't continue if there is not a port specified   
      if ( ! $port )
         return false;
      ## List of interfaces
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get the SNMP port index
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      ## Get the interface descriptions
      if (! $this->interface_descriptions )
         $this->pollInterface_Descriptions($community);
      return array_find_key($port_index, $this->interface_descriptions, '.',1);
   }
  
   /**
   * Get the vlan associated to the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return mixed                      The vlan associated to the port, false otherwise
   */
   public function getVlanOnPort($port, $port_index=false, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Don't continue if there is not a port specified
      if ( ! $port )
         return false;
      ## List of interfaces
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get the SNMP port index
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      ## Get the list of vlans on ports
      if ( ! $this->vlans_on_ports )
         $this->pollVlans_On_Ports($community);
      return array_find_key($port_index, $this->vlans_on_ports, '.', 1);
   }

   /**
   * Get the status for the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return mixed                      The status associated to the port, false otherwise
   */
   public function getPortStatus($port, $port_index=false, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      ## Don't continue if there is not a port specified
      if ( ! $port )
         return false;
      ## List of interfaces
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get ports status
      if ( ! $this->interface_status )
         $this->pollInterface_Status($community);
      ## Get the SNMP port index
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      $status = array_find_key($port_index, $this->interface_status, '.', 1);
      if (stristr($status,'up'))
         return 1;
      else
         return 2;       
   }
   
   /**
   * Interceptor to call the poll function associated to a certain property
   * with this interceptor is possible to do something like $object->property()
   * and if property is already defined, it'll return its value. If not, it will
   * call its associated function as long as there is a pollProperty method present in
   * the class. Then it'll return the value of the property
   * @param mixed $methodName		The name of the property we want to know
   * @param array $parameters		The parameters to use. Ideally only an SNMP community will be used
   * @return mixed			The value of the requested property
   */
   public function __call($methodName, $parameters)
   {
      ## Don't continue if we haven't specified a property
      if ( ! $this->$methodName )
      {
         $method_to_call=strtolower("poll$methodName");
         ## Take the first parameter as the community
         $community=array_shift($parameters);
         if (method_exists($this, $method_to_call))
         {
            ## Call the method
            $this->$method_to_call($community);
            ## And return its value
            return $this->$methodName;
         }
         else
         {
            return false;
         }
      }
      else
      {
         ## Return the value of the property
         return $this->$methodName;
      }
   }

   /**
   * Get the value of one property if it exists
   * @param mixed $key          Property to lookup
   * @return mixed              The value of the wanted property, or false if such a property doesn't exist
   */
   public function __get($key)
   {
      if (array_key_exists($key,$this->props))
         return $this->props[$key];
   }

   /**
   * Return all properties assigned to this system. This method is here only for debugging purposes, please delete it after
   * @return array      All properties present
   */
   public function getAllProps()
   {
      return $this->props;
   }

   /**
   * Turn off the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return boolean                    Result of the operation
   */
   public function turnOffPort($port,$port_index=false,$community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      ## Get the SNMP port index
      if (!$port_index)
      {
         if ( ! $this->interfaces)
            $this->pollInterfaces($community);
         ## Don't continue if host has been marked as down
         if ( $this->host_down )
            return false;
         $port_index=get_snmp_index($port,$this->interfaces);
      }
      ## Turn off port
      if (turn_off_port($this->switch_ip, $port,$port_index) && turn_off_port($this->switch_ip, $port,$port_index))
      {
         #TBD: Update port status in memory
         return true;
      }
      else
      {
         return false;
      }
   }

   /**
   * Turn on the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return boolean                    Result of the operation
   */
   public function turnOnPort($port,$port_index=false,$community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      ## Get the SNMP port index
      if (! $port_index)
      {
         if ( ! $this->interfaces)
            $this->pollInterfaces($community);
         ## Don't continue if host has been marked as down
         if ( $this->host_down )
            return false;
         $port_index=get_snmp_index($port,$this->interfaces);
      }
      ## Turn on port
      if (turn_on_port($this->switch_ip, $port,$port_index))
      {
         #TBD: Update port status in memory
         return true;
      }
      else
      {
         return false;
      }
   }
   
   /**
   * Turn on the specified port
   * @param mixed $port                 The port name
   * @param mixed $port_index           SNMP port index if available
   * @param mixed $community            SNMP community to use
   * @return boolean                    Result of the operation
   */
   public function restartPort($port, $port_index=false, $community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      ## Restart port
      if ($this->turnOffPort($port, $port_index, $community) && $this->turnOnPort($port, $port_index, $community))
         return true;
      else
         return false;
   }

   /**
   * Program the specified vlan on the specified port
   * @param mixed $port_name       	The port name
   * @param mixed $vlan_name           	Vlan name to program
   * @param mixed $community            SNMP community to use
   * @return boolean                    Result of the operation
   */
   public function programVlanOnPort($port_name,$vlan_name,$community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      ## Get interfaces
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      ## Don't continue of host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get SNMP index
      if ( ! $port_index=get_snmp_index($port_name,$this->interfaces))
      {
         $this->logger->logit("Port $port_name could not be found on switch {$this->switch_ip}", LOG_ERR);
         return false;
      }
      ## List of vlans on switch
      if ( ! $this->vlans)
         $this->pollVlans($community);
      ## Get vlan SNMP index
      if ( ! $vlan_index=get_snmp_index($vlan_name,$this->vlans))
      {
         $this->logger->logit("VLAN $vlan_name could not be found on switch {$this->switch_ip}", LOG_ERR);
         return false;
      }
      ## Get description
      if ( ! $this->description )
         $this->pollDescription($community);
      ## Query port type
      if ( $this->getPortType($port_name, $port_index,$community) == 3 )
      {
         $this->logger->logit("Programming of trunk ports is not allowed");
         return false;
      }
      ## Turn it off
      if (! $this->turnOffPort($port_name,$port_index)) 
         return false;
      if (stristr($this->description,'cisco'))
      {
         ## Set port to static
         if (snmpset($this->switch_ip, $community, '1.3.6.1.4.1.9.9.68.1.2.2.1.1.'.$port_index, 'i', 1))
         {
            ## And program the requested vlan on it
            if (snmpset($this->switch_ip, $community, '1.3.6.1.4.1.9.9.68.1.2.2.1.2.'.$port_index,'i', $vlan_index))
            {
               $this->logger->logit("VLAN $vlan_name has been programmed on port $port_name on switch {$this->switch_ip}");
            }
            else
            {
               $this->logger->logit("VLAN $vlan_name could not be programmed on port $port_name on switch {$this->switch_ip}");
            }
         }
         else
         {
            $this->logger->logit("Could not set port $port_name on switch {$this->switch_ip} to static");
         }
      }
      else
      {
         ## Program the requested vlan on the port
         if (snmpset($this->switch_ip, $community, '1.3.6.1.2.1.17.7.1.4.5.1.1.'.$port_index, 'u',$vlan_index))
         {
            $this->logger->logit("VLAN $vlan_name has been programmed on port $port_name on switch {$this->switch_ip}");
         }
         else
         {
            $this->logger->logit("VLAN $vlan_name could not be programmed on port $port_name on switch {$this->switch_ip}");   
         } 
      }     
      return $this->turnOnPort($port_name,$port_index);
   }

   /**
   * Set a port to VMPS
   * @param mixed $port_name            The port name
   * @param mixed $community            SNMP community to use
   * @return boolean                    Result of the operation
   */
   public function SetPortToDynamic($port_name,$community=false)
   {
      ## If no community has been specified, use the one defined in etc/config.inc
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      ## List of interfaces
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      ## Don't continue if host has been marked as down
      if ( $this->host_down )
         return false;
      ## Get port SNMP index
      if ( ! $port_index=get_snmp_index($port_name,$this->interfaces))
      {
         $this->logger->logit("Port $port_name could not be found on switch {$this->switch_ip}", LOG_ERR);
         return false;
      }
      ## Description
      if ( ! $this->description )
         $this->pollDescription($community);
      if (stristr($this->description,'cisco'))
      {
         ## Query port type
         if ( $this->getPortType($port_name, $port_index,$community) == 3 )
         {
            $this->logger->logit("Programming of trunk ports is not allowed");
            return false;
         }
         ## Turn off port
         if (! $this->turnOffPort($port_name,$port_index))
            return false;
         ## And program it as dynamic
         if (snmpset($this->switch_ip, $community, '1.3.6.1.4.1.9.9.68.1.2.2.1.1.'.$port_index,'i',2))
            $this->logger->logit("Port $port_name on switch {$this->switch_ip} has been set to dynamic");
         else
            $this->logger->logit("Port $port_name on switch {$this->switch_ip} could not be set to dynamic");
         ## Turn on port
         return $this->turnOnPort($port_name,$port_index);
      }
      else
      {
         $this->logger->logit("This feature is only supported by Cisco switches");
         return false;
      }
   }
}

?>
