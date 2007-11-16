<?php

class WSUSEndDevice extends EndDevice
{
   public function __construct($object) 
   {
      parent::__construct($object);
      $query=<<<EOF
      SELECT ct.targetid as wsus_targetid,
             ct.ipaddress AS wsus_ip, 
             ct.fulldomainname AS wsus_hostname, 
             om.osshortname AS wsus_os_short, 
             om.oslongname AS wsus_os_long, 
             ct.oslocale AS wsus_language, 
             ct.computermake AS wsus_computermake, 
             ct.computermodel AS wsus_computermodel, 
             ct.lastsynctime AS wsus_last_sync 
             FROM nac_wsuscomputertarget ct 
             INNER JOIN nac_wsusosmap om ON ct.osid=om.osid 
             WHERE ct.sid={$this->sid};
EOF;
      $this->logger->debug($query,3);
      if ($temp=mysql_fetch_one($query))
      {
         foreach($temp as $key => $value)
            $this->db_row[$key]=$value;
      }
      if ($this->wsus_targetid)
      {
         $query=<<<EOF
         SELECT u.title AS wsus_patch_title, 
                u.kbarticleid AS wsus_kb_number
                FROM nac_wsusupdatestatuspercomputer uspc 
                INNER JOIN nac_wsusupdate u ON uspc.updateid=u.localupdateid 
                WHERE uspc.targetid={$this->wsus_targetid} AND uspc.summarizationstate=4;
EOF;
         $this->logger->debug($query,3);
         $result=mysql_query($query);
         if ($result)
         {
            while ($row=mysql_fetch_array($result,MYSQL_ASSOC))
               $this->db_row['wsus_patches'][]=$row;
         }
      }
   }  
}
?>
