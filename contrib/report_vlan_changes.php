#!/usr/bin/php
<?php
/**
 *
 * Long description for file:
 *
 * Report systems that have changed their assigned vlan in the last 24 hours. 
 * Also report systems which have gotten access to the network on a different vlan to the one they normally use
 * in the last 24 hours.
 * It grabs the information to compare against from two different sources. In this first version one source is a text file,
 * which contains a dump of systems last seen in the last 24 hours, which is then compared againt the second source of data,
 * the database. In further revisions of this script this behavior will change and both of the sources will be databases. 
 * For that to work, the opennac database has to be mirrored every 24 hours in order to reflect system changes.
 *
 * The way it detects if a systems has changed the vlan assigned to it, is by comparing the vlan field on both sources. 
 * If the vlan assigned has changed, it lookups in the guilog table to see if there is an entry reporting the change.
 * For the final report, it shows also the Time when that records changed and also the user who made the change if such
 * information is available.
 *
 * It also reports systems which have been assigned to a vlan which is not the one they normally use. This is achieved
 * by comparing the fields lastvlan and vlan only in the most recent data source. If such fields don't match, it shows 
 * the Time when that records changed and also the user who made the change if such information is available.
 * 
 * 
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Héctor Ortiz (FreeNAC Core Team)
 * @copyright                   2008 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
 * @link                                http://www.freenac.net
 *
 */
chdir(dirname(__FILE__));
set_include_path("./:../");
require_once "../bin/funcs.inc.php";
chdir(dirname(__FILE__));

$logger->setDebugLevel(0);
$logger->setLogToStdOut(true);

$file_to_read = "vlan_changes.txt";

$systems_read = array();
$counter = 0;

#Read file and build array containing previous information
$logger->debug("Trying to read previous information saved in file $file_to_read");
if ( file_exists($file_to_read) )
{
   $lines = file($file_to_read);
   if ( $lines !== false )
   {
      foreach ($lines as $line)
      {
         $line_array = explode(',', $line);
         $systems_read[$counter]['id'] = trim($line_array[0]);
         $systems_read[$counter]['mac'] = trim($line_array[1]);
         $systems_read[$counter]['name'] = trim($line_array[2]);
         $systems_read[$counter]['changeuser'] = trim($line_array[3]);
         $systems_read[$counter]['vlan'] = trim($line_array[4]);
         $systems_read[$counter]['lastvlan'] = trim($line_array[5]);
         $systems_read[$counter]['changedate'] = trim($line_array[6]);
         $systems_read[$counter]['lastseen'] = trim($line_array[7]);
         $counter++;
      }
      $logger->debug("Information from file successfully read");
   }
}
else
{
   $logger->debug("File $file_to_read not found.");
}

if ( count($systems_read) > 0 )
{
   $logger->debug("ARRAY CONTAINING SYSTEMS INFORMATION READ FROM TEXT FILE\n", 3);
   $logger->debug(print_r($systems_read, true), 3);
}

## Get data to compare from the db
$query =<<<EOF
SELECT s.id, s.mac, s.name, u.username as changeuser,
       s.vlan,s.lastvlan,s.changedate,s.lastseen, p.last_auth_profile 
       FROM systems s INNER JOIN port p ON s.lastport=p.id
       LEFT JOIN users u ON s.ChangeUser=u.id 
       WHERE s.lastseen IS NOT NULL AND (s.status='1' or s.status='3')
       AND DATE_SUB(CURDATE(), INTERVAL 1 DAY) <= s.LastSeen;
EOF;
$logger->debug($query, 3);
$res = mysql_query($query);

$systems = array();
$logger->debug("Reading data to compare from database");
if (!$res)
{
   $logger->logit(mysql_error(), LOG_ERR);
   exit(1);
}
else
{
   while ( $row = mysql_fetch_array($res, MYSQL_ASSOC) )
   {
      $systems[] = $row;      
   }
   $logger->debug("Data from database sucessfully read");
}

if ( count($systems) > 0 )
{
   $logger->debug("ARRAY CONTAINING SYSTEMS INFORMATION READ FROM DATABASE\n", 3);
   $logger->debug(print_r($systems,true), 3);
}

## Report when a system has been placed in a different vlan to the one assigned to it
$lastvlan_differs_from_vlan = array();
$now = date('Y-m-d H:i:s');

foreach ( $systems as $system )
{
   ## For systems that have been placed in a different vlan than the one they normally use,
   ## report only systems that are only on dynamic ports, since they will appear in the logs
   ## and that have been placed in that vlan in the last hour
   if ( ( $system['last_auth_profile'] == 2 ) && ( $system['lastvlan'] != $system['vlan'] ) && 
	( $now !== false ) && (time_diff($system['lastseen'],$now)<=3600) )
   {
      $lastvlan_differs_from_vlan[] = $system;
   }
}

if ( count($lastvlan_differs_from_vlan) > 0 )
{
   $logger->debug("There are some systems whose lastvlan differs from their assigned vlan");
   $logger->debug("LASTVLAN != VLAN\n", 3);
   $logger->debug(print_r($lastvlan_differs_from_vlan, true), 3);
}

##Report systems whose vlan has changed
$vlan_changed = array();
$counter = 0;

if ( is_array($systems_read) )
{
   ## Iterate through the information contained in the text file
   foreach ( $systems_read as $system_read )
   {
      ## And compare it with the information contained in the database
      foreach ( $systems as $system )
      {
         ## If in both data sets
         if (strcasecmp($system['mac'], $system_read['mac']) === 0)
         {
            ## The assigned vlan has changed
            if ( $system['vlan'] != $system_read['vlan'] )
            {
               ## Report it
               $vlan_changed[$counter]['id'] = $system['id'];
               $vlan_changed[$counter]['mac'] = $system['mac'];
               $vlan_changed[$counter]['name'] = $system['name'];
               $vlan_changed[$counter]['changeuser'] = $system['changeuser'];
               $vlan_changed[$counter]['vlan'] = $system['vlan'];
               $vlan_changed[$counter]['lastvlan'] = $system['lastvlan'];
               $vlan_changed[$counter]['changedate'] = $system['changedate'];
               $vlan_changed[$counter]['lastseen'] = $system['lastseen'];
               $vlan_changed[$counter]['previous_vlan'] = $system_read['vlan'];
               $counter++;
            }
            break;
         }
      }
   }
}

if ( (count($vlan_changed)>0) || (count($lastvlan_differs_from_vlan)>0) )
{
   #Lookup vlan names
   $logger->debug("Retrieving vlan names to generate report");
   $vlan_names = NULL;
   $query = "SELECT id, default_name FROM vlan";
   $res = mysql_query($query);
   if ( ! $res )
   {
      $logger->logit(mysql_error(), LOG_ERR);
   }
   else
   {
      while ( $row = mysql_fetch_array($res, MYSQL_ASSOC) )
      {
         $vlan_names[$row['id']] = $row['default_name'];
      }
   }

   if ( count($vlan_names) > 0 )
   {
      $logger->debug("Vlan names successfully retrieved");
      $logger->debug("VLANS READ FROM DATABASE\n", 3);
      $logger->debug(print_r($vlan_names, true), 3);
   }
}

$report_vlan_changes = NULL;

## Create list of systems whose vlan has changed within the last 24 hours
if ($counter > 0)
{
   $logger->debug("There are some systems whose assigned vlan has changed. Generating report");
   $logger->debug("ARRAY CONTAINING INFORMATION OF SYSTEM WHOSE ASSIGNED VLAN HAS CHANGED\n", 3);
   $logger->debug(print_r($vlan_changed, true), 3);

   ##Grab the guilog data for the last 24 hours
   $query = "SELECT what FROM guilog WHERE DATE_SUB(CURDATE(), INTERVAL 1 DAY) <= datetime ORDER BY id DESC;";
   $logger->debug($query, 3);
   $res = mysql_query($query);

   $events = array();

   if (!$res)
   {
      $logger->logit(mysql_error(), LOG_ERR);
   }
   else
   {
      while ( $row = mysql_fetch_array($res, MYSQL_ASSOC) )
      {
         $events[] = $row;
      }
   }

   $report_vlan_changes = "The following systems have changed the vlan assigned to them in the last 24 hours\n\n";
   
   foreach ( $vlan_changed as $system )
   {
      ##Transform the vlan id in vlan name
      if ( is_array($vlan_names) )
      {
         if ( ! empty($vlan_names[$system['previous_vlan']]) )
            $previous_vlan = $vlan_names[$system['previous_vlan']];
         else
            $previous_vlan = $system['previous_vlan'];
         if ( ! empty($vlan_names[$system['vlan']]) )
            $vlan = $vlan_names[$system['vlan']];
         else
            $vlan = $system['vlan'];
         ## Add to the report the changed system's information with vlan names
         $report_vlan_changes .= "System {$system['name']} ({$system['mac']}), last seen on {$system['lastseen']} has changed from vlan $previous_vlan to vlan $vlan.\n";
      }
      else
         ##  Add to the report the changed system's information with vlan ids
         $report_vlan_changes .= "System {$system['name']} ({$system['mac']}), last seen on {$system['lastseen']} has changed from vlan {$system['previous_vlan']} to vlan {$system['vlan']}.\n";
      
      ## If we have Change information, add it to the report
      if ( ! empty($system['changedate']) )
         $report_vlan_changes .= "Change date: {$system['changedate']}.\n";
      if ( ! empty($system['changeuser']) )
         $report_vlan_changes .= "Change user: {$system['changeuser']}.\n";
      
      ##Is there something in the guilog regarding this system?
      if ( count($events) > 0 )
      {
         foreach ( $events as $event )
         {
            if ( stripos($event['what'], $system['mac']) !== false )
            {
               ##Then report it as well
               $report_vlan_changes .= " Entry from guilog: {$event['what']}.\n";         
               break;
            }
         }
      }
      $report_vlan_changes .= "\n";
   }
   $report_vlan_changes .= "\n\n";
}

if ( count($lastvlan_differs_from_vlan) > 0 )
{
   $report_vlan_changes .= "In the last 24 hours, the following systems have been placed in a vlan which is different from the one assigned to them (lastvlan != vlan).\n\n";
   foreach ( $lastvlan_differs_from_vlan as $system )
   {
      ##Transform the vlan id in vlan name
      if ( is_array($vlan_names) )
      {
         if ( ! empty($vlan_names[$system['lastvlan']]) ) 
            $lastvlan_name = $vlan_names[$system['lastvlan']];
         else
            $lastvlan_name = $system['lastvlan'];
         if ( ! empty($vlan_names[$system['vlan']]) )
            $vlan_name = $vlan_names[$system['vlan']];
         else
            $vlan_name = $system['vlan'];   
          ## Add to the report the changed system's information with vlan names 
         $report_vlan_changes .= "System {$system['name']} ({$system['mac']}), last seen on {$system['lastseen']} has been placed in vlan $lastvlan_name instead of $vlan_name.\n";
      }
      else
          ## Add to the report the changed system's information with vlan ids
         $report_vlan_changes .= "System {$system['name']} ({$system['mac']}), last seen on {$system['lastseen']} has been placed in vlan {$system['lastvlan']} instead of vlan {$system['vlan']}.\n";

      ## If we have Change information, add it to the report
      if ( ! empty($system['changedate']) )
         $report_vlan_changes .= "Change date: {$system['changedate']}.\n";
      if ( ! empty($system['changeuser']) )
         $report_vlan_changes .= "Change user: {$system['changeuser']}.\n";
      $report_vlan_changes .= "\n";
   }
}

### Save data to a file for further comparisons
$logger->debug("Writing data to file $file_to_read");
$file = fopen($file_to_read, 'w');
if ( $file )
{
   foreach ($systems as $system)
   {
     fprintf($file,"%s,%s,%s,%s,%s,%s,%s,%s\n",$system['id'],$system['mac'],$system['name'],$system['changeuser'],$system['vlan'],$system['lastvlan'],$system['changedate'], $system['lastseen']);
   }
   fclose($file);
}
else
   $logger->logit("Error while trying to write data to file", LOG_ERR);

if ( strlen($report_vlan_changes) > 0 )
{
   $logger->debug("Sending report");
   $logger->mailit('VLAN changes as reported by report_vlan_changes.php', $report_vlan_changes);
}
?>
