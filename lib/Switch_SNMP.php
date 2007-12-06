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

class Switch_SNMP extends Common
{
   private $props=array(); 
   
   private function getSingleOID($community,$oid)
   {
      if ($this->switch_ip && ! $this->host_down)
      {
         if (( ! $this->description ) && (strcmp($oid,'1.3.6.1.2.1.1.1')==0))
         {
            $temp=@snmprealwalk($this->switch_ip,$community,$oid,0,3);
            if ( ! $temp )
            {
               $this->logger->logit("No response from {$this->switch_ip}. Host seems to be down. If it is up, check your SNMP community.");
               $this->props['host_down'] = true;
               return false;
            }
         }
         else
         {
            $this->logger->debug("Retrieving OID $oid from switch {$this->switch_ip}",3);
            $temp=@snmprealwalk($this->switch_ip,$community,$oid);
         }
         if ($temp)
         {
            $temp=array_map("remove_type",$temp);
            $temp=array_shift($temp);
            return $temp;  
         }
         else
         {
            $this->logger->debug("OID $oid is not present on switch {$this->switch_ip}");
            return false;
         }
      }
      else
      {
         return false;
      }
   }

   private function getArrayOID($community,$oid)
   {
      if ($this->switch_ip && ! $this->host_down )
      {
         if (( ! $this->description ) && (strcmp($oid,'1.3.6.1.2.1.1.1')==0))
         {
            $temp=@snmprealwalk($this->switch_ip,$community,$oid,0,3);
            if ( ! $temp )
            {
               $this->logger->logit("No response from {$this->switch_ip}. Host seems to be down. If it is up, check your SNMP community.");
               $this->props['host_down'] = true;
               return false;
            }
         }
         else
         {
            $this->logger->debug("Retrieving OID $oid from switch {$this->switch_ip}",3);
            $temp=@snmprealwalk($this->switch_ip,$community,$oid);
         }
         if ($temp)
         {
            $temp=array_map("remove_type",$temp);
            return $temp;
         }
         else
         {
            $this->logger->debug("OID $oid is not present on switch {$this->switch_ip}");
            return false;
         }
      }
      else
      {
         return false;
      }
   }

   private function getFirstFromArray($array_var)
   {
      if (is_array($array_var))
      {
         $temp_array=array();
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

   private function pollInterfaces($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
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

   private function pollDescription($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description )
      {
         ## Textual description on the entity
         $this->props['description']=$this->getSingleOID($community,'1.3.6.1.2.1.1.1');
      }
   }

   private function pollName($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->name )
      {
         ## An administratively-assigned name for this managed node
         $this->props['name']=$this->getSingleOID($community,'1.3.6.1.2.1.1.5');
      }
   }

   private function pollLocation($community=false)
   { 
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->location )
      {
         ## The physical location of this node
         $this->props['location']=$this->getSingleOID($community,'1.3.6.1.2.1.1.6');
      }
   }  

   private function pollContact($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->contact)
      {
         ## The textual identification of the contact person for this managed node
         $this->props['contact']=$this->getSingleOID($community,'1.3.6.1.2.1.1.4');
      }
   }

   private function pollSerial_Number($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->serial_number)
      {
         ## The vendor-specific serial number string for the physical entity
         $this->props['serial_number']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.11'));
      }
   }

   private function pollModel($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->model)
      {
         ## The vendor-specific model name identifier string associated with this physical component.
         $this->props['model']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.13'));
      }
   }

   private function pollHardware($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community); 
      if ( ! $this->hardware)
      {
         ## Vendor-specific hardware revision
         $this->props['hardware']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.8'));
      }
   }

   private function pollInterface_Descriptions()
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->interface_descriptions )
      {
         ## Vendor-specific firmware revision
         $this->props['interface_descriptions']=$this->getArrayOID($community,'1.3.6.1.2.1.31.1.1.1.18');
      }
   }

   private function pollFirmware($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->firmware )
      {
         ## Vendor-specific firmware revision
         $this->props['firmware']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.9'));
      }
   }

   private function pollSoftware($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->software )
      {
         ## Vendor-specific software revision
         $this->props['software']=$this->getFirstFromArray($this->getArrayOID($community,'1.3.6.1.2.1.47.1.1.1.1.10'));
      }
   }
 
   public function __construct($ip)
   {
      parent::__construct();
      if ( ! defined('SNMP_NULL'))
      {
         $this->logger->logit("Your installation of PHP lacks support for SNMP", LOG_ERROR);
         return false;
      }
      else
      {
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
            $this->props['switch_ip']=$ip;
         else
         {
            $this->logger->logit("IP address $ip is invalid");
            $this->props['host_down']=true;
         }
      }
   }

   private function pollInterface_Status($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->interface_status )
      {
         ## The desired state of the interface
         $this->props['interface_status']=$this->getArrayOID($community,'1.3.6.1.2.1.2.2.1.7');
      }
   } 
   
   private function pollVlans($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
            $this->pollDescription($community);
      if ( $this->host_down )
         return false;
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

   private function pollVlans_On_Ports($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
            $this->pollDescription($community);
      if ( $this->host_down )
         return false;
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
  
   private function pollTrunk_Ports($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if (stristr($this->description,'cisco'))
      {
         ## The type of VLAN membership assigned to this port.
         $this->props['trunk_ports']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.46.1.6.1.1.14');
      }
   }

   private function pollPort_Type($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->port_type)
      {
         if (stristr($this->description,'cisco'))
         {
            ## The type of VLAN membership assigned to this port.
            $this->props['port_type']=$this->getArrayOID($community,'1.3.6.1.4.1.9.9.68.1.2.2.1.1');
         }
         else
         {
            if ( ! $this->vlans_on_ports )
               $this->vlans_on_ports($community);
         }
      }
   }

   private function pollConnected_Devices($community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->description)
         $this->pollDescription($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->vlans )
         $this->pollVlans($community);
      if ( ! $this->connected_devices )
      {
         foreach ($this->vlans as $vlan)
         {
            $vlan_id=$this->getVlan_ID($vlan);
            $temp_community=$community.'@'.$vlan_id;
            $macs = $this->getArrayOID($temp_community,'1.3.6.1.2.1.17.4.3.1.1');
            if ( ! $macs)
               continue;
            foreach ($macs as $k => $v)
            {
               $macs_on_switch[$k]=$v;
               $macs_on_vlan[$v]=$vlan_id;
            }
               
         }
         if ( ! is_array($macs_on_switch))
            return false;
         $counter=count($macs_on_switch);
         if ( ! $counter)
            return false;
         for ($i=0; $i < $counter; $i++)
         {
            $mac_on_vlan=current($macs_on_vlan);
            $temp_community=$community.'@'.$mac_on_vlan;
            $mac_on=array_search(current($macs_on_switch),$macs_on_switch);
            $bridge_port_number=$this->getArrayOID($temp_community,'1.3.6.1.2.1.17.4.3.1.2');
            if ( ! $bridge_port_number )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            $bridge_port=array_find_key($mac_on,$bridge_port_number,'.',5);          
            if ( ! $bridge_port )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            $map_bridge_port = $this->getArrayOID($temp_community,'1.3.6.1.2.1.17.1.4.1.2');
            if ( ! $map_bridge_port)
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            $map_bridge=array_find_key($bridge_port,$map_bridge_port,'.',1);
            if ( ! $map_bridge )
            {
               next($macs_on_switch);
               next($macs_on_vlan);
               continue;
            }
            if ( ! $this->interfaces )
               $this->pollInterfaces();
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
               $this->props['connected_devices']['mac'][]=strtolower($newmac);
               $this->props['connected_devices']['vlan'][]=$mac_on_vlan;
               $this->props['connected_devices']['port'][]=$port_learnt;
            }
            next($macs_on_switch);
            next($macs_on_vlan);
         }
      }
   } 

   public function getVlan_ID($vlan_name, $community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $this->$vlans )
         $this->pollVlans($community);
      if ( $this->host_down )
         return false;
      if ( ! $vlan_index=get_snmp_index($vlan_name,$this->vlans))
      {
         return false;
      }
      else return $vlan_index;
   }

   public function getPortType($port, $port_index=false, $community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $port )
         return false;
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      $this->pollPort_Type($community);
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      if (stristr($this->description,'cisco'))
      {
         if ( ! $this->trunk_ports ) 
            $this->pollTrunk_Ports($community);
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
            // Port has a vlan assigned to it, therefore it should be static
            return 1;
         else
            // Port doesn't have a vlan assigned to it, therefore it is a multivlan port
            return 3;
      }
   }

   public function getPortDescription($port, $port_index=false, $community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $port )
         return false;
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      if (! $this->interface_descriptions )
         $this->pollInterface_Descriptions($community);
      return array_find_key($port_index, $this->interface_descriptions, '.',1);
   }
  
   public function getVlanOnPort($port, $port_index=false, $community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $port )
         return false;
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      if ( ! $this->vlans_on_ports )
         $this->pollVlans_On_Ports($community);
      return array_find_key($port_index, $this->vlans_on_ports, '.', 1);
   }

   public function getPortStatus($port, $port_index=false, $community=false)
   {
      if (! $community)
      {
         global $snmp_ro;
         $community=$snmp_ro;
      }
      if ( ! $port )
         return false;
      if ( ! $this->interfaces)
            $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      if ( ! $this->interface_status )
         $this->pollInterface_Status($community);
      if (! $port_index)
         $port_index=get_snmp_index($port,$this->interfaces);
      $status = array_find_key($port_index, $this->interface_status, '.', 1);
      if (stristr($status,'up'))
         return 1;
      else
         return 2;       
   }
   
   public function __call($methodName, $parameters)
   {
      if ( ! $this->$methodName )
      {
         $method_to_call=strtolower("poll$methodName");
         $community=array_shift($parameters);
         if (method_exists($this, $method_to_call))
         {
            $this->$method_to_call($community);
            return $this->$methodName;
         }
         else
         {
            return false;
         }
      }
      else
      {
         return $this->$methodName;
      }
   }

   public function __get($key)
   {
      if (array_key_exists($key,$this->props))
         return $this->props[$key];
   }

   public function getAllProps()
   {
      return $this->props;
   }

   public function turnOffPort($port,$port_index=false,$community=false)
   {
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      if (!$port_index)
      {
         if ( ! $this->interfaces)
            $this->pollInterfaces($community);
         if ( $this->host_down )
            return false;
         $port_index=get_snmp_index($port,$this->interfaces);
      }
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

   public function turnOnPort($port,$port_index=false,$community=false)
   {
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      if (! $port_index)
      {
         if ( ! $this->interfaces)
            $this->pollInterfaces($community);
         if ( $this->host_down )
            return false;
         $port_index=get_snmp_index($port,$this->interfaces);
      }
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
   
   public function restartPort($port, $port_index=false, $community=false)
   {
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      if ($this->turnOffPort($port, $port_index, $community) && $this->turnOnPort($port, $port_index, $community))
         return true;
      else
         return false;
   }

   public function programVlanOnPort($port_name,$vlan_name,$community=false)
   {
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      if ( ! $port_index=get_snmp_index($port_name,$this->interfaces))
      {
         $this->logger->logit("Port $port_name could not be found on switch {$this->switch_ip}", LOG_ERROR);
         return false;
      }
      if ( ! $this->vlans)
         $this->pollVlans($community);
      if ( ! $vlan_index=get_snmp_index($vlan_name,$this->vlans))
      {
         $this->logger->logit("VLAN $vlan_name could not be found on switch {$this->switch_ip}", LOG_ERROR);
         return false;
      }
      if ( ! $this->description )
         $this->pollDescription($community);
      if ( $this->getPortType($port_name, $port_index,$community) == 3 )
      {
         $this->logger->logit("Programming of trunk ports is not allowed");
         return false;
      }
      if (! $this->turnOffPort($port_name,$port_index)) 
         return false;
      if (stristr($this->description,'cisco'))
      {
         if (snmpset($this->switch_ip, $community, '1.3.6.1.4.1.9.9.68.1.2.2.1.1.'.$port_index, 'i', 1))
         {
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

   public function SetPortToDynamic($port_name,$community=false)
   {
      if (! $community)
      {
         global $snmp_rw;
         $community=$snmp_rw;
      }
      if ( ! $this->interfaces)
         $this->pollInterfaces($community);
      if ( $this->host_down )
         return false;
      if ( ! $port_index=get_snmp_index($port_name,$this->interfaces))
      {
         $this->logger->logit("Port $port_name could not be found on switch {$this->switch_ip}", LOG_ERROR);
         return false;
      }
      if ( ! $this->description )
         $this->pollDescription($community);
      if ( $this->getPortType($port_name, $port_index,$community) == 3 )
      {
         $this->logger->logit("Programming of trunk ports is not allowed");
         return false;
      }
      if (stristr($this->description,'cisco'))
      {
         if (! $this->turnOffPort($port_name,$port_index))
            return false;
         if (snmpset($this->switch_ip, $community, '1.3.6.1.4.1.9.9.68.1.2.2.1.1.'.$port_index,'i',2))
            $this->logger->logit("Port $port_name on switch {$this->switch_ip} has been set to dynamic");
         else
            $this->logger->logit("Port $port_name on switch {$this->switch_ip} could not be set to dynamic");
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
