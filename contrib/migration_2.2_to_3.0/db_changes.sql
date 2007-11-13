-- New stats table
CREATE TABLE `stats` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(100) default NULL,
  `value` int(11) NOT NULL,
  `datetime` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- New health table
CREATE TABLE `health` (
	`id` int(10) unsigned not null,
	`value` varchar(30) not null,
	`color` varchar(6) default null,
	`comment` varchar(100) default null,
	PRIMARY KEY (`id`);
);
INSERT INTO health(id, value, comment) VALUES (0,'Unknown','No health information available');
INSERT INTO health(id, value, comment) VALUES (1, 'OK', '');
INSERT INTO health(id, value, comment) VALUES (4, 'Transition', 'Scan needed');
INSERT INTO health(id, value, comment) VALUES (5, 'Quarantine', 'AV or patch update needed');
INSERT INTO health(id, value, comment) VALUES (6, 'Infected', '');

-- Status table, delete transition, quarantine, infected
DELETE FROM vstatus WHERE id='4';
DELETE FROM vstatus WHERE id='5';
DELETE FROM vstatus WHERE id='6';

-- New fields needed for switch monitoring
ALTER TABLE switch ADD COLUMN last_monitored datetime COMMENT "Last time the switch was polled";
ALTER TABLE switch ADD COLUMN up int COMMENT "Monitor: switch is reachable(1) or down?";

-- New fields needed for status and programming of port settings
ALTER TABLE port ADD COLUMN last_monitored datetime COMMENT "Last time the port was monitored";
ALTER TABLE port ADD COLUMN up int COMMENT "Monitor: port is up(1) or down?";
ALTER TABLE port ADD COLUMN last_auth_profile int COMMENT "Is port static/dynamic (lookup table auth_profile)";
ALTER TABLE port ADD COLUMN staticvlan int COMMENT "If static, program this vlan";
ALTER TABLE port ADD COLUMN shutdown int COMMENT "Shutdown the port(1)?";

-- Systems table, new fields
ALTER TABLE systems ADD COLUMN dns_alias varchar(200) COMMENT "CSV: for static DNS IP mgt";
ALTER TABLE systems ADD COLUMN health int COMMENT "Lookup into health table";
ALTER TABLE systems ADD COLUMN last_hostname varchar(100) COMMENT "DNS or DHCP name + domain";
ALTER TABLE systems ADD COLUMN last_nbtname varchar(100) COMMENT "NetBIOS name";
ALTER TABLE systems ADD COLUMN last_uid int COMMENT "Last user logged on to PC";
ALTER TABLE systems ADD COLUMN email_on_connect varchar(100) COMMENT "Email address to alert";

-- New configuration entries in the config table
INSERT INTO config SET type='string', name='default_policy', value='BasicPolicy';
INSERT INTO config SET type='boolean', name='router_mac_ip_discoverall', value='false', comment='Discover/document all MAC/IPs found or only those already in the DB?';
INSERT INTO config SET type='boolean', name='scan_unmanaged', value='false', comment='Should port_scan scan unmanaged systems?';
INSERT INTO config SET type='integer', name='scan_hours_for_ip', value='3', comment='Number of hours for an IP address to be considered as up-to-date';
INSERT INTO config SET type='boolean', name='vm_lan_like_host', value='false', comment='If VM, assign the same VLAN as its host?';
INSERT INTO config SET type='boolean', name='wsus_enabled', value='false';
INSERT INTO config SET type='string', name='wsus_dbalias';
INSERT INTO config SET type='string', name='wsus_db';
INSERT INTO config SET type='boolean', name='epo_enabled', value='false';
INSERT INTO config SET type='string', name='epo_dbalias';
INSERT INTO config SET type='string', name='epo_db';
INSERT INTO config SET type='boolean', name='restart_daemons', value='false';

-- DB fixes
alter table systems change column description description varchar(100) default null comment "v3: not used";
alter table systems change column uid uid int(11) default null;
alter table systems change column ChangeDate ChangeDate varchar(100) default null;
alter table systems change column ChangeUser ChangeUser int(11) default null;
alter table systems change column LastPort LastPort int(11) default null;
alter table systems change column LastVlan LastVlan int(11) default null;
alter table systems change column os os int(11) default null;
alter table systems change column os1 os1 int(10) default null;
alter table systems change column os2 os2 int(10) default null;
alter table systems change column os3 os3 int(10) default null;
alter table systems change column os4 os4 varchar(64) default null;
alter table systems change column class class int(11) unsigned default null;
alter table systems change column class2 class2 int(11) unsigned default null;
alter table switch change column ip ip varchar(20) default null;
alter table switch change column location location int(11) default null;
alter table switch change column `comment` `comment` varchar(50) default null;
alter table port change column default_vlan default_vlan int(11) default null;
alter table port change column last_vlan last_vlan int(11) default null;
alter table port change column auth_profile auth_profile int(11) unsigned default null;
alter table vlanswitch change column vlan_id vlan_id int(11) default null;
alter table guirights change column ad_group ad_group varchar(255) default null;
alter table patchcable change column rack_location rack_location varchar(30) default null;
alter table patchcable change column outlet outlet varchar(30) default null;
alter table patchcable change column other other varchar(30) default null;
alter table patchcable change column `comment` `comment` varchar(30) default null;
alter table services change column description description varchar(255) default null;
alter table subnets change column scan scan tinyint(4) default '0';
alter table switch add column vlan_id int default null;
alter table systems add column group_id int default null;
