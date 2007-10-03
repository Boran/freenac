<?php

class PortScan extends EndDevice
{
   public function __construct($object)
   {
      parent::__construct($object);
      if ($this->inDB())
      {
         $query=<<<EOF
            SELECT s.port, p.name as protocol, s.name as service, o.banner, o.timestamp
            FROM nac_openports o INNER JOIN services s ON o.service=s.id
            INNER JOIN protocols p on p.protocol=s.protocol WHERE o.sid={$this->getEndDeviceID()};
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
}

?>
