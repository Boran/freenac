<?php
/**
 * PortScan.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Seiler Thomas (contributer)
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                        http://www.freenac.net
 */

/**
 * This class represents a row in the systems table in the database.
 * Additionaly, it retrieves information about open ports for this end device.
 * This class extends the {@link EndDevice} class 
 */
class PortScan extends EndDevice
{

   /**
   * Retrieve a row from the systems table for this system and also port_scan information
   * @param object $object	Required to construct our object
   */
   public function __construct($object)
   {
      #Call parent constructor to retrieve a row from the systems table
      parent::__construct($object);
 
      #And now retrieve port_scan information for this system
      if ($this->inDB())
      {
         #Check if this system is in the nac_hostscanned table
         $query=<<<EOF
            SELECT ip AS port_scan_ip, 
            hostname AS port_scan_hostname, 
            os AS port_scan_os, 
            timestamp AS port_scan_lastscanned 
            FROM nac_hostscanned 
            WHERE sid='{$this->getEndDeviceID()}';
EOF;
         $this->logger->debug($query,3);
         $res=mysql_query($query);
         if ($res)
         {
            #If so, assign all properties retrieved to our internal array
            $row=mysql_fetch_assoc($res);
            foreach ($row as $k => $v)
               $this->db_row[$k]=$v;
            
            #Now, get the list of open ports for this device
            $query=<<<EOF
               SELECT s.port, p.name AS protocol, s.name AS service, o.banner, o.timestamp
               FROM nac_openports o INNER JOIN services s ON o.service=s.id
               INNER JOIN protocols p on p.protocol=s.protocol WHERE o.sid='{$this->getEndDeviceID()}';
EOF;
            $this->logger->debug($query,3);
            $res=mysql_query($query);
            if ($res)
            {
               $open_ports=mysql_num_rows($res);
               #How many open ports?
               $this->db_row['open_ports']=$open_ports;
               if ($open_ports > 0)
               {
                  #And store those open ports in our internal array
                  while ($row=mysql_fetch_assoc($res))
                     $this->db_row['ports'][]=$row;
               }
            }
         }
      }
   }

   /**
   * Get the number of open ports for this device
   * @return mixed	Number of ports open or false if the device is not in de database
   */
   public function openPorts()
   {
      if ($this->inDB())
      {
         return $this->open_ports;
      }
      else
      {
         return false;
      }      
   }

   /**
   * Get all open ports for this system
   * @return array		List of open ports according to the nac_openports table
   */ 
   public function getPorts()
   {
      if ($this->inDB() && $this->openPorts())
      {
         return $this->ports;
      }
   }

   /**
   * Tell if a specific port has been open during the last days
   * @param integer $port	Port number
   * @param mixed $protocol	Protocol for this port
   * @param integer $days	Number of days to check if the port has been open for
   * @return boolean		True if port has been open during the last days, false otherwise
   */
   public function isPortOpen($port = 0, $protocol = 'TCP', $days = 7)
   {
      if ($days && !is_numeric($days))
         $days=7;
      #Convert number of days in seconds
      #This is done so because our time_diff function returns the difference between two dates in seconds
      $days_in_seconds = $days*24*3600;
      if ($port && is_numeric($port) && $this->openPorts())
      {
         foreach($this->ports as $temp_port)
            #Check if this port is the one we are refering to
            if (($temp_port['port'] == $port) && (strcasecmp($temp_port['protocol'], trim($protocol)) == 0))
               #If so, check if this port was open in the last $days days
               if ( time_diff($this->port_scan_lastscanned, date('Y-m-d H:i:s')) < $days_in_seconds)
               {
                  return true;
               }
         return false;
      }
      else
      {
         return false;
      }
   }
}

?>
