<?php


// this function will output a (formatted) table with
// all informations known about this host
function print_host($mac) {

// 1. query all data
// 1.1. General Properties
	$sel = "SELECT * FROM systems WHERE mac = '$mac';";
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
// 2.1 General
	$out .= "<table cellspacing=0 cellpadding=4 border=1>";
	$out .= "<tr><td colspan=3 align=center bgcolor=\"#CCCCCC\"><font size=+1><b>".$system['name'];
	$out .= '<tr><td>Type<td>'.$system['class'].'<td>'.$system['os1'];
	$out .= '<tr><td>Adresses<td>'.$system['mac'].'<td>'.$system['r_ip'];
	$out .= '<tr><td>Location<td>'.$system['building'].' '.$system['office'];
	$out .= '<tr><td>Lastseen<td>'.get_location($system['switch'],$system['port']).'<td>'.get_switch_name($system['switch']).' '.$system['port'];
 
// 2.2 McAfee ePO
	if ($epo) {
		$out .= '<tr><td colspan=3 bgcolor="#DEDEDE"><b>McAfee (ePO)</b>';
		$out .= '<tr><td>Username<td>'.$epo['UserName'];
		$out .= '<tr><td>SMB<td>\\\\'.$epo['DomainName'].'<td>\\'.$epo['ComputerName'];
		$out .= '<tr><td>IP<td>'.$epo['IPHostName'].'<td>'.$epo['IPAddress'];
		$out .= '<tr><td>OS<td>'.$epo['OSType'].' ('.$epo['OSVersion']. ')<td>'.$epo['OSServicePackVer'].'';
		$out .= '<tr><td>LastUpdate<td>'.$epo['LastUpdate'];
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
?>
