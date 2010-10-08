#!/usr/bin/php
<?php
#
# delete_old_users.php
# PURPOSE: See "Explain usage" below and $conf->delete_users_threshold
####

require_once('funcs.inc.php');

# configure logging
$logger->setDebugLevel(0);          // level 0-3
$logger->setLogToStdOut(false);

if ( ! $conf->delete_users_threshold )
# Module not activated or invalid value
   exit(1);

db_connect();


## Find out what users haven't been seen during the time period defined
$query = "SELECT * FROM users WHERE DATE_SUB(CURDATE(), INTERVAL {$conf->delete_users_threshold} DAY) "
       . " >= LastSeenDirectory AND LastSeenDirectory != '0000-00-00';";
$logger->debug($query, 3);
$res = mysql_query($query);
##Log and abort if there is an error in our query
if ( ! $res )
{
   $logger->logit(mysql_error(), LOG_ERR);
   exit(1);
}

# There are no entries in users table
if ( mysql_num_rows($res) == 0)
{
   exit(0);
}
#Array to hold the systems assigned to those users
$assigned_systems = array();
$systems_to_delete = array();
$deleted_users = array();

#Counter for assigned systems;
$assigned = 0;
$to_delete = 0;
$timestamp=date('Y-m-d H:i:s');


while ( $result = mysql_fetch_assoc($res) )
{
   #Let's find out if they have systems assigned
   $query = "SELECT s.id, s.mac, s.name, s.LastPort, s.LastSeen, v.default_name as vlan FROM systems s INNER JOIN vlan v ON s.vlan=v.id WHERE uid = {$result['id']};";
   $logger->debug($query, 3);
   $res_temp = mysql_query($query);
   if ( ! $res_temp )
   {
      $logger->logit(mysql_error(), LOG_ERR);
      continue;
   }
   ## Systems found, store them in our systems array for later reporting
   if ( mysql_num_rows($res_temp) >= 1 )
   {
      $row = mysql_fetch_assoc($res_temp);
      // Present only systems which have been online during $conf->delete_users_threshold and delete older systems
      $days_diff = (int)((time_diff($row['LastSeen'],$timestamp)/3600)/24);
      if ( $days_diff == false )
         continue;
      if ( $days_diff > $conf->delete_users_threshold )
      {
         $systems_to_delete[$to_delete] = $row;
         $systems_to_delete[$to_delete]['user'] = $result['username'];
         $systems_to_delete[$to_delete]['days_diff'] = $days_diff;
         $to_delete++;
      } 
      else
      {
         $assigned_systems[$assigned] = $row;
         $assigned_systems[$assigned]['user'] = $result['username'];
         $assigned++;    
      }
   }
   else
   {
      # No systems found, delete user from users table
      $query = "DELETE FROM users WHERE id='{$result['id']}';";
      $logger->debug($query, 3);
      $final_res = mysql_query($query);
      if ( ! $final_res )
      {
         $logger->logit(mysql_error(), LOG_ERR);
      }
      else
      {
         $deleted_users[] = $result;
      }
   }
}

# Create subject of email
if ( $assigned > 0 )
   $subject = "Expired users with End-devices assigned to them";
else
   $subject = "Expired users deleted";

# Create body of message
if ( $assigned > 0 )
{
## Explain usage
   $message =  "delete_old_users.php: find users not seen for $conf->delete_users_threshold days and delete them. If they have systems assigned, report them and actually delete systems not online in that period\n\n\n\n";

   $message .= "Some users haven't been seen in the central directory for more than {$conf->delete_users_threshold} days and have systems assigned. ";
   $message .= "These systems need to be reassigned to someone else: \n\n";
   for ($i = 0; $i < $assigned; $i++)
   {
      $row = '';
      $query = "SELECT p.name AS port, s.name AS switch_name, s.ip AS switch_ip FROM port p INNER JOIN switch s ON p.switch=s.id WHERE p.id='{$assigned_systems[$i]['LastPort']}';";
      $logger->debug($query, 3);
      $res = mysql_query($query);
      if ( ! $res )
      {
         $logger->logit(mysql_error(), LOG_ERR);
      }
      else
         $row = mysql_fetch_array($res, MYSQL_ASSOC);
      $message .= "{$assigned_systems[$i]['mac']}({$assigned_systems[$i]['name']}), last seen {$assigned_systems[$i]['LastSeen']} on port {$row['port']} on switch {$row['switch_name']}({$row['switch_ip']}) is assigned to {$assigned_systems[$i]['user']}\n\n";
   }
   $message .= "\n\n";
}

if ( ! isset($message) )
	$message = '';

if ( $to_delete > 0 )
{
   $message .= "The following SYSTEMS HAVE BEEN DELETED because they haven't been online during this time period. \n\n";
   for ( $i = 0; $i < $to_delete; $i++)
   {
      $message .= "{$systems_to_delete[$i]['mac']}({$systems_to_delete[$i]['name']}), last seen on {$systems_to_delete[$i]['LastSeen']} and assigned to vlan {$systems_to_delete[$i]['vlan']} and to user {$systems_to_delete[$i]['user']} will be deleted\n\n";  

      cascade_delete($systems_to_delete[$i]['mac']);         // actually delete!
   }
   $message .= "\n\n";

}

#Report deleted users
$message .= "Following users have been DELETED from the central directory:\n\n";
for ($i = 0; $i < count($deleted_users); $i++)
{
   $message .= "Username: {$deleted_users[$i]['username']}\n";
   $message .= "Name: {$deleted_users[$i]['GivenName']} {$deleted_users[$i]['Surname']}\n";
   $message .= "E-mail: {$deleted_users[$i]['rfc822mailbox']} \nDepartment: {$deleted_users[$i]['Department']}\n";
   $message .= "Telephone: {$deleted_users[$i]['TelephoneNumber']} Mobile: {$deleted_users[$i]['Mobile']}\n\n";
}

$logger->debug("Assigned systems", 1);
$logger->debug(print_r($assigned_systems,true), 1);

$logger->debug("Systems to delete", 1);
$logger->debug(print_r($systems_to_delete, true), 1);

$logger->debug("Deleted users",1);
$logger->debug(print_r($deleted_users, true), 1);

$logger->mailit($subject, $message);

?>
