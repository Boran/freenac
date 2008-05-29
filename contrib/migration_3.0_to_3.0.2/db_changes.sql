--- Upgrade to 3.02 ---

-- A new grant
GRANT SELECT, INSERT, UPDATE, DELETE on opennac.users to inventwrite@localhost;

--- Get rid of unnecessary status ---
DELETE FROM vstatus WHERE id='2';

--Adding a unique key to the config table
ALTER TABLE config ADD UNIQUE (name);

--- Modifications to the switch table ---
alter table switch modify column location int(11) default '1';
alter table switch modify column comment varchar(50) default 'NULL';
alter table switch modify column ap tinyint(4) default '0';
alter table switch modify column scan tinyint(1) default '0';
alter table switch modify column vlan_id int(11) default '0';
alter table switch add column scan3 tinyint(1) default '0';
--- Modifications to the port table ---
alter table port modify column default_vlan int(11) default '0';
alter table port modify column last_vlan int(11) default '0';
alter table port modify column auth_profile int(11) unsigned default '0';
alter table port modify column restart_now int(11) default '0';
alter table port modify column shutdown int(11) default '0';

-- New configuration entries in the config table
INSERT INTO config SET type='string', name='default_policy', value='BasicPolicy', COMMENT='Policy to load' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET TYPE='boolean', NAME='router_mac_ip_update_from_nmb', VALUE='false', COMMENT='Auto-update system names from WINS for \'unknowns\'? Note: DNS has still priority';
INSERT INTO `config` SET TYPE='boolean', NAME='enable_layer3_switches', VALUE='true', COMMENT='Query switches on layer 3 with router_mac_ip, if their scan3 flag is set';
INSERT INTO `config` SET TYPE='string', NAME='gui_disable_ports_list', VALUE='reserved,forbidden', COMMENT='GUI: disable editing ports with a comment containing one of these comma separated values';
INSERT INTO `guirights` SET code='4', value='helpdesk';
INSERT INTO config SET type='boolean', name='router_mac_ip_discoverall', value='false', comment='Discover/document all MAC/IPs found or only those already in the DB?' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='boolean', name='scan_unmanaged', value='false', comment='Should port_scan scan unmanaged systems?' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='integer', name='scan_hours_for_ip', value='3', comment='Number of hours for an IP address to be considered as up-to-date' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='boolean', name='vm_lan_like_host', value='false', comment='If VM, assign the same VLAN as its host?' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='boolean', name='wsus_enabled', value='false', COMMENT='Enable the WSUS module' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='string', name='wsus_dbalias', COMMENT='DNS name' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='string', name='wsus_db', COMMENT='DB instance' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='boolean', name='epo_enabled', value='false', COMMENT='Enable antivirus checking' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='string', name='epo_dbalias', COMMENT='DNS name' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='string', name='epo_db', COMMENT='DB instance' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='boolean', name='restart_daemons', value='false', COMMENT='Restart master daemons?' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO config SET type='integer', name='delete_not_seen', value='0', comment='Delete systems not seen for XX months' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='boolean', NAME='dhcp_enabled', VALUE='false', COMMENT='Enable DHCP management' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dhcp_configfile', VALUE='/etc/dhcp/dhcpd.conf.freenac', COMMENT='DHCP Configuration file (will be overwritten)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dhcp_default', VALUE='default-lease-time 36000
                max-lease-time 72000;
                authoritative;
                use-host-decl-names on;
                ddns-update-style ad-hoc;', COMMENT='Default DHCP settings'  ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='boolean', NAME='dns_enabled', VALUE='false', COMMENT='Enable DNS management' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_config', VALUE='file', COMMENT='DNS Management : \'file\' (generate zone files or \'update\' (send Dynamic DNS updates)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_domain', VALUE='domain.com', COMMENT='DNS Full Qualified Domain Name (top zone)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_outdir', VALUE = '/var/named/pri', COMMENT='DNS Configuration directory' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_subnets', VALUE='192.168.0,192.168.1', COMMENT='List of subnet for reverse DNS' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_ns', VALUE='ns1,ns2', COMMENT='DNS Name servers' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_mx', VALUE='mx1,mx2', COMMENT='DNS Mail servers (listed by priority)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_primary', VALUE='mydns.somewhereelse.com', COMMENT='Primary name server (SOA)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='dns_mail', VALUE='dnsadmin.domain.com', COMMENT='Email adress (SOA)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='ddns_server', VALUE='192.168.0.1', COMMENT='Primary server for DDNS updates' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='integer', NAME='ddns_ttl', VALUE='86400', COMMENT='TTL for dynamic dns updates' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='integer', NAME='ddns_level', VALUE='1', COMMENT='Level of DNS updates : 0 = all hosts, 1 = static ip & unmanaged hosts, 2 = all hosts' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='integer', NAME='web_lastdays', VALUE='14', COMMENT='Show devices seen in the last XX days' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='web_jpgraph', VALUE='/usr/share/jpgraph', COMMENT='Include path for the jpgraph library' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='web_dotcmd', VALUE='/usr/bin/neato', COMMENT='Graphviz binary to use (cf man dot for choices)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='boolean', NAME='web_showdhcp', VALUE='false', COMMENT='Show DHCP configuration-related fields' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='boolean', NAME='web_showdns', VALUE='false', COMMENT='Show DNS configuration-related fields' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='web_logtail_file', VALUE='/var/log/messages', COMMENT='Logfile to tail (must be readable by web daemon)' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='integer', NAME='web_logtail_length', VALUE='100', COMMENT='Number of lines to tail' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='entityname', VALUE='ACME', COMMENT='Name of the company/department' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='string', NAME='unknown', VALUE='%unknown%', COMMENT='Mask for unknown machines in the database' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='boolean', NAME='xls_output', VALUE='false', COMMENT='Enable XLS export from web interface' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
INSERT INTO `config` SET TYPE='integer', NAME='delete_users_threshold', VALUE='360', COMMENT='Delete users not seen in the central directory for more than XX days' ON DUPLICATE KEY UPDATE COMMENT=COMMENT;
UPDATE config SET comment='Global default vlan index for unknowns. Set to 0 for default deny' WHERE name='default_vlan';
UPDATE config SET comment='Vlan index for unknowns when auto added to the DB, normally=default_vlan' WHERE name='set_vlan_for_unknowns';
UPDATE config SET comment='Enable the use of a default vlan index per port - 0/1' WHERE name='use_port_default_vlan';
