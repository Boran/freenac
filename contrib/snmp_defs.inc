<?php

$snmp_sw['descr'] =             'SNMPv2-MIB::sysDescr.0';
$snmp_sw['name'] =              'SNMPv2-MIB::sysName.0';
$snmp_sw['location'] =          'SNMPv2-MIB::sysLocation.0';
$snmp_sw['contact'] =           'SNMPv2-MIB::sysContact.0';

$snmp_ifaces =                  '1.3.6.1.2.1.31.1.1.1.1';
$snmp_cisco['hw'] =             'SNMPv2-SMI::mib-2.47.1.1.1.1.13';

$snmp_if['name'] =              '1.3.6.1.2.1.31.1.1.1.1';
$snmp_if['highspeed'] =         '1.3.6.1.2.1.31.1.1.1.15';              //      10 - 100 - 1000 
$snmp_if['description'] =       '1.3.6.1.2.1.31.1.1.1.18';
$snmp_if['phys'] =              '1.3.6.1.2.1.31.1.1.1.17';              // true - false
$snmp_if['trunk'] =             '1.3.6.1.4.1.9.9.46.1.6.1.1.13';        // 1 : on - 2 : off - 3 : desirable - 4 : auto - 5 : onNoNegotiate
$snmp_if['vlan'] =              '1.3.6.1.4.1.9.9.68.1.2.2.1.2';

function is_port_vmps($myiface) {
        if (($myiface['phys'] == 1) && ($myiface['trunk'] != 1) &&
                                ((stristr($myiface['name'],'Fa') || stristr($myiface['name'],'Gi'))))
        {
                return(TRUE);
        } else {
                return(FALSE);
        };


};

function walk_ports($switch,$snmp_ro) {
        global $snmp_ifaces; // query to get all interfaces
        global $snmp_if; // sub-queries with interfaces characteristics

        $ifaces = snmprealwalk($switch,$snmp_ro,$snmp_ifaces);


                foreach ($ifaces as $oid => $name) {
                                $oids = explode('.',$oid);
                                $idx = $oids[12];
				if ($idx > 0) {
	                                $iface[$id]['id'] = $idx;
        	                        $index[] = $idx;
				};
                };
                unset($idx);

                foreach ($snmp_if as $field => $query) {
                        foreach($index as $idx) {
                                $iface[$idx][$field] = '';
                        };
                        $walk = snmprealwalk($switch,$snmp_ro,$query);
                        foreach ($walk as $oid => $value) {
                                $oids = explode('.',$oid);
                                $idx = $oids[count($oids)-1];
                                $iface[$idx][$field] = $value;
                        };
                        unset($walk);

                };

                foreach ($iface as $idx => $myiface) {
                        $iface[$idx]['vmps'] = is_port_vmps($myiface);
                };

        return($iface);
};

?>
