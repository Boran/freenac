<?php
#
#  functions.inc
#
#  2006.05.25/Sean Boran: Production
#  2006.01.24/Thomas Dagonnier: First prototype
#
#  Copyright (C) 2006 Swisscom
#  Licensed under GPL, see LICENSE file or http://www.gnu.org/licenses/gpl.html
####################################
// some generic definitions


$sel['class']['table'] = "SELECT cl.value as class, c2.value as subclass, count(*) as count FROM systems s
                                LEFT JOIN sys_class cl ON s.class=cl.id
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY cl.value, c2.value";
$sel['class']['graph'] = "SELECT cl.value as datax, count(*) as count  FROM systems s
                                LEFT JOIN sys_class cl ON s.class=cl.id
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY cl.value";


$sel['class2']['table'] = "SELECT c2.value as class2, count(*) as count FROM systems s
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY c2.value";
$sel['class2']['graph'] = "SELECT c2.value as datax, count(*) as count  FROM systems s
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY c2.value";


#$sel['os']['table'] = "SELECT o.value, os1, os3, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value, os1, os3";
#$sel['os']['graph'] = "SELECT o.value as datax, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value";

$sel['os']['table'] = "SELECT o.value as os, o1.value as os1, o2.value as os2, o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os o ON o.id = s.os
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o.value";

$sel['os']['graph'] = "SELECT o.value as datax, count(*) as count FROM systems s LEFT JOIN sys_os o ON o.id = s.os GROUP BY o.value";

$sel['os1']['table'] = "SELECT o1.value as os1, o2.value as os2, o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o1.value";

$sel['os1']['graph'] = "SELECT o1.value as datax, count(*) as count FROM systems s LEFT JOIN sys_os1 o1 ON o1.id = s.os1 GROUP BY o1.value";


$sel['os2']['table'] = "SELECT o2.value as os2, o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o2.value";

$sel['os2']['graph'] = "SELECT o2.value as datax, count(*) as count FROM systems s LEFT JOIN sys_os2 o2 ON o2.id = s.os2 GROUP BY o2.value";

$sel['os3']['table'] = "SELECT o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os o ON o.id = s.os
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o3.value";

$sel['os3']['graph'] = "SELECT o3.value as datax, count(*) as count FROM systems s LEFT JOIN sys_os2 o3 ON o3.id = s.os3 GROUP BY o3.value";


$sel['switch']['table'] = "SELECT switch.name, count(*) as count FROM systems 
                            LEFT JOIN port ON systems.LastPort = port.id
                            LEFT JOIN switch ON port.switch = switch.id
                         GROUP BY switch.name";

$sel['switch']['graph'] = "SELECT switch.name as datax, count(*) as count FROM systems 
                            LEFT JOIN port ON systems.LastPort = port.id
                            LEFT JOIN switch ON port.switch = switch.id
                         GROUP BY switch.name";


$sel['vlan']['table'] = "SELECT v.default_id as VLAN_ID, v.default_name as VLAN_Name, count(*) as count, v.vlan_description as description FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY s.vlan";
$sel['vlan']['graph'] = "SELECT v.default_name as datax, count(*) as count FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY s.vlan";

$sel['vlan_group']['table'] = "SELECT v.vlan_group as VLAN_Group, v.default_name, count(*) as count FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY v.vlan_group";
$sel['vlan_group']['graph'] = "SELECT v.vlan_group as datax, count(*) as count FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY v.vlan_group";




$sel['dat']['table'] = "SELECT DATversion, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";
$sel['dat']['graph'] = "SELECT DATversion as datax, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";

?>
