-- New epo_systems table
CREATE TABLE epo_systems (
  sid int not null,
  nodename varchar(30),
  domainname varchar(100),
  ip varchar(20),
  mac varchar(30) not null,
  agentversion varchar(50),
  lastepocontact datetime,
  virusver varchar(50),
  virusenginever varchar(100),
  virusdatver varchar(50),
  virushotfix varchar(100),
  ostype varchar(100),
  osversion varchar(100),
  osservicepackver varchar(100),
  osbuildnum int,
  freediskspace int,
  username varchar(128),
  lastsync datetime not null,
  PRIMARY KEY (sid),
  unique key (sid),
  UNIQUE KEY (mac)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='list of systems with the epo client installed, synced from epo database';

-- New epo_versions table
CREATE TABLE epo_versions (
  id int not null auto_increment,
  product varchar(255) not null,
  version varchar(255) not null,
  hotfix varchar(32),
  lastsync datetime not null,
  primary key (id),
  unique key (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='current version of epo products, synced from epo database';

-- New wsus_systems table
create table if not exists wsus_systems (
        sid int not null,
        hostname varchar(255) not null,
        ip varchar(20),
        lastwsuscontact datetime,
        os varchar(256),
        computermake varchar(64),
        computermodel varchar(64),
        notinstalled int,
        downloaded int,
        installedpendingreboot int,
        failed int,
        lastsync datetime not null,
        primary key (sid),
        unique (sid),
        unique (hostname)
) engine=myisam default charset=utf8 row_format=dynamic comment='list of systems with the wsus client, synced from wsus database';

-- New wsus_neededUpdates table
create table if not exists wsus_neededUpdates (
        localupdateid int not null,
        title varchar(200),
        description varchar(1500),
        msrcseverity varchar(20),
        creationdate datetime,
        receiveddate datetime,
        lastsync datetime not null,
        primary key (localupdateid),
        unique (localupdateid)
) engine=myisam default charset=utf8 row_format=dynamic comment='global list of needed updates, synced from wsus database';

-- New wsus_systemToUpdates table
create table if not exists wsus_systemToUpdates (
        id int not null auto_increment,
        sid int not null,
        localupdateid int not null,
        lastsync datetime not null,
        primary key (id),
        unique (id)
) engine=myisam default charset=utf8 row_format=dynamic comment='mapping of systems to updates';

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
	PRIMARY KEY (`id`)
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
INSERT INTO config SET type='string', name='default_policy', value='BasicPolicy', COMMENT='Policy to load';
INSERT INTO config SET type='boolean', name='router_mac_ip_discoverall', value='false', comment='Discover/document all MAC/IPs found or only those already in the DB?';
INSERT INTO config SET type='boolean', name='scan_unmanaged', value='false', comment='Should port_scan scan unmanaged systems?';
INSERT INTO config SET type='integer', name='scan_hours_for_ip', value='3', comment='Number of hours for an IP address to be considered as up-to-date';
INSERT INTO config SET type='boolean', name='vm_lan_like_host', value='false', comment='If VM, assign the same VLAN as its host?';
INSERT INTO config SET type='boolean', name='wsus_enabled', value='false', COMMENT='Enable the WSUS module';
INSERT INTO config SET type='string', name='wsus_dbalias', COMMENT='DNS name';
INSERT INTO config SET type='string', name='wsus_db', COMMENT='DB instance';
INSERT INTO config SET type='boolean', name='epo_enabled', value='false', COMMENT='Enable antivirus checking';
INSERT INTO config SET type='string', name='epo_dbalias', COMMENT='DNS name';
INSERT INTO config SET type='string', name='epo_db', COMMENT='DB instance';
INSERT INTO config SET type='boolean', name='restart_daemons', value='false', COMMENT='Restart master daemons?';
UPDATE config SET comment='Global default vlan index for unknowns. Set to 0 for default deny' WHERE name='default_vlan';
UPDATE config SET comment='Vlan index for unknowns when auto added to the DB, normally=default_vlan' WHERE name='set_vlan_for_unknowns';
UPDATE config SET comment='Enable the use of a default vlan index per port - 0/1' WHERE name='use_port_default_vlan';

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

-- Permisions on new tables
grant select, update, insert, delete on opennac.epo_versions to inventwrite@'localhost';
grant select, update, insert, delete on opennac.epo_systems to inventwrite@'localhost';
grant select, update, insert, delete on opennac.wsus_systems to inventwrite@'localhost';
grant select, update, insert, delete on opennac.wsus_systemToUpdates to inventwrite@'localhost';
grant select, update, insert, delete on opennac.wsus_neededUpdates to inventwrite@'localhost';

grant select on opennac.epo_versions to inventwrite@'%';
grant select on opennac.epo_systems to inventwrite@'%';
grant select on opennac.wsus_systems to inventwrite@'%';
grant select on opennac.wsus_systemToUpdates to inventwrite@'%';
grant select on opennac.wsus_neededUpdates to inventwrite@'%';

grant select on opennac.health to inventwrite@'localhost';
grant select on opennac.health to inventwrite@'%';

grant select, insert on opennac.stats to inventwrite@'localhost';
