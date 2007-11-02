#!/opt/php5/bin/php
<?php
/**
 * enterprise/epo_sync
 *
 * Long description for file:
 * Test McAfee EPO SQL connection
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Sean Boran (FreeNAC Core Team)
 * @copyright                   2006 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     CVS: $Id:$
 * @link                        http://www.freenac.net
 *
 */


# Php weirdness: change to script dir, then look for includes
chdir(dirname(__FILE__));
set_include_path("../:./");
require_once "./funcs.inc.php";               # Load settings & common functions

db_connect();      # init MySql 

// init MS-SQL

$msconnect = mssql_connect($conf->epo_dbalias, $epo_dbuser, $epo_dbpass);
if (! $msconnect ) {
  $logger->logit( "Cannot connect to DB server " . mssql_get_last_message());
  return;
}

$d = mssql_select_db($conf->epo_db, $msconnect) or die("Couldn't open database ".$conf->epo_db." " .mssql_get_last_message());


#$query="SELECT name FROM sysobjects WHERE xtype = 'u'";
#$query="select TOP 5 * from ComputerProperties ";
#$query="SELECT TOP 5 ParentID, ComputerName, IPHostName, DomainName, IPAddress, OSType, OSVersion, OSServicePackVer, NetAddress, UserName, TheTimestamp, TheHiddenTimestamp, Description  FROM ComputerProperties";
$query="SELECT       ParentID, ComputerName, IPHostName, DomainName, IPAddress, OSType, OSVersion, OSServicePackVer, NetAddress, UserName, TheTimestamp, TheHiddenTimestamp, Description  FROM ComputerProperties";

#$logger->logit( "$query\n");

$r= mssql_fetch_all($query);
foreach ($r as $row) { 
  #$logger->logit( "Sync $row[ComputerName], $row[IPAddress]\n"); 

  $mac=$row[NetAddress];    
  $sep='.';
  $mac="$mac[0]$mac[1]$mac[2]$mac[3]$sep$mac[4]$mac[5]$mac[6]$mac[7]$sep$mac[8]$mac[9]$mac[10]$mac[11]";    # Add '.' every three digits
  $sid=v_sql_1_select("select id from systems where mac='$mac'"); 
  if ($sid)
  {
   // Use REPLACE to either update or insert a new record
     $query2="REPLACE EpoComputerProperties                     "
      . "SET ParentID='$row[ParentID]'"
      . ", sid = '$sid'"
      #. ", ParentID='$row[ParentID]'"
      . ", ComputerName=LOWER('$row[ComputerName]')"
      . ", DomainName=LOWER('$row[DomainName]')"
      . ", IPAddress='$row[IPAddress]'"
      . ", OSType='$row[OSType]'"
      . ", OSVersion='$row[OSVersion]'"
      . ", OSServicePackVer='$row[OSServicePackVer]'"
      . ", OSBuildNum='$row[OSBuildNum]'"
      . ", NetAddress=LOWER('$mac')"
      . ", UserName=LOWER('$row[UserName]')"
      . ", IPHostName=LOWER('$row[IPHostName]')"
      . ", TheTimestamp='".$row[TheTimestamp]."'";
      #. " WHERE sid=$sid";
      #. " WHERE ParentID='$row[ParentID]'";
      #$logger->logit($query2."\n");
      $res = mysql_query($query2) OR die("Error in UPDATE DB-Query: " . mysql_error());

      #$query2="UPDATE EpoComputerProperties SET LastUpdate=NOW() WHERE ParentID='$row[ParentID]' ";
      #    $res = mysql_query($query2) OR die("Error in UPDATE DB-Query: " . mysql_error());

  // For each mac address, lookup the latest DAT version & timestamp, and store it too.
  //
      $query3="SELECT distinct top 1 AgentVersion, LastUpdate, DATVersion, ComputerName, IPHostName,  NetAddress "
      . " from LeafNode Left Outer Join ComputerProperties on (LeafNode.AutoID = ComputerProperties.ParentID) Left Outer Join Events on (LeafNode.AutoID = Events.NodeID) "
      . " WHERE NetAddress='$row[NetAddress]' ORDER BY DATVersion  DESC";
      #$logger->logit( $query3);
      $r3= mssql_fetch_all($query3);
      foreach ($r3 as $row3) { 
            logit("Update AV status $row[NetAddress] $mac $row3[LastUpdate], $row3[DATVersion], $row3[ComputerName]\n"); 
         
            // now store that AV info
            $query4="UPDATE EpoComputerProperties SET LastDATUpdate='$row3[LastUpdate]', AgentVersion='$row3[AgentVersion]', DATVersion='$row3[DATVersion]' WHERE sid='$sid'";
            #$logger->logit($query4. "\n");
            $res = mysql_query($query4) OR die("Error in UPDATE DB-Query: " . mysql_error());
      }

   }

}



?>
