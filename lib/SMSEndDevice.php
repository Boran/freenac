<?php
/**
 * SMSEndDevice.php
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
include_once("../bin/snmp_defs.inc.php");
/**
 * This class represents a row in the systems table in the database.
 * In the current version, the host is identified by its mac address.
 * This class is a specialization for the EndDevice class overriding the
 * functionality of 'insertIfUnknown' to insert an SMS device.
 * This class extends the {@link EndDevice} class.
 */

require_once "../bin/snmp_defs.inc.php";

class SMSEndDevice extends EndDevice
{
   /**
   * Insert a new user, if not already in the Users table.
   * @return integer	Return the user id or false otherwise
   */
   public function get_insert_user_id ($username) 
   {
      ## Is this user already in our "users" table?
      $query="SELECT id FROM users WHERE username='".$username."' LIMIT 1";
      $this->logger->debug($query,3);
      $user_id=v_sql_1_select($query);

      ## The select query had no effect, so assume its a new user.
      if (!$user_id) 
      {
         ## TBD: Is this new user in our organisation? We should
         ##      really only set manual_direx_sync for "foreign" users.
         $query="INSERT INTO users SET LastSeenDirectory=NOW(), manual_direx_sync='1', "
         .      "username='".$username."'";
         $this->logger->debug($query,3);
         $res = mysql_query($query);
         if ($res)
         {
            $str = "New user added for Directory: $username" ;
            $this->logger->logit($str);
            $query="SELECT id FROM users WHERE username='".$username."' LIMIT 1";
            $this->logger->debug($query,3);
            $user_id=v_sql_1_select($query);
            direx_sync_user($username);  // Download details on this user into the local User DB
            return $user_id;
         }
         else
         {
            $this->logger->logit(mysql_error(),LOG_ERROR);
            return false;
         }
      }
      else 
      {
         return $user_id;
      }
   }

   /**
   * Override default insertIfUnknown function.
   * Try to insert an SMS device. If the device is not an SMS device, call
   * parent insertIfUnknown method to perform a normal insert
   * @return boolean		True if an insert operation was performed, false otherwise
   */
   public function insertIfUnknown($vlan_to_assign = 0)
   {
      if ($this->check_calling_method() && !$this->inDB() && $this->port_id)
      {
         #Enterprise only
         if ($this->conf->lastseen_sms)
         {
            $query="SELECT * FROM nac_sms_1 WHERE MACAddress='{$this->mac}' LIMIT 1";
            $this->logger->debug($query,3);
            $res=mysql_query($query);
            if (!$res)
            {
               $this->logger->logit(mysql_error(),LOG_ERROR);
               return false;
            } 
            if (mysql_num_rows($res)==0)
            {
               #This system is not in SMS, then do a normal insert
               return parent::insertIfUnknown();
            }           
            $row=mysql_fetch_array($res);
            # Enable PC and set to SMS VLAN
            if ($row)
            {
               $uid=$this->get_insert_user_id($row['Username']);

               if (!$uid)
                  $uid=0;
               if ($this->conf->lastseen_sms_vlan)
                  $vlan_id=$this->conf->lastseen_sms_vlan;
               else
                  $vlan_id=0;
               $query="INSERT INTO systems "
                    . "SET LastSeen=NOW(), status=1, class=2, "	#Active, GWP
                    . "description='{$row['Username']}', "
                    . "uid='$uid', "
                    . "name='{$row['ComputerName']}', "
                    . "comment='', "
                    . "vlan='$vlan_id', "
                    . "os4='{$row['OS']}', "
                    . "lastport='{$this->port_id}', "
                    . "office='{$this->office_id}', "
                    . "mac='{$this->mac}';";
               $this->logger->debug($query,3);
               $res = mysql_query($query);
               if ($res)
               {
                  # Document the user's details in the alert
                  $query="SELECT CONCAT(Givenname,' ',Surname,' ',Department,' ',Mobile) FROM users WHERE username='{$row['Username']}'";
                  $this->logger->debug($query,3);
                  $res = mysql_query($query);
                  if ($res)
                  {
                     list($sms_details)=mysql_fetch_array($res);
                     #$subject="NAC alert in ".$this->patch_info.", $sms_details, port ".$this->port_info."\n";
                     #$mesg="New ".$this->conf->sms_device." ".$this->mac."(".$this->getVendor().") ".$row['ComputerName'].", ".$row['Username'].", $sms_details, switch ".$this->switch_info."\n";
                     $subject="NAC alert in {$this->alert_subject}";
                     $mesg="New {$this->conf->sms_device} {$this->mac}({$row['ComputerName']} - {$row['Username']}), {$this->alert_message}";
                     $this->logger->debug($subject,2);
                     #$this->logger->logit($mesg);
                     if (($this->notify) && (strcasecmp($this->notify,'null')!=0))
                        $this->logger->mailit($subject, $mesg, $this->notify);
                     $this->logger->mailit($subject, $mesg);
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
               $this->logger->logit(mysql_error(),LOG_ERROR);
               return false;
            }
         }
         #Device is not an SMS device or option not enabled, perform normal insert
         return parent::insertIfUnknown($vlan_to_assign);
      }
   }

}
?>
