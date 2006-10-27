<?php


// this function will output a (formatted) table with
// all informations known about a specific host host

function print_host($mac) {

// 1. query all data
// 1.1. General Properties
//	$sel = "SELECT * FROM systems WHERE mac = '$mac';";
	$sel = "SELECT inventar, s.mac as mac, s.vlan, v.value as vlanname, stat.value as statusname, s.status,
  s.name as ComputerName, s.description as user, s.comment, s.ChangeDate, LastSeen, building,
  office as officeSoll, p.location as officeIst,
  pat.von_dose as PatchSocket,pat.von_office as PatchCable,
  port, sw.name as switch,
  class, cl.value as classname,
  class2, c2.value as class2name, os, o.value as osname, os1, os2, os3 FROM systems s, vlan v, vstatus stat, sys_class cl, sys_class2 c2, sys_os o, switch sw, port p, patchcable pat WHERE s.vlan=v.id AND s.status=stat.id AND s.class=cl.id AND s.class2=c2.id AND s.os=o.id
  AND s.switch=sw.ip AND (s.switch=p.switch AND s.port=p.name) AND (s.port=pat.nach_port AND s.switch=pat.nach_switch) AND s.mac='$mac'";
	$system = mysql_fetch_array(mysql_query($sel));

// 1.2 McAfee EPO
	$sel = "SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'";
	$res = mysql_query($sel);
	if (mysql_num_rows($res) > 0) {
		$epo = mysql_fetch_array($res);
	} else {
		$epo = FALSE;
	};

// 1.3 Open ports
	$nmap_id = get_nmap_id($mac);
	if ($nmap_id) {
		$scan = TRUE;
		$sel = "SELECT * FROM nac_hostscanned WHERE id = '$nmap_id';";
		$nmap = mysql_fetch_array(mysql_query($sel));
	
		$sel = "SELECT nac_openports.port as port, nac_servicestcp.description as description, nac_openports.banner as `banner` FROM nac_openports 
						 LEFT JOIN nac_servicestcp ON nac_openports.port = nac_servicestcp.port 
						 WHERE host = '$nmap_id' and protocol='tcp' ;";
		$ports = mysql_query($sel);
		if (mysql_num_rows($ports) > 0) {
			while ($port = mysql_fetch_array($ports)) {
				$tcp_port[$port['port']] = $port['description'];
				$tcp_banner[$port['port']] = $port['banner'];
			};
		} else {
			$tcp_port[0] = 'No open TCP port scanned';
		};
		
		$sel = "SELECT nac_openports.port as port, nac_servicesudp.description as description, nac_openports.banner as `banner` FROM nac_openports 
						 LEFT JOIN nac_servicesudp ON nac_openports.port = nac_servicesudp.port 
						 WHERE host = '$nmap_id' and protocol='udp' ;";
		$ports = mysql_query($sel);
		if (mysql_num_rows($ports) > 0) {
			while ($port = mysql_fetch_array($ports)) {
				$udp_port[$port['port']] = $port['description'];
				$udp_banner[$port['port']] = $port['banner'];
			};
		} else {
			$udp_port[0] = 'No open UDP port scanned';
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
	$out .= '<tr><td>Home location<td>'.$system['building'].' '.$system['officeSoll'];
	$out .= '<tr><td>Last seen<td>'.$system['officeIst'].' ('.$system['PatchSocket'].')<td>'.$system['switch'].' '.$system['port']."\t";
 
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
			foreach($tcp_port as $num => $descr) {
				$sdescr = explode(',',$descr);
				$banner = $tcp_banner[$num];
				$out .= '<tr><td>'.$num.'<td>'.$sdescr[0].'<td>'.$banner;
			};

			foreach($udp_port as $num => $descr) {
				$sdescr = explode(',',$descr);
				$banner = $udp_banner[$num];
				$out .= '<tr><td>'.$num.'<td>'.$sdescr[0].'<td>'.$banner;
			};
	};
	

/* UGLY
// meta-things
		$out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>Meta-informations</b>';
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
//		echo $query.'<br>';
		$mysql_res = mysql_query($query);
		if (mysql_num_rows($mysql_res) > 0) {
			$html .= "<table cellspacing=0 cellpadding=5 border=1>\n";
			$html .= "<tr><th>OS<td>Nmap<td>ePO<th>Hostname<th>Owner<th>Inventar<th>MAC<th>Last IP<th>VLAN<th>Standard<br>location<th colspan=2>LastSeen\n";
			while ($row = mysql_fetch_array($mysql_res,MYSQL_ASSOC)) {
				$html .= '<tr bgcolor="'.get_vlan_color($row['vlan']).'">';
				$html .= '<td align=center><img src="os/'.$row['os'].'.gif" border=0>';
// extra details
				$mac = $row['mac'];
				$html .= '<td align=center>';
				if (get_nmap_id($mac)) { $html .= 'X'; } else { $html .= '&nbsp;' };;

				$html .= '<td align=center>';
				if (mysql_num_rows(mysql_query("SELECT * FROM EpoComputerProperties WHERE NetAddress = '$mac'")) > 0) { $html .= 'X'; };
				
// name => print details link
				$html .= '<td><b><a href="display.php?single_host='.$row['mac'].'">';
				$html .= $row['name'].'</a></b>';
// owner => email link
				$html .= '<td>';
				  if ($row['description']) {
					$html .= '<a href="mailto:'.get_user_email($row['description']).'" ';
					$html .= 'title="'.user_tooltip($row['description']).'" ';
					//$html .= '>'.$row['description']."</a>\n";
					$html .= '>'.get_user_name($row['description'])."</a>\n";
				  } else {
					$html .= '<i>Unknown</i>';
				  };
				$html .= '<td>'.$row['inventar'].'&nbsp;';
				$html .= '<td>'.$row['mac'].'&nbsp;';
				$html .= '<td>'.$row['r_ip'].'&nbsp;';
				$html .= '<td>'.get_vlan_descr($row['vlan']); // get_vlan_short(get_vlan_descr
					if (! $row['building']) { $row['building'] = 'Ber-Omu93' ; };
				$html .= '<td>'.$row['building'].'&nbsp;'.$row['office'];
				$html .= '<td>'.get_location($row['switch'],$row['port']).'&nbsp;<td>'.$row['LastSeen'].'</font>'.'&nbsp;';
				$html .= "\n";
			};
			$html .= "</table>\n";
		} else {
			$html .= '<i>No record found</i>';
		};
	return($html);
};


?>
