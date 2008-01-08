#!/usr/bin/php
<?php
/**
 * /opt/nac/bin/portscan
 *
 * Long description for file:
 * Learn open ports from equipments allowed
 * Currently it has 3 modes of operation:
 * Normal: Without parameters
 *    Will get ips from devices allowed in the network and will scan them
 * Scannow: With the parameter "scannow"
 *    Will get ips which have the scannow value set to 1 no matter if
 *    they are allowed in the network or not
 * Manual: 
 *    Every parameter passed through the command line will be taken
 *    as an ip to be scanned. No to be used in combination with the 
 *    "scannow" parameter
 *
 * Important: You have to define first networks to scan in the 
 *    nac_netsallowed table
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Héctor Ortiz (FreeNAC Core Team)
 * @copyright			2006 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link				http://www.freenac.net
 *
 */

require_once "funcs.inc.php";
$output=TRUE;

$logger->setDebugLevel(0);
$logger->setLogToStdOut(true);

#Compatibility with old vars
if (!$conf->scan_directory && $conf->nmap_scan_directory)
   $scan_directory=$conf->nmap_scan_directory;
else 
   $scan_directory=$conf->scan_directory;

if (!$conf->what_units_time && $conf->nmap_what_units_time)
   $what_units_time=$conf->nmap_what_units_time;
else 
   $what_units_time=$conf->what_units_time;

if (!$conf->time_threshold && $conf->nmap_time_threshold)
   $time_threshold=$conf->nmap_time_threshold;
else
   $time_threshold=$conf->time_threshold;

if (!$conf->which_nmap && $conf->nmap_path)
   $which_nmap=$conf->nmap_path;
else
   $which_nmap=$conf->which_nmap;

check_requirements();
//If we get up to this point, life is good
$queries['number']=0;
$queries['messages']=0;
$flagscannow=0;
for ($i=0;$i<$argc;$i++)
{
   if ($argv[$i]=="--scannow")
      $flagscannow++;
   else if ($argv[$i]=="--verbose")
      $logger->setDebugLevel(1);      
}  
if ($flagscannow)
   $output=FALSE;

message("Doing port_scan to sytems... Please wait...\n");
$logger->debug("port_scan started");
$file_timestamp=date('Y-m-d H:i:s');
$file_timestamp=str_replace(' ','-',$file_timestamp);

$scan_results=$scan_directory."/scan-$file_timestamp.xml";   	//Scan file
$logger->debug("Scan file: $scan_results");		//Parameters from port_scan.inc
$logger->debug("Nmap flags: ".$conf->nmap_flags);
if (($what_units_time<0)||($what_units_time>6)||($what_units_time==2))
   $string=$time_threshold." hours";
else if ($what_units_time==0)
   $string=$time_threshold." seconds";
else if ($what_units_time==1)
   $string=$time_threshold." minutes";
else if ($what_units_time==3)
   $string=$time_threshold." days";
else if ($what_units_time==4)
   $string=$time_threshold." weeks";
else if ($what_units_time==5)
   $string=$time_threshold." months";
else if ($what_units_time==6)
   $string=$time_threshold." years";
$logger->debug("Last_seen threshold: $string");
if ($argc==1)				//Running mode
   $logger->debug("Running mode: Normal");
else if ($flagscannow)
   $logger->debug("Running mode: Scannow");
else $logger->debug("Running mode: Manual"); 
$list=scan($scan_results,$conf->nmap_flags);		//Scan network with those flags	
$var=parse_scanfile($scan_results,$list);		//Parse the xml file
if ($var['equipments']>0)			
   do_inventory($var);				//Check against database	
else
   $var['equipments']=0;

syscall("rm $scan_results");
message("port_scan finished normally. ".$var['equipments']." hosts scanned\n");
$logger->debug("port_scan finished normally. ".$var['equipments']." hosts scanned\n");
if ($flagscannow)
   log2db('info',"port_scan finished normally. ".$var['equipments']." hosts scanned");

function check_requirements()  //Checks for required functions and tables structure
{
   global $conf,$what_units_time, $scan_directory, $time_threshold, $which_nmap;
   $functions=get_defined_functions();
   $functions=$functions['user']; //A little bit of paranoia :)
   if (!in_array('logit',$functions))
      check_and_abort("Function logit not defined in funcs.inc\n",0);
   if (!in_array('db_connect',$functions))
      check_and_abort("Function db_connect not defined in funcs.inc\n",0);
   if (!in_array('syscall',$functions))
      check_and_abort("Function syscall not defined in funcs.inc\n",0);
   if (!in_array('normalise_mac',$functions))
      check_and_abort("Function normalise_mac not defined in funcs.inc\n",0);
   if (!in_array('log2db',$functions))
      check_and_abort("Function log2db not defined in funcs.inc\n",0);
   if (!$which_nmap)
      check_and_abort("Var \$which_nmap not defined in port_scan.inc\n",0);
   if (!$scan_directory)
      check_and_abort("Var \$scan_directory not defined in port_scan.inc\n",0);
   if (!$scan_directory)
        check_and_abort("Required directory $scan_directory doesn't exist\n",0);
   if (!$time_threshold)
      check_and_abort("Var \$time_threshold not defined in port_scan.inc\n",0);
   if (!$what_units_time)
     check_and_abort("Var \$what_units_time not defined in port_scan.inc\n",0);
   $query="describe systems;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly installed FreeNAC\n",$res);
   $query="describe nac_hostscanned;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly followed the doc file README.port_scan\n",$res);
   $query="describe nac_openports;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly followed the doc file README.port_scan\n",$res);
   $query="describe subnets;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly followed the doc file README.port_scan\n",$res);
   $query="describe services;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly followed the doc file README.port_scan\n",$res);
   $query="describe protocols;";
   $res=execute_query($query);
   check_and_abort("Please make sure you have properly followed the doc file README.port_scan\n",$res);
   $tmp=syscall($which_nmap." --version | grep -i nmap");
   $tmp=explode(" ",$tmp);
   $nmap_string=$tmp[0];
   $nmap_version=$tmp[2];
   if (isset($nmap_string)&&(strcasecmp($nmap_string,"nmap")!=0))
      check_and_abort("Nmap seems not to be installed in your system\n",0);
   #if (isset($nmap_version)&&($nmap_version<4.11))
   if (isset($nmap_version)&&($nmap_version<4.10))
      check_and_abort("You need a newer version of nmap\n",0);
   if (!$conf->nmap_flags)
     check_and_abort("Var \$nmap_flags not defined in port_scan.inc\n",0);
}

function message($string)
{
   global $output,$logger,$output_to_syslog;
   if (($output===TRUE)&&(!$logger->getDebugLevel()))
   {
      if ($output_to_syslog===TRUE)
      {
         $logger->logit($string);
         //log2db('info',$string);
      }   
      else
      {
         $logger->setLogToStdOut();
         $logger->logit($string);
         $logger->setLogToStdOut(false);
      }
   }
}

function validate($string)
{
   rtrim($string,' ');
   if (get_magic_quotes_gpc()) {
      $value=stripslashes($string);
   }
   if (!is_numeric($string)) {
      $string= mysql_real_escape_string($string);
   }
   return $string;
}

function do_something($query)			//Let's do something with our structure
{
   global $logger;
   $queries=$query['number'];   		//How many queries we have?
   $messages=$query['messages'];   		//How many messages?
   for ($i=0;$i<$queries;$i++)
   {
      execute_query($query['query'][$i]);   	//Execute the queries
   }
   for ($i=0;$i<$messages;$i++)
        $logger->debug($query['message'][$i]);   	//And display the messages
}

function update_queries($mesg,$what)
{
   global $queries; //Here we hold messages and queries
   if ($what=='q')
   {
      $queries['query'][$queries['number']]=$mesg; //This is a query
      $queries['number']++;	//Count queries
   }
   else if ($what=='m')
   {
      $queries['message'][$queries['messages']]=$mesg; //This is a message
      $queries['messages']++;	//Count messages
   }
}

function do_inventory($data_from_xml)
{
   global $queries;
   for ($i=0;$i<$data_from_xml['equipments'];$i++)	//How many hosts scanned
   {
       $ip=$data_from_xml[$i]['ip'];		//Get ip of one host
       $id=$data_from_xml[$i]['sid'];
       $query=sprintf("select * from nac_hostscanned where sid='%s';",$id);
       $res=execute_query($query);
       if ($res)
       {
          if (mysql_num_rows($res) ==0)
             add_entry($data_from_xml[$i]);//Host not found in database 
          else
             check_existent($data_from_xml[$i]); //Host in database, let's see if something has changed
       }
   }
   do_something($queries); 			//Do something with the structure
}

function check_existent($data) 	//This function will check info concerning one host scanned against its info in the database
{
   $timestamp=date('Y-m-d H:i:s');
   if ((!isset($data))||(!is_array($data)))
      check_and_abort("There was a problem parsing the XML file. Make sure you have the right version of PHP and libXML in your system",0);
   $ip=$data['ip'];   
   $ports=$data['ports'];  			//Number of open ports this time
   $hostname=strtolower($data['hostname']);
   $os=$data['os'];   				//OS system this time
   $id=$data['sid'];
   $query=sprintf("select * from nac_hostscanned where sid='%s';",$id);
   $res=execute_query($query);
   if ($res)
   {
      $result=mysql_fetch_array($res, MYSQL_ASSOC);
      $db_ports=mysql_num_rows($res);
      $db_ip=$result['ip'];			//Same IP from last time?
      $db_hostname=strtolower($result['hostname']);		//Same hostname from last time?
      $db_os=$result['os'];			//Same OS from last time?
      $db_timestamp=$result['timestamp'];  	//If it changed, since when?
      $host_changed=$os_changed=$mac_changed=0; //To control if we need to update its record in the database
      if (!empty($hostname)&&!empty($db_hostname)&&(strcasecmp($hostname,$db_hostname)!=0))   	//Info about its hostname
      {
         if ((strcasecmp($db_hostname,'NULL')==0)&&(strcasecmp($hostname,'NULL')!=0))
         { 
            update_queries("Host $ip has its hostname resolved now. $ip is $hostname\n",'m');
            $host_changed++;
         }
         else if ((strcasecmp($db_hostname,'NULL')!=0)&&(strcasecmp($hostname,'NULL')==0))
         {
            update_queries("Unable to resolve $ip this time, old hostname $db_hostname preserved\n",'m');
            $mac=$db_mac;
         }
         else
         { 
            update_queries("Old hostname $db_hostname no longer valid. Renamed to $hostname\n",'m');
            $host_changed++;
         }
      }
      if (!empty($os)&&!empty($db_os)&&(strcasecmp($os,$db_os)!=0))		//Info about its OS
      {
         if ((strcasecmp($db_os,'NULL')==0)&&(strcasecmp($os,'NULL')!=0))
         {
            update_queries("OS from $ip now determined. $ip is using $os\n",'m');
            $os_changed++;
         }
         else if ((strcasecmp($db_os,'NULL')!=0)&&(strcasecmp($os,'NULL')==0))
         {
            //update_queries("No OS info yet for $ip this time\n",'m');
            $os=$db_os;
         }
         else
         {
            if (strcasecmp($db_os,'Unreachable')!=0)
               update_queries("$ip has changed its OS since $db_timestamp. Now is using $os\n",'m');
            $os_changed++;
         }
      }
      $changes=$host_changed+$os_changed+$mac_changed;
      if($changes>0)
      {
         $query=sprintf("update nac_hostscanned set hostname='%s',os='%s',timestamp='%s' where sid='%d' and ip='%s';",$hostname,$os,$timestamp,$id,$ip);
         update_queries($query,'q');
      }
      $query=sprintf("select o.banner as banner,o.timestamp as timestamp, p.name as protocol, s.port as port from nac_openports o inner join services s on o.service=s.id inner join protocols p on s.protocol=p.protocol and o.sid='%s';",$id);
      $res1=execute_query($query);
      if ($res1)				//Let's check info about ports
      {
         $db_ports=mysql_num_rows($res1);
         $counter=0; 
         $db_tcp=0;
         $db_udp=0;
         while ($result=mysql_fetch_array($res1, MYSQL_ASSOC))
         {
            if (strcasecmp($result['protocol'],'tcp')==0)
            {
               $db_tmp_port_tcp[$db_tcp]['port']=$result['port'];
               $db_tmp_port_tcp[$db_tcp]['timestamp']=$result['timestamp'];
               $db_tmp_port_tcp[$db_tcp]['banner']=$result['banner'];
               $db_tcp++;
            }
            else if (strcasecmp($result['protocol'],'udp')==0)
            {
               $db_tmp_port_udp[$db_udp]['port']=$result['port'];
               $db_tmp_port_udp[$db_udp]['timestamp']=$result['timestamp'];
               $db_tmp_port_udp[$db_udp]['banner']=$result['banner'];
               $db_udp++;
            }
         }
         if ((isset($db_tmp_port_tcp))&&(is_array($db_tmp_port_tcp)))
            sort($db_tmp_port_tcp);
         if ((isset($db_tmp_port_udp))&&(is_array($db_tmp_port_udp)))
            sort($db_tmp_port_udp);
         if (($db_tcp==0)&&($db_udp==0)) //In case we have no info in the db
         {
            $db_protocol[0]='tcp';
            $db_port[0]=0;
            $db_porttstmp[0]='0000-00-00 00:00:00';
            $db_banner[0]=':'; 
         }
         else
         {
            for ($i=0;$i<$db_tcp;$i++)
            {
               $db_protocol[$i]='tcp';
               $db_port[$i]=$db_tmp_port_tcp[$i]['port'];
               $db_porttstmp[$i]=$db_tmp_port_tcp[$i]['timestamp'];
               $db_banner[$i]=$db_tmp_port_tcp[$i]['banner'];
            }
            for (;$i<($db_tcp+$db_udp);$i++)
            {
               $db_protocol[$i]='udp';
               $db_port[$i]=$db_tmp_port_udp[($i-$db_tcp)]['port'];
               $db_porttstmp[$i]=$db_tmp_port_udp[($i-$db_tcp)]['timestamp'];
               $db_banner[$i]=$db_tmp_port_udp[($i-$db_tcp)]['banner'];
            }
         }
         $tcp=0;
         $udp=0;
         for ($i=0;$i<$ports;$i++)
         {
            if ($data['port'][$i]['protocol']=='tcp')
            {
               $tmp_port_tcp[$tcp]['port']=$data['port'][$i]['portid'];
               $tmp_port_tcp[$tcp]['banner']=$data['port'][$i]['description'];
               $tcp++;
            }
            else if ($data['port'][$i]['protocol']=='udp')
            {
               $tmp_port_udp[$udp]['port']=$data['port'][$i]['portid'];
               $tmp_port_udp[$udp]['banner']=$data['port'][$i]['description']
;
               $udp++;
            }
         }
         if ((isset($tmp_port_tcp))&&(is_array($tmp_port_tcp)))
            sort($tmp_port_tcp);
         if ((isset($tmp_port_udp))&&(is_array($tmp_port_udp)))
            sort($tmp_port_udp);
         if (($tcp==0)&&($udp==0)) //In case our scan didnt detect any open ports
         {
            $protocol[$i]='tcp';
            $port[$i]=0;
            $banner[$i]=':';

         }
         else
         {
            for ($i=0;$i<$tcp;$i++)
            {
               $protocol[$i]='tcp';
               $port[$i]=$tmp_port_tcp[$i]['port'];
               $banner[$i]=$tmp_port_tcp[$i]['banner'];
            }
            for ($i;$i<($tcp+$udp);$i++)
            {
               $protocol[$i]='udp';
               $port[$i]=$tmp_port_udp[($i-$tcp)]['port'];
               $banner[$i]=$tmp_port_udp[($i-$tcp)]['banner'];
            }
         }
         $add_counter=0;	//Let's count the number of new open ports 
         $remove_counter=0;	//Let's count the number of old closed ports
	 $update_counter=0;	//Number of ports which version service has changed
         $result='';
         if (is_array($port)&&is_array($db_port))
         {
  	    $result=array_diff($port,$db_port);	//New open ports discovered that are to be added to the database
            $position=array_keys($result);	//Their position in the array
            $index=count($result);		//Number of open ports this time
            for ($i=0;$i<$index;$i++)
            {
               if ($port[$position[$i]]!=0)
               {
                  $add_port[$add_counter]=$result[$position[$i]];
                  $add_prot[$add_counter]=$protocol[$position[$i]];
                  $add_banner[$add_counter]=$banner[$position[$i]];
                  update_queries('New port open ('.$add_port[$add_counter].'/'.$add_prot[$add_counter].") on host $ip.\n",'m');
                  check_service($ip,$add_port[$add_counter],$add_prot[$add_counter],$add_banner[$add_counter]);
                  $add_counter++;
               }
            }
         
            $result='';
            $result=array_diff($db_port,$port);	//Old open ports which are not open in this new scan, therefore should be discarded from the database
            $position=array_keys($result);
            $index=count($result);
            for ($i=0;$i<$index;$i++)
            {
               if ($db_port[$position[$i]]!=0)
               {
                  $remove_port[$remove_counter]=$result[$position[$i]];
                  $remove_prot[$remove_counter]=$db_protocol[$position[$i]];
                  update_queries('Port ('.$remove_port[$remove_counter].'/'.$remove_prot[$remove_counter].") on host $ip no longer open since ".$db_porttstmp[$position[$i]]."\n",'m');
                  $remove_counter++;
               }
            }
	    $xml_result=array_intersect($port,$db_port);  //Now we check the protocol to find out if it is the same service, if not, we add the service to the database
	    $db_result=array_intersect($db_port,$port);
            if (isset($xml_result)&&isset($db_result))
            {
	       $xml_position=array_keys($xml_result);
	       $db_position=array_keys($db_result);
	       $index=count($xml_position);
	       $j=0;
	       $result='';
	       for ($i=0;$i<$index;$i++)
   	          if (!empty($protocol[$xml_position[$i]])&&!empty($db_protocol[$db_position[$i]])&&strcasecmp($protocol[$xml_position[$i]],$db_protocol[$db_position[$i]] )!=0)
   	          {
                     $result[$j]=$xml_result[$xml_position[$i]]; //This is a new service since we have another port number but with different protocol
	  	     $prot_res[$j]=$protocol[$xml_position[$i]];
                     $string[$j]=$banner[$xml_position[$i]];
	  	     $j++;
   	          }  
                  else
                  {
                     check_service($ip,$xml_result[$xml_position[$i]],$protocol[$i],$banner[$xml_position[$i]]); //Let's check if the service is running on its default port
                     if (!empty($banner[$xml_position[$i]])&&!empty($db_banner[$db_position[$i]])&&strcasecmp($banner[$xml_position[$i]],$db_banner[$db_position[$i]])!=0) //Now let's check if the service has changed
                     {
                        $service=explode(':',$db_banner[$db_position[$i]]);
			$service=$service[0];
			$update_port[$update_counter]=$port[$xml_position[$i]];
			$update_prot[$update_counter]=$protocol[$i];
			$update_banner[$update_counter]=$banner[$xml_position[$i]];
 			update_queries("Service $service(".$port[$xml_position[$i]].'/'.$protocol[$i].") on host $ip has changed since ".$db_porttstmp[$db_position[$i]].'. Was using '.$db_banner[$db_position[$i]].' but now is using '.$banner[$xml_position[$i]]."\n",'m');
			$update_counter++;
		     }
                  }
               if (is_array($result))	//We have collected ports to add, let's pass them to our add_port structure and print some messages to inform
               {
                  $keys_result=array_keys($result);
                  $differences=count($keys_result);
                  for ($i=0;$i<$differences;$i++)
                  {
                     $add_port[$add_counter]=$result[$keys_result[$i]];
                     $add_prot[$add_counter]=$prot_res[$keys_result[$i]];
                     $add_banner[$add_counter]=$string[$keys_result[$i]];
                     update_queries('New port open ('.$add_port[$add_counter].'/'.$add_prot[$add_counter].") on host $ip.\n",'m');
                     check_service($ip,$add_port[$add_counter],$add_prot[$add_counter],$add_banner[$add_counter]);
                     $add_counter++;
                  }
               }
            }
            $db_result=array_intersect($db_port,$port);  //This is the same than list time, but now we are checking the services from the database that we are going to delete
            $xml_result=array_intersect($port,$db_port);
            if (isset($db_result)&&isset($xml_result))
            {
            
               $xml_position=array_keys($xml_result);
               $db_position=array_keys($db_result);
               $index=count($db_position);
               $j=0;
               $result='';
               for ($i=0;$i<$index;$i++)
                  if (!empty($protocol[$xml_position[$i]])&&!empty($db_protocol[$db_position[$i]])&&strcasecmp($protocol[$xml_position[$i]],$db_protocol[$db_position[$i]] )!=0)
                  {
                     $result[$j]=$db_result[$db_position[$i]]; //If protocol is different this time, delete it
                     $prot_res[$j]=$db_protocol[$db_position[$i]];
                     $tstmp[$j]=$db_porttstmp[$db_position[$i]];
                     $j++;
                  }
               if (is_array($result))
               {
                  $keys_result=array_keys($result);
                  $differences=count($keys_result);
                  for ($i=0;$i<$differences;$i++)	//Let's update our remove_port structure
                  {
                     $remove_port[$remove_counter]=$result[$keys_result[$i]];
                     $remove_prot[$remove_counter]=$prot_res[$keys_result[$i]];
                     update_queries('Port ('.$remove_port[$remove_counter].'/'.$remove_prot[$remove_counter].") on host $ip no longer open since ".$tstmp[$keys_result[$i]]."\n",'m');
                     $remove_counter++;
                  }
               }
             }
         }
         for ($i=0;$i<$remove_counter;$i++)	//Ok, let's perform the queries to delete our old closed ports
         {
            $res=execute_query("select s.id as id from services s inner join protocols p on s.protocol=p.protocol and p.name='".$remove_prot[$i]."' and s.port='".$remove_port[$i]."';");
            $result=mysql_fetch_array($res,MYSQL_ASSOC);
            $query=sprintf("delete from nac_openports where sid='%s' and service='%s';",$id,$result['id']);
            update_queries($query,'q');
         }
         for ($i=0;$i<$add_counter;$i++)	//And the queries for the new open ports
         {
            $res=execute_query("select s.id as id from services s inner join protocols p on s.protocol=p.protocol and p.name='".$add_prot[$i]."' and s.port='".$add_port[$i]."';");
            $result=mysql_fetch_array($res,MYSQL_ASSOC); 
            $query=sprintf("insert into nac_openports (sid,service,banner,timestamp) values ('%s','%s','%s','%s');",$id,$result['id'],$add_banner[$i],$timestamp);
            update_queries($query,'q');
         }
	 for ($i=0;$i<$update_counter;$i++)
	 {
            $res=execute_query("select s.id as id from services s inner join protocols p on s.protocol=p.protocol and p.name='".$update_prot[$i]."' and s.port='".$update_port[$i]."';");
            $result=mysql_fetch_array($res,MYSQL_ASSOC);            
            $query=sprintf("update nac_openports set banner='%s',timestamp=NOW() where sid='%d' and service='%s';",$update_banner[$i],$timestamp,$id,$result['id']);
	    update_queries($query,'q');
	 }
         if ($add_counter)
            update_queries("Number of new ports open on host $ip: $add_counter",'m');
         if ($remove_counter)
            update_queries("Number of old ports closed on host $ip: $remove_counter",'m');
         if ($update_counter)
            update_queries("Number of updated services on host $ip: $update_counter",'m');
      }
   }
}

function check_service($ip,$port,$protocol,$banner)  //With this function we check if a service is running on the port that it has been assigned to by IANA
{
   $query="select s.name as service from services s inner join protocols p on s.protocol=p.protocol and p.name='$protocol' and s.port='$port';";
   $res=execute_query($query);
   if ($res)
   {
      $result=mysql_fetch_array($res,MYSQL_ASSOC);
      $service=explode(':',$banner); 
      $service=$service[0];	//We are interested only in the name of the service
      if (!empty($service)&&!empty($result['service'])&&(!substr_count(strtolower($service),strtolower($result['service']))))
         if (!empty($result['service'])&&!empty($service)&&(!substr_count(strtolower($result['service']),strtolower($service))))
         {
            update_queries("Service $service ($port/$protocol) on scanned host $ip is using a port which is reserved for ".$result['service']."\n",'m');   
         }
   }
}

function execute_query($query)	
{
   global $logger;
   db_connect();
   $res=mysql_query($query);
   $logger->debug($query,3);
   if (!$res)
   { $logger->logit("Cannot execute query $query because ".mysql_error()."\n"); }
   return $res;
}

function add_entry($data)	//A new host in our network that needs to be added to the database
{
   $new=false;
   if ((!isset($data))||(!is_array($data)))
      check_and_abort("There was a problem parsing the XML file. Make sure you have the right version of PHP and libXML in your system",0);
   $timestamp=date('Y-m-d H:i:s');
   $res=execute_query("select id from systems where r_ip='".$data['ip']."' and r_timestamp>=DATE_SUB(NOW(),INTERVAL 3 HOUR);");
   $result=mysql_fetch_array($res,MYSQL_ASSOC);
   $sid=$result['id'];
   
   #Let's check if it is really a new device, if not, do an update
   $res=execute_query("select * from nac_hostscanned where sid='".$sid."'");
   if (mysql_num_rows($res)==0)
   {
      $new=true;
      $query=sprintf("insert into nac_hostscanned (sid,ip,hostname,os,timestamp) values('%s','%s','%s','%s','%s');",$sid,$data['ip'],strtolower($data['hostname']),$data['os'],$timestamp);
   }
   else
   {
      $query=sprintf("update nac_hostscanned set ip='%s', hostname='%s', os='%s', timestamp='%s' where sid='%s';",$data['ip'],strtolower($data['hostname']),$data['os'],$timestamp,$sid);
      $new=false;
   }

   $res=execute_query($query);
   if ($res)
   {
      if ($new)
         update_queries("Host ".$data['ip']."(".$data['hostname'].") added to the database\n",'m');
      else
         update_queries("Host ".$data['ip']."(".$data['hostname'].") has been updated\n",'m');
      $query=sprintf("select id from nac_hostscanned where ip='%s' and hostname='%s' and os='%s' and timestamp='%s' and sid='%s';",$data['ip'],$data['hostname'],$data['os'],$timestamp,$data['sid']);
      $res=execute_query($query);
      if ($res)
      {
         $result=mysql_fetch_array($res, MYSQL_NUM);
         $id=$result[0];
         check_existent($data);
      }
   }
   return($queries);
}

function scan($xml_file,$nmap_flags)	//We perform the scan with the flags specified in the file "port_scan.inc" and the location for our XML file
{
   global $which_nmap,$logger;
   syscall("touch ".$xml_file);	//Just to avoid an error while running from cron
   $scan=$which_nmap." ".$nmap_flags." -oX ".$xml_file." ";
   $list=get_ips();		//We need some IPs to scan
   for ($i=0;$i<$list['counter'];$i++) //Put those ips in a string
   {
      message("Scanning host ".$list['ip'][$i]."\n");
      $logger->debug("Scanning host ".$list['ip'][$i]);
      $hosts.=" ".$list['ip'][$i];
   }
   $logger->debug("Total number of hosts to scan: ".$list['counter']);
   $scan.=$hosts;		//Now our command line is complete
   syscall($scan);		//Scan
   return($list);
}

function check_and_abort($message,$resource)	//This function checks if there is a problem with an important query and aborts. It is also used to abort the script because we want to do it
{
   global $scan_results,$logger;
   if (!is_resource($resource))		//Let's abort just for fun
   {
      syscall("rm $scan_results");
      message($message);
      message("port_scan ended abnormally.\n");
      $logger->debug($message);
      $logger->debug("port_scan ended abnormally.\n");
      //log2db('err',$message);
      //log2db('err',"port_scan ended abnormally.");
      exit();
   }
   else
   if (mysql_num_rows($resource) == 0)	//No results from our query so there is no point in continuing execution
   {
      syscall("rm $scan_results");
      message($message);
      message("port_scan ended abnormally.\n");
      $logger->debug($message);
      $logger->debug("port_scan ended abnormally.\n");
      //log2db('err',$message);
      //log2db('err',"port_scan ended abnormally.");
      exit();
   }
}

function valid_ip($ip)
{
   if (ereg("^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$",$ip,$res))
   {
      for($i=1;$i<5;$i++)
        if($res[$i]>255)
          return FALSE;
   }
   else 
   {
      return FALSE;
   }
   return TRUE;
}

function ip_to_bin($ip)
{
   $address=explode('.',$ip);
   for ($m=0;$m<4;$m++)
      $bin[$m]=str_pad(decbin($address[$m]),8,"0",STR_PAD_LEFT);
   $address=implode("",$bin);
   return($address);
}

function get_ips()	//This function will get some ips to scan
{
   global $what_units_time, $time_threshold, $argc, $argv,$flagscannow,$queries,$logger,$conf;
   $timestamp=date('Y-m-d H:i:s');
   $ips="";
   $list=array();
   $counter=0;
   if (($argc==2)&&($argv[1]=="--scannow"))
   {
      $query="select id,r_ip,name from systems where scannow=1 and r_ip!='NULL' and r_ip!='' and r_timestamp>=DATE_SUB(NOW(),INTERVAL ".validate($conf->scan_hours_for_ip)." HOUR);";
      $res=execute_query($query);
      check_and_abort("No systems have the flag \"scannow\" in the systems table\n",$res); 
      while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
      {
         if (empty($result['r_ip']))
            continue;
            #check_and_abort("Illegal value found in systems table.\n",0);
         else
            $devices['ip'][$counter]=$result['r_ip'];
         if ( ! empty($result['name']))
            #check_and_abort("Illegal value found in systems table (empty name).\n",0);
         #else
            $devices['hostname'][$counter]=$result['name'];
         $devices['sid'][$counter]=$result['id'];
         $counter++;
         $query="update systems set scannow=0 where r_ip='".$result['r_ip']."';";
         execute_query($query);
         logit('Nmap scan-now of ' . $result['r_ip'] . ' started');
         log2db('info','Nmap scan-now of ' . $result['r_ip'] . ' started');
      }
   }
   else if ($argc>=2)
   {
      if ($flagscannow)
         check_and_abort("The flag \"--scannow\" is not compatible with the parameters specified\n",0);
      for ($i=1;$i<$argc;$i++)
      if (!valid_ip($argv[$i]))
         check_and_abort("One or more IPs from the command line are not well defined as IPv4\n",0);
      for ($i=1;$i<$argc;$i++)
      {
         $device=validate($argv[$i]);
         if ($i==1)
            $query="select id, r_ip,name from systems where r_ip!='NULL' and r_ip='$device'";
         else
            $query.=" or r_ip='$device'";
      }
      $query.=" and r_timestamp>=DATE_SUB(NOW(),INTERVAL ".validate($conf->scan_hours_for_ip)." HOUR);";
      $res=execute_query($query);
      check_and_abort("IPs specified are not part of the systems table\n",$res);
      while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
      {
         if (empty($result['r_ip']))
            continue;
            #check_and_abort("Illegal value found in systems table.\n",0);
         else
            $devices['ip'][$counter]=$result['r_ip'];
         if ( ! empty($result['name']))
            #check_and_abort("Illegal value found in systems table.\n",0);
         #else
            $devices['hostname'][$counter]=$result['name'];
         $devices['sid'][$counter]=$result['id'];
         $counter++;
      }
      for ($i=1;$i<$argc;$i++)
      {
         $there=0;
         for ($j=0;$j<$counter;$j++)
            if ($argv[$i]==$devices['ip'][$j])
               $there++;   
         if (!$there)
         {
            if (empty($result['r_ip']))
               continue;
               #check_and_abort("Illegal value found in systems table.\n",0);
            else
               $devices['ip'][$counter]=$result['r_ip'];
            if ( ! empty($result['name']))
               #check_and_abort("Illegal value found in systems table.\n",0);
            #else
               $devices['hostname'][$counter]=$result['name'];
            $devices['sid'][$counter]=$result['id'];
            $counter++;
         }
      }
   }   
   else if ($argc==1)
   {
      #$query="select lastseen,r_ip,mac,name from systems where r_ip!='NULL' and status=1 and lastseen!='NULL';";
      if ($conf->scan_unmanaged)
         $query="select id,lastseen,r_ip,mac,name from systems where r_ip!='' and r_ip!='NULL' and status=1 or status=3 and lastseen!='NULL' and r_timestamp>=DATE_SUB(NOW(),INTERVAL ".validate($conf->scan_hours_for_ip)." HOUR);";
      else
         $query="select id,lastseen,r_ip,mac,name from systems where r_ip!='' and r_ip!='NULL' and status=1 and lastseen!='NULL' and r_timestamp>=DATE_SUB(NOW(),INTERVAL ".validate($conf->scan_hours_for_ip)." HOUR);";
      $res=execute_query($query);
      check_and_abort("No ip addresses to scan found in systems table.\n",$res);
      while ($result=mysql_fetch_array($res,MYSQL_ASSOC))
      {
         if (empty($result['r_ip']))
            continue;
            #check_and_abort("Illegal value found in systems table.\n",0);
         else
            $devices['ip'][$counter]=$result['r_ip'];
         if ( ! empty($result['name']))
            #check_and_abort("Illegal value found in systems table (empty name).\n",0);
         #else
            $devices['hostname'][$counter]=$result['name'];
         if ( ! empty($result['lastseen']))
            #check_and_abort("Illegal value found in systems table (not seen).\n",0);
         #else
            $devices['lastseen'][$counter]=$result['lastseen'];
         $devices['sid'][$counter]=$result['id'];
         $counter++;
      }
   } 
   $devices['counter']=$counter;
   $devices['ip']=array_unique($devices['ip']);
   //We got some ips, let's see which ones are candidates to be scanned according to the info provided in the nac_netsallowed table
   $query='select ip_address,ip_netmask,dontscan from subnets where scan=1;'; 
   $res1=execute_query($query);
   if ($res1)
   {
      $counter=0;
      $number=0;
      check_and_abort("Nothing to scan. No networks defined in subnets.\n",$res1);
      $logger->debug("Networks defined in subnets");
      while ($result1=mysql_fetch_array($res1,MYSQL_ASSOC))
      {
         $network[$number]['ip']=$result1['ip_address'];
         $network[$number]['netmask']=$result1['ip_netmask'];
         if ($result1['dontscan'])
         {
            $temp=explode(',',$result1['dontscan']);
            foreach($temp as $ip)
            {
               $network[$number]['dontscan'][]=trim($ip);
               if ($temp_number=array_search(trim($ip),$devices['ip']))
               {
                  $keys_to_delete[]=$temp_number; 
               }
            }
         }
         $logger->debug($result1['ip_address'].'/'.$result1['ip_netmask']);
         $number++;
      }
      $logger->debug("Number of networks defined in subnets: $number");
      for ($l=0;$l<$devices['counter'];$l++)
      {
         $take_into_account=true;
         if ($keys_to_delete)
         {
            foreach($keys_to_delete as $key_to_delete)
            {
               if ($key_to_delete==$l)
               {
                  $take_into_account=false;
               }
            }
         }
         if (!$take_into_account)
            continue;
         $candidate=0;
         for ($i=0;$i<$number;$i++)
         {
            $address=ip_to_bin($network[$i]['ip']);
            $net=bindec(str_pad(substr($address,0,$network[$i]['netmask']),32,0));
            $broadcast=bindec(str_pad(substr($address,0,$network[$i]['netmask']),32,1));
            $host=bindec(ip_to_bin($devices['ip'][$l]));
            if (($host>=$net)&&($host<$broadcast))
               $candidate++;
         }
         $there=0;
	 if (($candidate)&&($argc==1)) //This is an ip which is a good candidate to be scanned and no parameters present on the command line
         {
            $lastseen=$devices['lastseen'][$l];
            $diff=(int)date_diff($lastseen,$timestamp,$what_units_time);
            if ($diff<=$time_threshold)
            {
	       for ($k=0;$k<=$counter;$k++)
                  if ($devices['ip'][$l]==$list['ip'][$k])
                     $there++;
               if (!$there)
               {
                  $list['ip'][$counter]=$devices['ip'][$l];
		  $list['hostname'][$counter]=$devices['hostname'][$l];
                  $list['sid'][$counter]=$devices['sid'][$l];
                  $counter++;
               }
            }
          }
          else if (($candidate)&&($argc>1))
          {
            for ($k=0;$k<=$counter;$k++)
               if ($devices['ip'][$l]==$list['ip'][$k])
                  $there++;
            if (!$there)
            {
               $list['ip'][$counter]=$devices['ip'][$l];
               $list['hostname'][$counter]=$devices['hostname'][$l];
               $list['sid'][$counter]=$devices['sid'][$l];
               $counter++;
            }
          }
      }
      if (($argc==2)&&($flagscannow))
      {
         $temp_1=$devices['ip'];
         $temp_2=$list['ip'];
         if ((isset($temp_1))&&(is_array($temp_1)))
            if ((isset($temp_2))&&(is_array($temp_2)))
            {
               $notscanned=array_diff($temp_1,$temp_2);
               foreach($notscanned as $rejected_ip)
               {
                  $logger->debug($rejected_ip." doesn't meet criteria specified in subnets table\n");
                  logit($rejected_ip." doesn't meet criteria specified in subnets table\n");
		  log2db('info',$rejected_ip." doesn't meet criteria specified in subnets table");
               }
            }
      }
      $list['counter']=$counter;
      if (($counter==0)&&(!$flagscannow))
         check_and_abort("IPs didn't match criteria (check subnets or your port_scan.inc file).\n",0);
   }
   return($list);  
}

function date_diff($date1, $date2,$what) //Time difference between the timestamp from the database and the one generated by the script
{
   $time=time_diff($date1,$date2);
   if (($what<0)||($what>6))
      $what=2;
   for ($i=1;$i<=$what;$i++)
   {
      if (($i==1)||($i==2))	//Minutes and hours
         $time/=60;
      else if ($i==3)		//Days
         $time/=24;
      else if ($i==4)		//Weeks
         $time/=7;
      else if ($i==5)		//Months
	 $time/=4;
      else if ($i==6)		//Years
         $time/=12;
}
   return $time; //This time is not very accurate, it is just an approximation
}

function parse_scanfile($scan_file,$list)
{
   global $flagscannow,$logger;
   $timestamp=date('Y-m-d H:i:s');
   $info=array();
   if (file_exists($scan_file))
   {
      $file_loaded=file_get_contents(trim($scan_file));
      if (!$file_loaded)
         check_and_abort("Couldn't load contents of file $scan_file",0);
      if(preg_match("/<?xml version/i",$file_loaded))
      {
         $i=0;
         if (!($xml = @simplexml_load_string($file_loaded))) #Picking info from XML file
         {
            check_and_abort("There was a problem reading the file $scan_file",0);
         }
         if (!isset($xml->host))
         {
            for ($i=0;$i<$list['counter'];$i++)
            {
               $logger->debug($list['ip'][$i]." is down\n");
               logit($list['ip'][$i]." is down\n");
               log2db('info',$list['ip'][$i]." is down");
            }
            if ($flagscannow) //Lets set os=Unreachable to those devices which are present in the nac_hostscanned table
            {
               for ($i=0;$i<$list['counter'];$i++) //Grabs devices' ids
               {
                  if ($i==0)
                     $query="select id, r_ip, name from systems where ip='{$list['ip'][$i]}'";
                  else
                     $query.=" or ip='".$list['ip'][$i]."'";
               }
               $query.=" and r_timestamp>=DATE_SUB(NOW(),INTERVAL 3 HOUR);";
               $res=execute_query($query);
               if ($res)
                  while ($result=mysql_fetch_array($res, MYSQL_ASSOC)) //And do it
                  {
                     $query="update nac_hostscanned set os='Unreachable', timestamp=NOW(), ip='{$result['r_ip']}', hostname='{$result['name']}' where sid='{$result['id']}';";
                     execute_query($query);
                  }
            }
            check_and_abort("No host was up.\n",0);
         }
         foreach($xml->host as $host) 
         {
            $ip_info=$host->address[0];
	    $temp=$ip_info->attributes(); //Let's retrieve the attributes
            $info[$i]['sid']=$list['sid'][$i];
            if (isset($temp->addr))
               $info[$i]['ip']=(string)$temp->addr;         //IP address
            else $info[$i]['ip']="NULL";
	    $info[$i]['ip']=validate($info[$i]['ip']);
            $index=0;	
	    while (($info[$i]['ip']!=$list['ip'][$index])&&($index<$list['counter']))
		$index++;
            if (isset($host->hostnames->hostname))	
            {
               $host_info=$host->hostnames->hostname; 
               $temp=$host_info->attributes();
            }
            if (isset($temp->name))
               $info[$i]['hostname']=(string)$temp->name; 	//Hostname
            else $info[$i]['hostname']=$list['hostname'][$index];
	    $info[$i]['hostname']=validate($info[$i]['hostname']); 
            if (isset($host->os->osmatch))
            {
               $os_info=$host->os->osmatch;
               $temp=$os_info->attributes();
               if (isset($temp->name))
                  $info[$i]['os']=(string)$temp->name;			//OS
               else
                  $info[$i]['os']="NULL";
            }
            else $info[$i]['os']="NULL";
	    $info[$i]['os']=validate($info[$i]['os']);
            if (isset($host->ports))
            {
               $ports_info=$host->ports;
               $j=0;
               foreach($ports_info->port as $port)		//Ports
               {
	          $temp=$port->state->attributes();
                  if (!empty($temp)&&strcasecmp((string)$temp->state,"open")==0)
                  {
                     $temp=$port->attributes();
                     $info[$i]['port'][$j]['protocol']=(string)$temp->protocol;	#Protocol
		     $info[$i]['port'][$j]['protocol']=validate($info[$i]['port'][$j]['protocol']);
                     $info[$i]['port'][$j]['portid']=(string)$temp->portid;	#Service identifier
                     $info[$i]['port'][$j]['portid']=validate($info[$i]['port'][$j]['portid']);
		     $temp=$port->state->attributes();
                     $temp2=$port->service->attributes(); //Attributes to get the running service
                     if (@isset($temp2->name))		#Description
                        $name=(string)$temp2->name;
                     else $name="";
                     if (@isset($temp2->product))
                        $product=(string)$temp2->product;
                     else $product="";
                     if (isset($temp2->version))
                        $version=(string)$temp2->version;
                     else $version="";
                     $info[$i]['port'][$j]['description']=$name.":".$product." ".$version;
		     $info[$i]['port'][$j]['description']=validate($info[$i]['port'][$j]['description']);	
                     $j++;
                  }
               }
               $info[$i]['ports']=$j;
            }
            $i++;
         }
      }
      else check_and_abort("File $scan_file is not in XML format\n",0);
   }
   else check_and_abort("File $scan_file not found\n",0);
   $info['equipments']=$i;
   if (($flagscannow)||($logger->getDebugLevel()))
   {
      $temp="";
      for ($j=0;$j<$i;$j++)
      {
         $temp[$j]=$info[$j]['ip'];
         $logger->debug($temp[$j]." is up");
      }
      if (isset($temp)&&is_array($temp))
         if (isset($list)&&is_array($list))
            $hosts_down=array_diff($list['ip'],$temp);
      foreach($hosts_down as $down)
      {
         $logger->debug($down." is down\n");
         logit($down." is down\n");
         log2db('info',$down." is down");
      }
      for ($i=0;$i<$list['counter'];$i++) //It's like déjà vu
      {
         /*if ($i==0)
            $query="select id, r_ip, name from systems where r_ip='".$list['ip'][$i]."'";
         else
            $query.=" or ip='".$list['ip'][$i]."'";*/
         $query="update nac_hostscanned set os='Firewalled', timestamp=NOW(), ip='{$list['ip'][$i]}', hostname='{$list['hostname'][$i]}' where sid='{$list['sid'][$i]}';";
         execute_query($query);
      }
      /*$query.=" and r_timestamp>=DATE_SUB(NOW(),INTERVAL 3 HOUR);"; 
      $res=execute_query($query);
      while ($result=mysql_fetch_array($res, MYSQL_ASSOC)) //And do it
      {
            $query="update nac_hostscanned set os='Firewalled', timestamp=NOW(), ip='{$result['r_ip']}', hostname='{$result['name']}' where sid='{$result['id']}';";
         execute_query($query);
      }*/
   }
   $logger->debug("Total number of hosts up: ".$info['equipments']);
   return($info);
}
?>
