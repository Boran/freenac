<?php

class SMSEndDevice extends EndDevice
{

   public function insertInUnknown()
   {
      if (!$this->inDB() && $this->port_id)
      {
         #Enterprise only
         if ($this->conf->lastseen_sms)
         {
            $retval='';
            $sms_details=syscall($this->conf->sms_mac." ".$this->mac, $retval);

            # Enable PC and set to SMS VLAN
            if (preg_match("/Host=(\S+) NtAccount=(\S+) OS=(.+)$/",$sms_details, $matches))
            {
               $sms_name=$matches[1];
               $txx_name= $conf->default_user_unknown;         # default
               $txx_name   =$matches[2];
               $sms_os     =$matches[3];
               $sms_details="";
               $this->logger->logit("SMS PC: name=$sms_name, NTaccount=$txx_name, $sms_os, $sms_details");
               $query="SELECT id FROM sys_os3 WHERE value='$sms_os';";
               $this->logger->debug($query,3);
               $os3_id=v_sql_1_select($query);

               insert_user($txx_name);
               direx_sync_user($txx_name);

               $query="select id from users where username like '$txx_name';";
               $this->logger->debug($query,3);
               $uid=v_sql_1_select($query);
               if (!$uid)
                  $uid=0;
               if ($this->conf->lastseen_sms_vlan)
                  $vlan_id=$this->conf->lastseen_sms_vlan;
               $query="INSERT INTO systems "
                    . "SET LastSeen=NOW(), status=1, class=2,"      # active, GWP
                    .      "description='$txx_name', "   # nt account
                    .      "uid='$uid', "
                    .      "name='$sms_name', "
                    .      "comment='$sms_details', "
                    .      "vlan='$vlan_id', "
                    .      "os3='$os3_id', "
                    .      "os4='$sms_os', "
                    .      "lastport='{$this->port_id}', "
                    .      "office='{$this->office_id}', "
                    .      "mac='{$this->mac}' ";
               $this->logger->debug($query,3);
               $res = mysql_query($query);
               if ($res)
               {
                  # Document the user's details in the alert
                  $query="SELECT CONCAT(Givenname,' ',Surname,' ',Department,' ',Mobile) FROM users WHERE username='$txx_name'";
                  $this->logger->debug($query,3);
                  $res = mysql_query($query);
                  if ($res)
                  {
                     list($sms_details)=mysql_fetch_array($res);
                     $subject="NAC alert in {$this->patch_info}, $sms_details, port {$this->port_info}\n";
                     $mesg="New {$this->conf->sms_device} {$this->mac}({$this->getVendor()}) $sms_name, $txx_name, $sms_details, switch {$this->switch_info}\n";
                     $this->logger->logit($subject);
                     $this->logger->logit($mesg);
                     #Restart port
                     snmp_restart_port_id($this->port_id);
                     return true;
                  }
                  else
                  {
                     $this->logger->logit(mysql_error(),LOG_ERROR);
                     return false;
                  }
               }
               else
               {
                  $this->logger->logit(mysql_error(),LOG_ERROR);
                  return false;
               }
            }
            else
            {
               return parent::insertIfUnknown();
            }
        }
      }
   }

}
?>