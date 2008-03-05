<?php


// this function will output a (formatted) table with
// all informations known about a specific host host

function print_host($mac) {
	global $big_sel;
// 1. query all data
// 1.1. General Properties
//	$sel = "SELECT * FROM systems WHERE mac = '$mac';";
	$sel = $big_sel." WHERE s.mac='$mac'";
	$system = mysql_fetch_array(mysql_query($sel));
	$sid = $system['sid'];

// 1.2 McAfee EPO
	$sel = "SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'";
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


#		$sel = "SELECT nac_openports.port as port, nac_servicestcp.description as description, nac_openports.banner as `banner` FROM nac_openports 
#						 LEFT JOIN nac_servicestcp ON nac_openports.port = nac_servicestcp.port 
#						 WHERE host = '$nmap_id' and protocol='tcp' ;";
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

// 2.1 General properties
	$out .= "<tr><td colspan=3 align=center bgcolor=\"#CCCCCC\"><font size=+1><b>".$system['ComputerName'];
	$out .= '<tr><td>Class<td>'.$system['classname'].'<td>'.$system['class2name'];
	$out .= '<tr><td>OS<td>'.$system['osname'].'<td>'.$system['os1'].' '.$system['os2'].' '.$system['os3'];
	$out .= '<tr><td>Adresses<td>'.$system['mac'].'<td>'.$system['r_ip'];
	$out .= '<tr><td>Home location<td>'.$system['officeSoll'];
	$out .= '<tr><td>Last seen<td>'.get_location($system['LastPort']).' ('.$system['PatchSocket'].')<td>'.$system['switch'].' '.$system['port']."\t";
 
// 2.2 McAfee ePO
	if ($epo) {
		$out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>McAfee (ePO)</b>';
		$out .= '<tr><td>Username<td>'.$epo['UserName'];
		$out .= '<tr><td>SMB<td>\\\\'.$epo['DomainName'].'<td>\\'.$epo['ComputerName'];
		$out .= '<tr><td>IP<td>'.$epo['IPHostName'].'<td>'.$epo['IPAddress'];
		$out .= '<tr><td>OS<td>'.$epo['OSType'].' ('.$epo['OSVersion']. ')<td>'.$epo['OSServicePackVer'].'';
		$out .= '<tr><td colspan=3 bgcolor="#EEEEEE"><b>McAfee Updates</b>';
		$out .= '<tr align=center><td>Part<td>Version<td>Last update';
		$out .= '<tr><td>Agent<td>'.$epo['AgentVersion'].'<td>'.$epo['LastUpdate'];
		$out .= '<tr><td>DAT File<td>'.$epo['DATVersion'].'&nbsp;<td>'.$epo['LastDATUpdate'];
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
	

/* UGLY
// meta-things
		$out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>Meta information</b>';
		$out .= '<tr><th colspan=3>Operating System';
		$out .= '<tr><td>Inventory<td>'.$system['class'].'<td>'.$system['os1'];
		$out .= '<tr><td>McAfee ePO<td>';
		if ($epo) { $out .= $epo['OSType'].' ('.$epo['OSVersion']. ')<td>'.$epo['OSServicePackVer'].'';
		} else { $out .= 'N/A'; };
		$out .= '<tr><td>Inventory<td colspan=2>';
		if ($scan) {
					$out .= get_nmap_os($nmap_id);
		} else { $out .= 'N/A'; };
*/
	$out .= "</table>";
	return($out);
};


function print_host_table($where) {
		$query = "SELECT * FROM systems WHERE $where ;";
		$mysql_res = mysql_query($query) or die ("Unable to query MySQL ($query)\n");
		if (mysql_num_rows($mysql_res) > 0) {
			$html .= "<table cellspacing=0 cellpadding=5 border=1>\n";
			$html .= "<tr><th>OS<td>Nmap<td>ePO<th>Hostname<th>Owner<th>Inventory<th>MAC<th>Last IP<th>VLAN<th>Standard<br>location<th colspan=2>LastSeen\n";
			while ($row = mysql_fetch_array($mysql_res,MYSQL_ASSOC)) {
				$html .= '<tr bgcolor="'.get_vlan_color($row['vlan']).'">';
				$html .= '<td align=center><img src="../images/'.$row['os'].'.gif" border=0>';
// extra details
				$mac = $row['mac'];
				$sid = $row['id'];
				$html .= '<td align=center>';
				if (get_nmap_id($sid)) { $html .= 'X'; } else { $html .= '&nbsp;'; };

				$html .= '<td align=center>';

                                $sel_epo = "SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'";
				$res_epo = mysql_query($sel_epo) or die ("Unable to query MySQL ($sel_epo)");
				if (mysql_num_rows($res_epo) > 0) { $html .= 'X'; } else { $html .= '&nbsp;'; };
				
// name => print details link
				$html .= '<td><b><a href="display.php?single_host='.$row['mac'].'">';
				$html .= $row['name'].'</a></b>';
// owner => email link
				$html .= '<td>';
				  if ($row['uid']) {
					$html .= '<a href="mailto:'.get_user_email($row['uid']).'" ';
					$html .= 'title="'.user_tooltip($row['uid']).'" ';
					//$html .= '>'.$row['uid']."</a>\n";
					$html .= '>'.get_user_name($row['uid'])."</a>\n";
				  } else {
					$html .= '<i>Unknown</i>';
				  };
				$html .= '<td>'.$row['inventory'].'&nbsp;';
				$html .= '<td>'.$row['mac'].'&nbsp;';
				$html .= '<td>'.$row['r_ip'].'&nbsp;';
				$html .= '<td>'.get_vlan_descr($row['vlan']).'&nbsp;'; // get_vlan_short(get_vlan_descr
				$html .= '<td>'.print_location($row['office']);
				$html .= '<td>'.get_location($row['LastPort']).'&nbsp;<td>'.$row['LastSeen'].'</font>'.'&nbsp;';
				$html .= "\n";
			};
			$html .= "</table>\n";
		} else {
			$html .= '<i>No record found</i>';
		};
	return($html);
};


?>
