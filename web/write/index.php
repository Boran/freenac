<?php

/**
 * index.php
 *
 * Long description for file:
 * Simple FreeNAC browser.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package             FreeNAC
 * @author              Patrick Bizeau
 * @copyright   2006 FreeNAC
 * @license             http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version             SVN: $Id$
 * @link                http://www.freenac.net
 *
 */


///////////////////////////////////////////
//     DO NOT EDIT BELOW THIS LINE       //
///////////////////////////////////////////
chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');
// include pear module (if activated in config)
if ($xls_output){
        require_once "Spreadsheet/Excel/Writer.php";
}

function page()
{
   global $dbhost, $dbuser, $dbname, $dbpass;
   //session setup
   session_name('FreeNAC');
   session_start();

   // if not already set, set the $_SESSION vars
   if (!isset($_SESSION['name'])){
        $_SESSION['name']=$unknown;
        $_SESSION['mac']='';
        $_SESSION['username']='';
   }

   // validate webinput
   $_GET=array_map('validate_webinput',$_GET);
   $_POST=array_map('validate_webinput',$_POST);
   $_COOKIE=array_map('validate_webinput',$_COOKIE);


   // setup db connectivity
   $dblink=mysql_connect($dbhost, $dbuser, $dbpass)
        or die('DB Error. Unable to connect: ' . mysql_error());
   // select database
   mysql_select_db($dbname,$dblink)
        or die('Could not select database '.$dbname);

   // handle search requests
   if ($_REQUEST['action']=='search'){
        // clear
        if ($_REQUEST['submit']=='Clear'){
                $_SESSION['name']=$unknown;
                $_SESSION['mac']='';
                $_SESSION['vlan']='';
                $_SESSION['username']='';
                $_SESSION['switch']='';
                $_SESSION['ip']='';
        }
        if ($_REQUEST['submit']=='Submit'){
                $_SESSION['name']=$_REQUEST['name'];
                $_SESSION['mac']=$_REQUEST['mac'];
                $_SESSION['vlan']=$_REQUEST['vlan'];
                $_SESSION['username']=$_REQUEST['username'];
                $_SESSION['switch']=$_REQUEST['switch'];
                $_SESSION['ip']=$_REQUEST['ip'];
        }
   }

   // if the ouput is a xls file we need to do it now (before returning anything to the browser... header issue)
   if ($_REQUEST['action']=='xls' && $_REQUEST['type']!=''){
        // sql query
        $sql='SELECT sys.id, sys.name as Systemname, sys.mac, sys.vlan, vlan.default_name as VlanName, lvlan.default_name as LastVlan, vlan.vlan_group as VlanGroup, status.value as Status, usr.username as Username, sys.inventory, sys.description, sys.comment, sys.changedate, cusr.username as ChangeUser, sys.lastseen, b.name as building, loc.name as office, p.name as port, pcloc.name as PortLocation, p.comment as PortComment, p.last_activity as PortLastActivity, swi.ip as SwitchIP, swi.name as Switch, swloc.name as SwitchLocation, pc.outlet as PatchCableOutlet, pc.comment as PatchCableComment, sys.history, usr.surname, usr.givenname, usr.department, usr.rfc822mailbox as EMail, usrloc.name as UserLocation, usr.telephonenumber as UserTelephone, usr.mobile, usr.lastseendirectory as UserLastSeenDirectory, sos.value as OSName, sos1.value as OS1, sos2.value as OS2, sos3.value as OS3, sys.os4 as OS4, sys.class, sclass.value as ClassName, sys.class2, sclass2.value as ClassName2, sys.scannow, sys.r_ip, sys.r_timestamp, sys.r_ping_timestamp
                FROM systems as sys LEFT JOIN vlan as vlan ON vlan.id=sys.vlan
                        LEFT JOIN vlan as lvlan ON lvlan.id=sys.lastvlan
                        LEFT JOIN vstatus as status ON status.id=status
                        LEFT JOIN users as usr ON usr.id=sys.uid
                        LEFT JOIN users as cusr ON cusr.id=sys.changeuser
                        LEFT JOIN location as loc ON loc.id=sys.office
                        LEFT JOIN building as b ON b.id=loc.building_id
                        LEFT JOIN port as p ON p.id=sys.lastport
                        LEFT JOIN patchcable as pc ON pc.port=p.id
                        LEFT JOIN location as pcloc ON pcloc.id=pc.office
                        LEFT JOIN switch as swi ON swi.id=p.switch
                        LEFT JOIN location as swloc ON swloc.id=swi.location
                        LEFT JOIN location as usrloc ON usrloc.id=usr.location
                        LEFT JOIN sys_os as sos ON sos.id=sys.os
                        LEFT JOIN sys_os1 as sos1 ON sos1.id=sys.os1
                        LEFT JOIN sys_os2 as sos2 ON sos2.id=sys.os2
                        LEFT JOIN sys_os3 as sos3 ON sos3.id=sys.os3
                        LEFT JOIN sys_class as sclass ON sclass.id=sys.class
                        LEFT JOIN sys_class2 as sclass2 ON sclass2.id=sys.class2';
        // not seen in the last 12 month?
        if ($_REQUEST['type']=='12plus'){
                $sql.=' WHERE LastSeen < (NOW() - INTERVAL 1 YEAR) ORDER BY LastSeen DESC;';
        }
        // query database
        $result=mysql_query($sql) or die('Query failed: ' . mysql_error());
        // Nothing found.
        if (mysql_num_rows($result)<1){
                die('Empty Data Set.');
        }
        // Found something
        else {
                // put the results in a neat excel file and send it to the browser
                create_xls($result);
                // work done, exit gracefully
                exit(0);
        }
   }

   // print the page header; so the user knows there's (much) more to come
   echo print_header($entityname, $xls_output);
   // let's find out what we're supposed to do
   // edit the properties of a given system
   $remote_host=validate_webinput($_SERVER['REMOTE_ADDR']);
   if (empty($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']))
      $uname='1';
   else
   {
      $temp=explode('@',$_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
      $temp=validate_webinput($temp[0]);
      $res=mysql_query("select id from users where username like '%$temp%'");
      if (mysql_num_rows($res)!=1)
         $uname='1';
      else
      {
         $row=mysql_fetch_array($res);
         $uname=$row['id'];
      }
   }
   if ($_REQUEST['action']=='edit'){
        // check that what we got is a number
        if (is_numeric($_REQUEST['id'])){
                $sql=' SELECT sys.id, sys.name, sys.mac, sys.status, sys.vlan, lvlan.default_name as lastvlan, sys.uid as user, sys.office, port.name as port, sys.lastseen, swloc.name as location, swi.name as switch, sys.r_ip as lastip, sys.r_timestamp as lastipseen, sys.comment, eth.vendor
                        FROM systems as sys LEFT JOIN vlan as lvlan ON sys.lastvlan=lvlan.id LEFT JOIN port as port ON port.id=sys.lastport LEFT JOIN switch as swi ON port.switch=swi.id LEFT JOIN location as swloc ON swloc.id=swi.location LEFT JOIN ethernet as eth ON (SUBSTR(sys.mac,1,4)=SUBSTR(eth.mac,1,4) AND SUBSTR(sys.mac,6,2)=SUBSTR(eth.mac,5,2))
                        WHERE sys.id=\''.$_REQUEST['id'].'\';';
                $result=mysql_query($sql) or die('Query failed: ' . mysql_error());
        }
        // Nothing or too much found.
        if (mysql_num_rows($result)!=1){
                echo ' Search returned '.mysql_num_rows($result).' rows.';
        }
        // Found something
        else {
                $row=mysql_fetch_array($result);
                echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
                echo '<table width="1000" border="0">'."\n";
                // Name
                echo '<tr><td width="87">Name:</td><td width="400">'."\n";
                echo '<input name="name" type="text" value="'.stripslashes($row['name']).'"/>'."\n";
                echo '</td></tr>'."\n";
                // MAC
                echo '<tr><td>MAC:</td><td>'."\n";
                echo $row['mac'].(!is_null($row['vendor'])?' ('.$row['vendor'].')':'')."\n";
                echo '</td></tr>'."\n";
                echo '<input type="hidden" name="mac" value="'.$row['mac'].'" />'."\n";
                // Status
                echo '<tr><td>Status:</td><td>'."\n";
                echo get_status($row['status']);
                echo '</td></tr>'."\n";
                // VLAN
                echo '<tr><td>VLAN:</td><td>'."\n";
                echo '<select name="vlan">';
                $sql='SELECT id, default_name as value FROM vlan ORDER BY value;'; // Get details for all vlans
                $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
                if (mysql_num_rows($res)>0){
                        while ($r=mysql_fetch_array($res)){
                                echo '<option value="'.$r['id'].'" '.($r['id']==$row['vlan']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
                        }
                }
                echo '</select></td></tr>'."\n";
                // LastVLAN
                echo '<tr><td>LastVLAN:</td><td>'."\n";
                echo (is_null($row['lastvlan'])?'NONE':$row['lastvlan'])."\n";
                echo '</td></tr>'."\n";
                // User
                echo '<tr><td>User:</td><td>'."\n";
                echo get_userdropdown($row['user']);
                echo '</td></tr>'."\n";
                // Office
                echo '<tr><td>Office:</td><td>'."\n";
                echo get_officedropdown($row['office']);
                echo '</td></tr>'."\n";
                // Switch
                echo '<tr><td>Switch:</td><td>'."\n";
                echo $row['switch'].' -- '.$row['port'].' -- '.$row['location']."\n";
                echo '</td></tr>'."\n";
                // LastIP / LastIPseen
                echo '<tr><td>LastIP:</td><td>'."\n";
                echo (is_null($row['lastip'])?'NONE':$row['lastip'])."\n";
                echo ' -- ';
                echo (is_null($row['lastipseen'])?'NEVER':$row['lastipseen'])."\n";
                echo '</td></tr>'."\n";
                // LastSeen
                echo '<tr><td>LastSeen:</td><td>'."\n";
                echo (is_null($row['lastseen'])?'NEVER':$row['lastseen'])."\n";
                echo '</td></tr>'."\n";
                // Comment
                echo '<tr><td>Comment:</td><td>'."\n";
                echo '<input name="comment" type="text" value="'.stripslashes($row['comment']).'"/>'."\n";
                echo '</td></tr>'."\n";

                // Submit
                echo '<tr><td>&nbsp;</td><td>'."\n";
                echo '<input type="submit" name="action" value="update" />'.'&nbsp;'.'<input type="submit" name="action" value="delete" />'."\n";
                echo '</td></tr>'."\n";
                echo '</table><!input type="hidden" name="action" value="update" /><input type="hidden" name="id" value="'.$row['id'].'" /></form>';
        }

   }
   // parse request and update database
   else if ($_REQUEST['action']=='update' && is_numeric($_REQUEST['id'])
                        && is_numeric($_REQUEST['status']) && is_numeric($_REQUEST['vlan'])
                        && is_numeric($_REQUEST['username']) && $_REQUEST['name']!='') {
        // make sure we got a matching systems, a vlan with this number and a useraccount
        $sql='SELECT port.id, port.name as port, swi.name as switch, users.username, vlan.id as vlan
                FROM systems as sys LEFT JOIN port as port ON port.id=sys.lastport LEFT JOIN switch as swi ON port.switch=swi.id, vlan, users
                WHERE sys.id='.$_REQUEST['id'].' AND vlan.id='.$_REQUEST['vlan'].' AND users.id=\''.$_REQUEST['username'].'\';';
        $result=mysql_query($sql) or die('Query failed: ' . mysql_error());
        if (mysql_num_rows($result)!=1){
                echo 'System, VLAN or User missmatch.';
        }
        // Got it, prepare statment and insert changes into DB
        else {
                $row=mysql_fetch_array($result);
                $sql='UPDATE systems SET ';
                // got name?
                $sql.=($_REQUEST['name']!=''?'name=\''.$_REQUEST['name'].'\', ':'');
                // status, vlan
                $sql.='status='.$_REQUEST['status'].', vlan='.$_REQUEST['vlan'];
                // username
                $sql.=($_REQUEST['username']!=''?', uid='.$_REQUEST['username'].' ':'');
                // got office?
                $sql.=($_REQUEST['office']!=''?', office='.$_REQUEST['office'].'':'');
                // got comment?
                $sql.=($_REQUEST['comment']!=''?', comment=\''.$_REQUEST['comment'].'\'':'');
                // set what we know for sure (changedate, changeuser,...)
                $sql.=', changedate=NOW(), changeuser=\'WEBGUI\'';
                // where?
                $sql.=' WHERE id=\''.$_REQUEST['id'].'\';';
                // update the given data set
                mysql_query($sql) or die('Query failed: ' . mysql_error());
                // Update OK
                // log what we have done
                $sql="INSERT INTO guilog (who, host, datetime, priority, what) VALUES ('$uname','$remote_host',NOW(),'info','Updated system: ".$_REQUEST['name'].', '.$_REQUEST['mac'].', WEBGUI, '.$_REQUEST['comment'].', '.$_REQUEST['office'].', '.$row['port'].', '.$row['switch'].', vlan'.$_REQUEST['vlan'].'\');';
                mysql_query($sql) or die('Query failed: ' . mysql_error());
                // Update successful
                echo '<br />Update successful.<br />';
                // Ask the user if he want's to restart the associated port
                echo '<br />To restart Port '.$row['port'].' on Switch '.$row['switch'].' click <a href="'.$_SERVER['PHP_SELF'].'?action=restartport&port='.$row['id'].'">here</a>.';
        }
   }
   // parse request and delete record
   else if ($_REQUEST['action']=='delete' && is_numeric($_REQUEST['id'])
			&& is_numeric($_REQUEST['status']) && is_numeric($_REQUEST['vlan'])
			&& is_numeric($_REQUEST['username']) && $_REQUEST['name']!='')
   {
      //make sure we have a matching system
      $sql='select port.id, port.name as port, swi.name as switch, users.username, vlan.id as vlan
            from systems as sys left join port as port on port.id=sys.lastport left join switch as swi on port.switch=swi.id, vlan, users
            where sys.id='.$_REQUEST['id'].' and vlan.id='.$_REQUEST['vlan'].' and users.id=\''.$_REQUEST['username'].'\';';
      $result=mysql_query($sql) or die('Query failed: '.mysql_error());
      if (mysql_num_rows($result)!=1)
      {
         echo 'System, VLAN or User missmatch.';
      }
      else
      {
         $row=mysql_fetch_array($result);
         $sql="delete from systems where id='{$_REQUEST['id']}';";
         mysql_query($sql) or die('Query failed: '.mysql_error());
         //Record successfully deleted, inform user
         $sql="insert into guilog (who, host, datetime, priority, what) values ('$uname','$remote_host',NOW(),'info','Deleted system: ".$_REQUEST['name'].', '.$_REQUEST['mac'].', WEBGUI, '.$_REQUEST['comment'].', '.$_REQUEST['office'].', '.$row['port'].', '.$row['switch'].',vlan'.$_REQUEST['vlan'].'\');';
         mysql_query($sql) or die('Query failed: '.mysql_error());
         //Delete successful
         echo '<br />Delete successful.<br />';
         echo "<br />Click <a href=\"{$_SERVER['PHP_SELF']}\">here</a> to return to main page";
      }
   }
         
   // mark switchport for restart
   else if ($_REQUEST['action']=='restartport' && is_numeric($_REQUEST['port'])){
        // make sure this switchport exists
        $sql='SELECT p.id, p.name as port, swi.name as switch
                FROM port as p LEFT JOIN switch as swi ON p.switch=swi.id
                WHERE p.id='.$_REQUEST['port'].';';
        $result=mysql_query($sql) or die('Query failed: ' . mysql_error());
        if (mysql_num_rows($result)!=1){
                echo 'Switch/Port missmatch.';
        }
        // Got it, mark port for restart
        else {
                $r=mysql_fetch_array($result);
                $sql='UPDATE port SET restart_now=1 WHERE id='.$_REQUEST['port'].';';
                mysql_query($sql) or die('Query failed: ' . mysql_error());
                // Mark OK
                // Port marked for restart
                echo '<br />Port '.$r['port'].' on switch '.$r['switch'].' will be restarted whithin the next minute.';
        }
   }
   // show export choices
   else if ($_REQUEST['action']=='export'){
        echo '<table width="1000" border="0"><tr><td>Excel Export<br /><br />'."\n";
        echo '<a href="'.$_SERVER['PHP_SELF'].'?action=xls&type=all">All systems</a><br />'."\n";
        echo '<a href="'.$_SERVER['PHP_SELF'].'?action=xls&type=12plus">Not seen during last 12 month</a><br />'."\n";
        echo '</td></tr></table>'."\n";
   }
   // display (all) systems
   else {
        // get the systems
        $sql='SELECT sys.id, sys.name, sys. mac, vstat.value as status, sys.vlan, vlan.default_name as vlanname, lvlan.default_name as lastvlan, us.username as user, us.surname, us.givenname, port.name as port, sys.lastseen, swi.name as switch, swi.ip as switchip, sys.r_ip as lastip
                FROM systems as sys left JOIN vstatus as vstat ON sys.status=vstat.id LEFT JOIN vlan as vlan ON sys.vlan=vlan.id LEFT JOIN vlan as lvlan ON sys.lastvlan=lvlan.id LEFT JOIN users as us ON sys.uid=us.id LEFT JOIN port as port ON sys.lastport=port.id LEFT JOIN switch as swi ON port.switch=swi.id';

        // if its a search adjust the where...
        if ($_REQUEST['action']=='search'){
                $sql.=' WHERE (1=1)';
                // looking for a certain system?
                if ($_SESSION['name']!=''){
                        $sql.=' AND sys.name LIKE \''.$_SESSION['name'].'\'';
                }
                // looking for a mac address?
                if ($_SESSION['mac']!=''){
                        $sql.=' AND sys.mac LIKE \''.$_SESSION['mac'].'\'';
                }
                // looking for aVLAN?
                if ($_SESSION['vlan']!=''){
                        $sql.=' AND sys.vlan LIKE \''.$_SESSION['vlan'].'\'';
                }
                // looking for a user?
                if ($_SESSION['username']!=''){
                        $sql.=' AND sys.uid = '.$_SESSION['username'];
                }
                // looking for a switch?
                if ($_SESSION['switch']!=''){
                        $sql.=' AND swi.id='.$_SESSION['switch'];
                }
                // looking for an ip?
                if ($_SESSION['ip']!=''){
                        $sql.=' AND sys.r_ip LIKE \''.$_SESSION['ip'].'\'';
                }
                $sql.=' ORDER BY sys.name ASC;';
        }

        // ... if not get today's unknowns
        else{
                $sql.=' WHERE sys.name=\'unknown\' AND sys.LastSeen > (NOW() - INTERVAL 1 DAY)';
                $sql.=' ORDER BY sys.LastSeen DESC;';
        }

        $result=mysql_query($sql) or die('Query failed: ' . mysql_error());
        // echo table head
        echo '<table width="1000" border="0">
  <tr>
    <td width="140" class="center">Name</td>
    <td width="101" class="center">MAC</td>
    <td width="54" class="center">Status</td>
    <td width="60" class="center">Vlan</td>
    <td width="60" class="center">Last Vlan </td>
    <td width="112" class="center">Username</td>
    <td width="49" class="center">Port</td>
    <td width="163" class="center">Last Seen </td>
    <td width="105" class="center">Switch</td>
    <td width="112" class="center">Last IP </td>
  </tr>
        ';

        // if it is a search print the search area
        if ($_REQUEST['action']=='search'){
                echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
                echo '<tr align="center">'."\n";
                // Name
                echo '<td><input name="name" type="text" size="20" value="'.$_SESSION['name'].'" /></td>'."\n";
                // MAC
                echo '<td><input name="mac" type="text" size="14" value="'.$_SESSION['mac'].'" /></td>'."\n";
                // Status
                echo '<td>&nbsp;</td>'."\n";
                // VLAN
                echo '<td><select name="vlan">';
                $sql='SELECT id, default_name as value FROM vlan ORDER BY value;'; // Get details for all vlans
                $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
                if (mysql_num_rows($res)>0){
                        echo '<option value=""></option>'."\n";
                        while ($r=mysql_fetch_array($res)){
                                if ($r['value']!=''){ // only those with actual values
                                        echo '<option value="'.$r['id'].'" '.($r['id']==$_SESSION['vlan']?'selected="selected"':'').'>'.$r['value'].'</option>'."\n";
                                }
                        }
                }
                echo '</select></td>'."\n";
                // Last VLAN
                echo '<td>&nbsp;</td>'."\n";
                // Username
                echo '<td><select name="username">';
                $sql='SELECT DISTINCT(us.username), us.id as uid FROM users as us RIGHT JOIN systems AS sys ON us.id=sys.uid ORDER BY username ASC;'; // Get details for all active users
                $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
                if (mysql_num_rows($res)>0){
                        echo '<option value=""></option>'."\n";
                        while ($r=mysql_fetch_array($res)){
                                if ($r['username']!=''){ // only those with actual values
                                        echo '<option value="'.$r['uid'].'" '.($r['uid']==$_SESSION['username']?'selected="selected"':'').'>'.$r['username'].'</option>'."\n";
                                }
                        }
                }
                echo '</select></td>'."\n";
                // Port
                echo '<td>&nbsp;</td>'."\n";
                // Last seen
                echo '<td>&nbsp;</td>'."\n";
                // Switch
                echo '<td><select name="switch">';
                $sql='SELECT DISTINCT(swi.name) AS switch, swi.id FROM switch as swi ORDER BY switch ASC;'; // Get details for all active switches
                $res=mysql_query($sql) or die('Query failed: ' . mysql_error());
                if (mysql_num_rows($res)>0){
                        echo '<option value=""></option>'."\n";
                        while ($r=mysql_fetch_array($res)){
                                if ($r['switch']!=''){ // only those with actual values
                                        echo '<option value="'.$r['id'].'" '.($r['id']==$_SESSION['switch']?'selected="selected"':'').'>'.$r['switch'].'</option>'."\n";
                                }
                        }
                }
                echo '</select></td>'."\n";
                // Last IP
                echo '<td><input name="ip" type="text" size="16" value="'.$_SESSION['ip'].'" /></td>'."\n";
                echo '</tr>'."\n";
                // Clear/Submit
                echo '<tr align="right">
                  <td colspan="10"><input type="submit" name="submit" value="Submit" />
                        <input type="submit" name="submit" value="Clear" /></td>
                </tr>
                <input type="hidden" name="action" value="search" /></form>
                ';
        }
        // Nothing found.
        if (mysql_num_rows($result)<1){
                echo ' <td colspan="7">No entries found.</td></td>';
        }
        // Found something
        else {
                // Iterate trough the result set
                $i=0;
                echo print_resultset($result,$_SERVER);
        }
        echo '</table>';
   }

    // we're done. and as all tags need to be closed, print the footer now!
   echo print_footer();

}

if ($ad_auth===true)
{
   $rights=user_rights($_SERVER['AUTHENTICATE_USERPRINCIPALNAME']);
   if ($rights>=2)
   {
      page();
   }
   else if ($rights==1)
   {
      echo "<html>\n";
      echo "<head>\n";
      echo "\t<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=../read\" />\n";
      echo "</head>\n";
      echo "</html>\n";
   }
   else echo "<h1>ACCESS DENIED</h1>";
}
else
   page();


?>
