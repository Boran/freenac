<?php
/**
 *
 * graphdefs.inc
 *
 * Long description for file:
 *
 * @package     FreeNAC
 * @author      FreeNAC Core Team
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id
 * @link        http://freenac.net
 *
 */


#$sel['class']['table'] = "SELECT cl.value as class, c2.value as subclass, count(*) as count FROM systems s
#                                LEFT JOIN sys_class cl ON s.class=cl.id
#                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY cl.value, c2.value";
#$sel['class']['graph'] = "SELECT cl.value as datax, count(*) as count  FROM systems s
#                                LEFT JOIN sys_class cl ON s.class=cl.id
#                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY cl.value";
# (IFNULL(c2.value,'unknown'))
$sel['class']['table'] = "SELECT (IFNULL(c2.value,'unspecified')) as 'Device per class', count(*) as count FROM systems s
                                LEFT JOIN sys_class c2  ON s.class=c2.id GROUP BY c2.value";
$sel['class']['graph'] = "SELECT (IFNULL(c2.value,'unspecified')) as datax, count(*) as count  FROM systems s
                                LEFT JOIN sys_class c2  ON s.class2=c2.id GROUP BY c2.value";


#$sel['class2']['table'] = "SELECT c2.value as class2, count(*) as count FROM systems s
#                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY c2.value";
$sel['class2']['table'] = "SELECT (IFNULL(c2.value,'unknown')) as 'Device per class2', count(*) as count FROM systems s
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY c2.value";
$sel['class2']['graph'] = "SELECT (IFNULL(c2.value,'unknown')) as datax, count(*) as count  FROM systems s
                                LEFT JOIN sys_class2 c2  ON s.class2=c2.id GROUP BY c2.value";


#$sel['os']['table'] = "SELECT o.value, os1, os3, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value, os1, os3";
#$sel['os']['graph'] = "SELECT o.value as datax, count(*) as count FROM systems s, sys_os o WHERE s.os=o.id GROUP BY o.value";

$sel['os']['table'] = "SELECT o.value as Os1, o2.value as Os2, o3.value as Os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os o ON o.id = s.os
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o.value";

$sel['os']['graph'] = "SELECT (IFNULL(o.value,'unspecified')) as datax, count(*) as count FROM systems s LEFT JOIN sys_os o ON o.id = s.os GROUP BY o.value";

$sel['os1']['table'] = "SELECT o1.value as Os1, o2.value as Os2, o3.value as Os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o1.value";

$sel['os1']['graph'] = "SELECT (IFNULL(o1.value,'unspecified')) as datax, count(*) as count FROM systems s LEFT JOIN sys_os1 o1 ON o1.id = s.os1 GROUP BY o1.value";


$sel['os2']['table'] = "SELECT o2.value as os2, o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o2.value";

$sel['os2']['graph'] = "SELECT (IFNULL(o2.value,'unspecified')) as datax, count(*) as count FROM systems s LEFT JOIN sys_os2 o2 ON o2.id = s.os2 GROUP BY o2.value";

$sel['os3']['table'] = "SELECT o3.value as os3, count(*) as count FROM systems s
                            LEFT JOIN sys_os o ON o.id = s.os
                            LEFT JOIN sys_os1 o1 ON o1.id = s.os1
                            LEFT JOIN sys_os2 o2 ON o2.id = s.os2
                            LEFT JOIN sys_os3 o3 ON o3.id = s.os3
                         GROUP BY o3.value";

$sel['os3']['graph'] = "SELECT (IFNULL(o3.value,'unspecified')) as datax, count(*) as count FROM systems s LEFT JOIN sys_os2 o3 ON o3.id = s.os3 GROUP BY o3.value";



#$sel['switch']['table'] = "SELECT (IFNULL(switch.name,'unspecified')) as Switch, count(*) as count FROM systems 
#                            LEFT JOIN port ON systems.LastPort = port.id
#                            LEFT JOIN switch ON port.switch = switch.id
#                         GROUP BY switch.name";
#$sel['switch']['graph'] = "SELECT switch.name as datax, count(*) as count FROM systems 
#                            LEFT JOIN port ON systems.LastPort = port.id
#                            LEFT JOIN switch ON port.switch = switch.id
#                         GROUP BY switch.name";
## Count Switches /ports used last 30 days
$sel['switch']['table'] = "SELECT  switch.name AS 'Switches with ports used last 30 days', count(*) as count
       FROM port LEFT JOIN switch ON port.switch = switch.id      
       WHERE (TO_DAYS(last_activity)>=TO_DAYS(CURDATE())-30)           
       GROUP BY switch.name";
$sel['switch']['graph'] = "SELECT  switch.name AS datax, count(*) as count
       FROM port LEFT JOIN switch ON port.switch = switch.id      
       WHERE (TO_DAYS(last_activity)>=TO_DAYS(CURDATE())-30)           
       GROUP BY switch.name";



$sel['vlan']['table'] = "SELECT v.default_name as 'Vlan name', 
   count(*) as 'End-Device Count last 30 days', v.vlan_description as 'Vlan Description' , v.default_id as 'Vlan Number' 
   FROM systems s LEFT JOIN vlan v ON s.vlan=v.id 
   WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-30)           
   GROUP BY v.default_name";
$sel['vlan']['graph'] = "SELECT IFNULL(v.default_name,'unspecified') as datax, count(*) as count 
   FROM systems s LEFT JOIN vlan v ON s.vlan=v.id 
   WHERE (TO_DAYS(LastSeen)>=TO_DAYS(CURDATE())-30)           
   GROUP BY v.default_name";



$sel['vlan_group']['table'] = "SELECT v.vlan_group as VLAN_Group, v.default_name, count(*) as count FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY v.vlan_group";
$sel['vlan_group']['graph'] = "SELECT v.vlan_group as datax, count(*) as count FROM systems s LEFT JOIN vlan v ON s.vlan=v.id GROUP BY v.vlan_group";




$sel['dat']['table'] = "SELECT virusdatver AS 'McAfee AV Dat-file version', count(*) as count FROM epo_systems GROUP BY virusdatver";
$sel['dat']['graph'] = "SELECT virusdatver AS datax, count(*) as count FROM epo_systems GROUP BY virusdatver";



$sel['wsus1']['table'] = "SELECT hostname AS 'End device with Windows update problems', 
    failed AS 'Number of errors' 
    from wsus_systems WHERE failed>0 ORDER BY failed DESC";
$sel['wsus1']['graph'] = "SELECT hostname as datax ,failed as count 
    from wsus_systems WHERE failed>0 ORDER BY failed DESC";
#    from wsus_systems WHERE failed>0";


?>
