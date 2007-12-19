<?php

$sid = $row['id'];
$mac = $row['mac'];

$wsus_status = array("notinstalled" => "Not installed","downloaded" => "Already downloaded", "installedpendingreboot" => "Installed, pending reboot", "failed" => "Failed");

// 1.0 Missing general properties
$sel = "SELECT sys_class.value as class, sys_class2.value as class2,
 sys_os.value as os, sys_os1.value as os1, sys_os2.value as os2, sys_os3.value as os3  
 FROM systems
 LEFT JOIN sys_class ON systems.class = sys_class.id
 LEFT JOIN sys_class2 ON systems.class2 = sys_class2.id
 LEFT JOIN sys_os ON systems.os = sys_os.id
 LEFT JOIN sys_os1 ON systems.os1 = sys_os1.id
 LEFT JOIN sys_os2 ON systems.os2 = sys_os2.id
 LEFT JOIN sys_os3 ON systems.os3 = sys_os3.id
 WHERE systems.id = $sid;";
        $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
        if (mysql_num_rows($res) > 0) {
		$system = mysql_fetch_array($res);
	};
// 1.1 Microsoft WSUS
	$sel = "SELECT * FROM wsus_systems WHERE sid=$sid;";
	 $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
        if (mysql_num_rows($res) > 0) {
		$wsus = mysql_fetch_array($res);
		$sel = "SELECT title,msrcseverity FROM wsus_neededUpdates LEFT JOIN wsus_systemToUpdates ON wsus_systemToUpdates.localupdateid = wsus_neededUpdates.localupdateid WHERE wsus_systemToUpdates.sid=$sid ORDER BY msrcseverity ;";
		$res2 = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
	        if (mysql_num_rows($res2) > 0) {
			$wsus_count=0;
			while ($row2 = mysql_fetch_array($res2)) {
				$wsus_update[$wsus_count] = $row2;
				$wsus_count++;
			};
		};
	} else {
		$wsus = FALSE;
	};

// 1.2 McAfee EPO
//        $sel = "SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'";
	$sel = "SELECT * FROM epo_systems WHERE sid=$sid";
        $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
        if (mysql_num_rows($res) > 0) {
                $epo = mysql_fetch_array($res);
        } else {
                $epo = FALSE;
        };

// 1.3 Open ports
# TODO : get nmap_id out : not used anymore
        $nmap_id = get_nmap_id($sid);
        if ($nmap_id) {
                $scan = TRUE;
                $sel = "SELECT * FROM nac_hostscanned WHERE id = '$nmap_id';";
                $res = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
                // this query MUST give an answer otherwise nmap_id should be FALSE
                $nmap = mysql_fetch_array($res);

                $sel = "SELECT services.port AS port, services.description AS description, open.banner AS banner, protocols.name AS protocol, services.id as serviceid
FROM nac_openports open
LEFT JOIN services ON services.id = open.service
LEFT JOIN protocols ON protocols.protocol = services.protocol WHERE sid = '$sid';";

#               $sel = "SELECT nac_openports.port as port, nac_servicestcp.description as description, nac_openports.banner as `banner` FROM nac_openports
#                                                LEFT JOIN nac_servicestcp ON nac_openports.port = nac_servicestcp.port
#                                                WHERE host = '$nmap_id' and protocol='tcp' ;";
                $ports = mysql_query($sel) or die ("Unable to query MySQL ($sel)");
                if (mysql_num_rows($ports) > 0) {
                        while ($port = mysql_fetch_array($ports)) {
                                $openport[$port['serviceid']] = $port['port'];
                                $openport_descr[$port['serviceid']] = $port['description'];
                                $openport_banner[$port['serviceid']] = $port['banner'];
                                $openport_proto[$port['serviceid']] = $port['protocol'];
                        };
                } else {
                        $openport[0] = 'No open port found in last scan';
                };
        } else {
                $scan = FALSE;
        };


// then print
        $out .= "<table cellspacing=0 cellpadding=4 border=1>";

// 2.0 General properties
//        $out .= '<tr><td>Last seen<td>'.get_location($system['LastPort']).' ('.$system['PatchSocket'].')<td>'.$system['switch'].' '.$system['port']."\t";
	$out .= '<tr><td>Classification'."\n";
                $out .= '<td>'.(!is_null($system['class'])?$system['class']:'');
                $out .= '<td>'.(!is_null($system['class2'])?$system['class2']:'')."\n";
        $out .= '<tr><td>Operating System'."\n";
                $out .= '<td>'.(!is_null($system['os'])?$system['os']:'');
                $out .= ' '.(!is_null($system['os1'])?$system['os1']:'')."\n";
                $out .= '<td>'.(!is_null($system['os2'])?$system['os2']:'');
                $out .= ' '.(!is_null($system['os3'])?$system['os3']:'')."\n";

// 2.1 Microsoft WSUS
	if ($wsus) {
                $out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>Microsoft WSUS</b>';
		$out .= '<tr><td>Host<td>'.$wsus['hostname'].'<td>IP '.$wsus['ip'];
		$out .= '<tr><td>OS<td>'.$wsus['os'];
		$out .= '<tr><td>Hardware<td>'.$wsus['computermake'].'<td>'.$wsus['computermodel'];
		$out .= '<tr><td>Last contact<td>'.$wsus['lastwsuscontact'];
/*
		$out .= '<tr><td colspan=2>Updates';
		foreach ($wsus_status as $col => $descr) {
			$out .= '<tr><td><td>'.$descr.'<td>'.$wsus[$col];
			$wsus_count = $wsus_count + $wsus[$col];
		};
		$out .= '<tr><td><td>Total<td>'.$wsus_count;
*/
		$out .= '<tr><td>Number of missing updates<td>'.$wsus_count;
		$out .= '<tr><td>Descriptions<td colspan=2><ul>';
		foreach ($wsus_update as $i => $update) {
				$out .= '<li>'.$update['title'].' ('.$update['msrcseverity'].')';
		};
		$out .= '</ul>';


	};

// 2.2 McAfee ePO
        if ($epo) {
                $out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>McAfee (ePO)</b>';
                $out .= '<tr><td>Username<td>'.$epo['username'];
                $out .= '<tr><td>SMB<td>\\\\'.$epo['domainname'].'<td>\\'.$epo['nodename'];
                $out .= '<tr><td>IP<td>'.$epo['ip'];//.'<td>'.$epo['IPAddress'];
                $out .= '<tr><td>OS<td>'.$epo['ostype'].' ('.$epo['osversion']. ')<td>'.$epo['osservicepackver'].' '.' (Build '.$epo['osbuildnum']. ')';
                $out .= '<tr><td colspan=3 bgcolor="#EEEEEE"><b>McAfee Updates</b>';
//                $out .= '<tr align=center><td>Part<td>Version<td>Last update';
                $out .= '<tr><td>Agent<td>'.$epo['agentversion'].'<td>'.$epo['lastepocontact'];
		$out .= '<tr><td>Antivirus<td>'.$epo['virusver'].'<td>&nbsp;';
		$out .= '<tr><td>Engine<td>'.$epo['virusenginever'].'<td>Hotfix '.$epo['virushotfix'];
                $out .= '<tr><td>DAT File<td>'.$epo['virusdatver'].'<td>&nbsp;';
        };
// 2.3 SCAN
        if ($scan) {
                $out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>Port scanning</b>';
                $out .= '<tr><td>Time/date of scan<td colspan=2>'.$nmap['timestamp'];
                $out .= '<tr><td>OS Detection<td colspan=2>'.$nmap['os'];
                $out .= '<tr><td colspan=3 bgcolor="#EEEEEE"><b>TCP</b>';
                        foreach($openport as $num => $port) {
                                $sdescr = explode(',',$openport_descr[$num]);
                                $banner = $openport_banner[$num];
                                $out .= '<tr><td>'.$port.'/'.strtolower($openport_proto[$num]);
                                $out .= '<td>'.$sdescr[0].'<td>'.$openport_banner[$num];
                        };
        };



echo $out;
?>
