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
$sel['class']['table'] = "SELECT cl.value as class, c2.value as subclass, count(*) as count FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value, c2.value";
$sel['class']['graph'] = "SELECT cl.value as datax, count(*) as count FROM systems s, sys_class cl, sys_class2 c2 WHERE s.class=cl.id AND s.class2=c2.id GROUP BY cl.value";


$sel['os']['table'] = "SELECT o.value, os1, os3, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value, os1, os3";
$sel['os']['graph'] = "SELECT o.value as datax, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value";


$sel['switch']['table'] = "SELECT sw.name,count(*) as count FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name";
$sel['switch']['graph'] = "SELECT sw.name as datax, count(*) as count FROM systems s, switch sw WHERE s.switch=sw.ip GROUP BY sw.name;";


$sel['vlan']['table'] = "SELECT s.vlan as ID, v.value as name, count(*) as count FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";
$sel['vlan']['graph'] = "SELECT v.value as datax, count(*) as count FROM systems s, vlan v WHERE s.vlan=v.id GROUP BY s.vlan";


$sel['dat']['table'] = "SELECT DATversion, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";
$sel['dat']['graph'] = "SELECT DATversion as datax, count(*) as count FROM EpoComputerProperties GROUP BY DATversion";

?>
