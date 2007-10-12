<?php

class PortScan extends EndDevice
{
   public function __construct($object)
   {
      parent::__construct($object);
      if ($this->inDB())
      {
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
            $row=mysql_fetch_assoc($res);
            foreach ($row as $k => $v)
               $this->db_row[$k]=$v;
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
               $this->db_row['open_ports']=$open_ports;
               if ($open_ports > 0)
               {
                  while ($row=mysql_fetch_assoc($res))
                     $this->db_row['ports'][]=$row;
               }
            }
         }
      }
   }

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

   public function getPorts()
   {
      if ($this->inDB() && $this->openPorts())
      {
         return $this->ports;
      }
   }

   public function isPortOpen($port=0,$protocol='TCP')
   {
      if ($port && is_numeric($port) && $this->openPorts())
      {
         foreach($this->ports as $temp_port)
            if (($temp_port['port']==$port) && (strcasecmp($temp_port['protocol'],trim($protocol))==0))
               return true;
         return false;
      }
      else
      {
         return false;
      }
   }
}

?>
