/*
Created		18.01.2007
Modified		12.03.2007
Project		
Model		
Company		
Author		
Version		
Database		mySQL 5 
*/


drop table IF EXISTS config;
drop table IF EXISTS cabletype;
drop table IF EXISTS nac_wsusprocessor;
drop table IF EXISTS sys_os3;
drop table IF EXISTS sys_os1;
drop table IF EXISTS sys_os2;
drop table IF EXISTS nac_wsuspatches;
drop table IF EXISTS vstatus;
drop table IF EXISTS naclog;
drop table IF EXISTS vmpsauth;
drop table IF EXISTS vlanswitch;
drop table IF EXISTS vlan;
drop table IF EXISTS users;
drop table IF EXISTS systems;
drop table IF EXISTS sys_os;
drop table IF EXISTS sys_class2;
drop table IF EXISTS sys_class;
drop table IF EXISTS switch;
drop table IF EXISTS subnets;
drop table IF EXISTS stat_systems;
drop table IF EXISTS stat_ports;
drop table IF EXISTS services;
drop table IF EXISTS protocols;
drop table IF EXISTS port;
drop table IF EXISTS patchcable;
drop table IF EXISTS oper;
drop table IF EXISTS nac_wsusosmap;
drop table IF EXISTS nac_wsusupdatesstatuspercomputer;
drop table IF EXISTS nac_wsuscomputertarget;
drop table IF EXISTS nac_openports;
drop table IF EXISTS nac_hostscanned;
drop table IF EXISTS location;
drop table IF EXISTS guilog;
drop table IF EXISTS guirights;
drop table IF EXISTS ethernet;
drop table IF EXISTS dhcp_subnets;
drop table IF EXISTS dhcp_options;
drop table IF EXISTS building;
drop table IF EXISTS auth_profile;
drop table IF EXISTS EpoComputerProperties;


Create table EpoComputerProperties (
	id Int NOT NULL AUTO_INCREMENT,
	sid Int NOT NULL,
	LastUpdate Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	ParentID Int(11) NOT NULL,
	ComputerName Varchar(255) NOT NULL,
	DomainName Varchar(100),
	IPAddress Varchar(100),
	OSType Varchar(100),
	OSVersion Varchar(100),
	OSServicePackVer Varchar(100),
	OSBuildNum Smallint(6),
	NetAddress Varchar(100) NOT NULL,
	UserName Varchar(128),
	IPHostName Varchar(255),
	TheTimestamp Binary(8),
	AgentVersion Varchar(50) COMMENT 'epo agent version',
	LastDATUpdate Timestamp NOT NULL COMMENT 'date/time when DAT file was updated',
	DATVersion Varchar(20) COMMENT 'Version of the DAT file',
	UNIQUE (id),
	UNIQUE (sid),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table auth_profile (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	method Varchar(16) NOT NULL COMMENT 'access config : static, vmps, 8021x',
	config Text COMMENT 'optionnal : configuration instructions ',
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table building (
	id Int(11) NOT NULL AUTO_INCREMENT,
	name Varchar(64) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table dhcp_options (
	id Int(11) NOT NULL AUTO_INCREMENT,
	scope Int(11) NOT NULL,
	name Varchar(128) NOT NULL,
	value Varchar(256) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table dhcp_subnets (
	id Int(11) NOT NULL AUTO_INCREMENT,
	subnet_id Int(11) NOT NULL,
	dhcp_from Varchar(15) NOT NULL,
	dhcp_to Varchar(15) NOT NULL,
	dhcp_defaultrouter Varchar(128) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table ethernet (
	vendor Varchar(16) NOT NULL,
	mac Varchar(6) NOT NULL,
	UNIQUE (mac),
 Primary Key (mac)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table guirights (
	code Int(11) NOT NULL DEFAULT "0",
	value Varchar(30) NOT NULL,
	ad_group Varchar(255) NOT NULL,
 Primary Key (code)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table guilog (
	id Serial NOT NULL AUTO_INCREMENT,
	who Int NOT NULL,
	host Varchar(30) NOT NULL,
	datetime Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	priority Enum('info','err','crit') DEFAULT "info",
	what Varchar(200) NOT NULL,
	UNIQUE (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table location (
	id Int(15) NOT NULL AUTO_INCREMENT,
	building_id Int(11) NOT NULL DEFAULT 1,
	name Varchar(64) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_hostscanned (
	sid Int(11) NOT NULL,
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	ip Varchar(15) NOT NULL,
	hostname Varchar(80),
	os Varchar(80),
	timestamp Datetime,
	UNIQUE (sid),
 unique id (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_openports (
	id Int(11) NOT NULL AUTO_INCREMENT,
	sid Int(10) NOT NULL,
	service Int NOT NULL,
	banner Varchar(128),
	timestamp Datetime,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_wsuscomputertarget (
	sid Int(11) NOT NULL,
	TargetID Int(11) NOT NULL COMMENT 'local system id attributed by WSUS',
	IPAddress Varchar(40),
	FullDomainName Varchar(255),
	OSid Tinyint(11) NOT NULL COMMENT 'FK to wsusosmap.id',
	OSLocale Varchar(10),
	ComputerMake Varchar(64),
	ComputerModel Varchar(64),
	BiosVersion Varchar(64),
	BiosName Varchar(64),
	BiosReleaseDate Datetime,
	datetime Timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE (TargetID),
 Primary Key (TargetID)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_wsusupdatesstatuspercomputer (
	id Int NOT NULL AUTO_INCREMENT,
	SummarizationState Int NOT NULL,
	UpdateID Varchar(38) NOT NULL,
	TargetID Int(11) NOT NULL,
	LastChangeTime Datetime NOT NULL,
	LastRefreshTime Datetime NOT NULL,
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_wsusosmap (
	OSid Tinyint(4) NOT NULL,
	OSMajorVersion Smallint(6) NOT NULL,
	OSMinorVersion Smallint(6) NOT NULL,
	OSBuildNumber Smallint(6) NOT NULL,
	OSServicePackMajorNumber Smallint(6) NOT NULL,
	OSServicePackMinorNumber Smallint(6) NOT NULL,
	ProcessorArchitecture Tinyint NOT NULL,
	OSShortName Varchar(16),
	OSLongName Mediumtext,
 Primary Key (OSid)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table oper (
	value Enum('guiupdate','vmpsupdate','vmpscheck','server1','server2','lastseen1','lastseen2') NOT NULL DEFAULT "guiupdate",
	datetime Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	comment Varchar(66) NOT NULL) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'Status of GUI and daemons';

Create table patchcable (
	id Int NOT NULL AUTO_INCREMENT,
	rack Varchar(30) NOT NULL,
	rack_location Varchar(30) NOT NULL,
	outlet Varchar(30) NOT NULL,
	other Varchar(30) NOT NULL,
	office Int NOT NULL DEFAULT 1,
	type Int NOT NULL DEFAULT "0",
	port Int NOT NULL,
	comment Varchar(255) NOT NULL,
	lastchange Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	modifiedby Int NOT NULL,
	expiry Date,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'Patch cables from Office Sockets to Port Switches';

Create table port (
	id Int(11) NOT NULL AUTO_INCREMENT,
	switch Int NOT NULL,
	name Varchar(20) NOT NULL,
	comment Varchar(255),
	restart_now Int(11),
	default_vlan Int(11) NOT NULL,
	snmp_idx Int(11),
	last_vlan Int(11) NOT NULL,
	last_activity Datetime,
	auth_profile Int(11) UNSIGNED NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table protocols (
	protocol Int(11) NOT NULL DEFAULT "0",
	name Varchar(50),
	description Varchar(50),
 Primary Key (protocol)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table services (
	id Int(11) NOT NULL AUTO_INCREMENT,
	port Int(11) NOT NULL,
	protocol Int(11) NOT NULL DEFAULT "6",
	name Varchar(50) NOT NULL,
	description Varchar(255) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table stat_ports (
	date Date NOT NULL DEFAULT "0000-00-00",
	auth_profile Int(11) UNSIGNED NOT NULL DEFAULT "0",
	count Int(11)) ENGINE = MyISAM
ROW_FORMAT = Fixed;

Create table stat_systems (
	date Date NOT NULL DEFAULT "0000-00-00",
	vstatus Int(10) UNSIGNED NOT NULL,
	count Int(11)) ENGINE = MyISAM
ROW_FORMAT = Fixed;

Create table subnets (
	id Int(11) NOT NULL AUTO_INCREMENT,
	ip_address Varchar(20) NOT NULL,
	ip_netmask Int(4) NOT NULL,
	scan Tinyint(4) NOT NULL,
	dontscan Varchar(128),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table switch (
	id Int(11) NOT NULL AUTO_INCREMENT,
	ip Varchar(20) NOT NULL DEFAULT "NULL",
	name Varchar(20) DEFAULT "NULL",
	location Int NOT NULL DEFAULT 1,
	comment Varchar(50) NOT NULL DEFAULT "NULL",
	swgroup Varchar(20) DEFAULT "NULL",
	notify Varchar(200) DEFAULT "NULL",
	ap Tinyint(4) NOT NULL DEFAULT "0",
	scan Tinyint(1),
	hw Char(64),
	sw Char(64),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table sys_class (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Number',
	value Varchar(30) NOT NULL COMMENT 'Class lookup for systems',
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'Device Types';

Create table sys_class2 (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Number',
	value Varchar(30) NOT NULL,
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table sys_os (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	value Varchar(30) NOT NULL COMMENT 'Operating System lookup for systems',
	icon Varchar(20),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table systems (
	id Int(11) NOT NULL AUTO_INCREMENT,
	mac Varchar(30) NOT NULL,
	name Varchar(30) NOT NULL DEFAULT "NOBODY",
	description Varchar(100) NOT NULL,
	uid Int(11) NOT NULL,
	vlan Int(11) NOT NULL DEFAULT "13",
	comment Varchar(100),
	ChangeDate Varchar(100),
	ChangeUser Int NOT NULL,
	status Int(4) UNSIGNED NOT NULL DEFAULT "1",
	LastSeen Datetime,
	office Int NOT NULL DEFAULT 1,
	LastPort Int NOT NULL,
	history Text,
	LastVlan Int NOT NULL,
	os Int(11) UNSIGNED NOT NULL,
	os1 Int UNSIGNED NOT NULL,
	os2 Int UNSIGNED NOT NULL,
	os3 Int UNSIGNED NOT NULL,
	os4 Varchar(64),
	class Int(11) UNSIGNED NOT NULL,
	class2 Int(11) UNSIGNED NOT NULL,
	r_ip Varchar(20),
	r_timestamp Datetime,
	r_ping_timestamp Datetime,
	inventory Varchar(20),
	scannow Tinyint(4) DEFAULT "0",
	expiry Datetime,
	dhcp_fix Tinyint(4),
	dhcp_ip Varchar(20),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'List of VMPS controlled computers';

Create table users (
	id Int(11) NOT NULL AUTO_INCREMENT,
	LastSeenDirectory Date NOT NULL DEFAULT "0000-00-00",
	username Varchar(100) NOT NULL,
	Surname Varchar(100) NOT NULL,
	GivenName Varchar(100) NOT NULL,
	Department Varchar(100),
	rfc822mailbox Varchar(100),
	HouseIdentifier Varchar(100),
	PhysicalDeliveryOfficeName Varchar(100),
	TelephoneNumber Varchar(100),
	Mobile Varchar(100),
	nac_rights Int(11),
	manual_direx_sync Int(11),
	comment Varchar(200),
	GuiVlanRights Varchar(255),
	location Int NOT NULL DEFAULT 1,
	UNIQUE (username),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'Users details';

Create table vlan (
	id Int(11) NOT NULL AUTO_INCREMENT,
	default_name Varchar(30) NOT NULL,
	default_id Int,
	vlan_description Varchar(100),
	vlan_group Int(11),
	color Varchar(6),
	UNIQUE (id),
	UNIQUE (default_name),
	UNIQUE (default_id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table vlanswitch (
	vid Int(11) NOT NULL,
	swid Int(11) NOT NULL,
	vlan_id Int(11) NOT NULL,
	vlan_name Varchar(100) NOT NULL) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table vmpsauth (
	sid Int NOT NULL,
	AuthLast Datetime,
	AuthPort Int NOT NULL,
	AuthVlan Int(11) NOT NULL) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'List of VMPS authenticated Computers
Local on each server - not replicated
Used only by vmps_external';

Create table naclog (
	id Serial NOT NULL AUTO_INCREMENT,
	who Int NOT NULL,
	host Varchar(30) NOT NULL,
	datetime Timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	priority Enum('info','err','crit') DEFAULT "info",
	what Varchar(200) NOT NULL,
	UNIQUE (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic
COMMENT = 'Log of server activities';

Create table vstatus (
	id Int(10) UNSIGNED NOT NULL,
	value Varchar(30) NOT NULL,
	color Varchar(6),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_wsuspatches (
	LocalUpdateID Int NOT NULL,
	UpdateID Varchar(38) NOT NULL COMMENT 'Microsoft KB number',
	Title Varchar(200),
	KBArticleID Varchar(15) NOT NULL,
	UNIQUE (LocalUpdateID),
 Primary Key (UpdateID)) ENGINE = MyISAM;

Create table sys_os2 (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	value Varchar(30) NOT NULL COMMENT 'Operating System lookup for systems',
	icon Varchar(20),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table sys_os1 (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	value Varchar(30) NOT NULL COMMENT 'Operating System lookup for systems',
	icon Varchar(20),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table sys_os3 (
	id Int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	value Varchar(30) NOT NULL COMMENT 'Operating System lookup for systems',
	icon Varchar(20),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM
ROW_FORMAT = Dynamic;

Create table nac_wsusprocessor (
	Pid Tinyint NOT NULL,
	ProcessorArchitecture Varchar(50),
	UNIQUE (Pid),
 Primary Key (Pid)) ENGINE = MyISAM;

Create table cabletype (
	id Int NOT NULL AUTO_INCREMENT,
	name Varchar(64),
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM;

Create table config (
	id Int NOT NULL AUTO_INCREMENT,
	type Varchar(16),
	name Varchar(64),
	value Varchar(255),
	comment Varchar(255),
	LastChange Timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	who Int,
	UNIQUE (id),
 Primary Key (id)) ENGINE = MyISAM;


INSERT INTO `nac_wsusprocessor` VALUES (1,'x86'),(2,'ia64'),(3,'AMD64');


Alter table EpoComputerProperties add unique Alter_Key4 (sid);
Alter table port add unique Alter_Key2 (switch,name);
Alter table vmpsauth add unique Alter_Key3 (sid,AuthPort,AuthVlan);


Alter table port add Foreign Key (auth_profile) references auth_profile (id) on delete  restrict on update  restrict;
Alter table stat_ports add Foreign Key (auth_profile) references auth_profile (id) on delete  restrict on update  restrict;
Alter table location add Foreign Key (building_id) references building (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (office) references location (id) on delete  restrict on update  restrict;
Alter table switch add Foreign Key (location) references location (id) on delete  restrict on update  restrict;
Alter table patchcable add Foreign Key (office) references location (id) on delete  restrict on update  restrict;
Alter table users add Foreign Key (location) references location (id) on delete  restrict on update  restrict;
Alter table nac_wsusupdatesstatuspercomputer add Foreign Key (TargetID) references nac_wsuscomputertarget (TargetID) on delete  restrict on update  restrict;
Alter table nac_wsuscomputertarget add Foreign Key (OSid) references nac_wsusosmap (OSid) on delete  restrict on update  restrict;
Alter table patchcable add Foreign Key (port) references port (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (LastPort) references port (id) on delete  restrict on update  restrict;
Alter table vmpsauth add Foreign Key (AuthPort) references port (id) on delete  restrict on update  restrict;
Alter table services add Foreign Key (protocol) references protocols (protocol) on delete  restrict on update  restrict;
Alter table nac_openports add Foreign Key (service) references services (id) on delete  restrict on update  restrict;
Alter table dhcp_subnets add Foreign Key (subnet_id) references subnets (id) on delete  restrict on update  restrict;
Alter table dhcp_options add Foreign Key (scope) references subnets (id) on delete  restrict on update  restrict;
Alter table vlanswitch add Foreign Key (swid) references switch (id) on delete  restrict on update  restrict;
Alter table port add Foreign Key (switch) references switch (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (class) references sys_class (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (class2) references sys_class2 (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (os) references sys_os (id) on delete  restrict on update  restrict;
Alter table nac_hostscanned add Foreign Key (sid) references systems (id) on delete  restrict on update  restrict;
Alter table EpoComputerProperties add Foreign Key (sid) references systems (id) on delete  restrict on update  restrict;
Alter table nac_wsuscomputertarget add Foreign Key (sid) references systems (id) on delete  restrict on update  restrict;
Alter table nac_openports add Foreign Key (sid) references systems (id) on delete  restrict on update  restrict;
Alter table vmpsauth add Foreign Key (sid) references systems (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (uid) references users (id) on delete  restrict on update  restrict;
Alter table guilog add Foreign Key (who) references users (id) on delete  restrict on update  restrict;
Alter table naclog add Foreign Key (who) references users (id) on delete  restrict on update  restrict;
Alter table patchcable add Foreign Key (modifiedby) references users (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (ChangeUser) references users (id) on delete  restrict on update  restrict;
Alter table config add Foreign Key (who) references users (id) on delete  restrict on update  restrict;
Alter table vlanswitch add Foreign Key (vid) references vlan (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (vlan) references vlan (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (LastVlan) references vlan (id) on delete  restrict on update  restrict;
Alter table port add Foreign Key (default_vlan) references vlan (id) on delete  restrict on update  restrict;
Alter table port add Foreign Key (last_vlan) references vlan (id) on delete  restrict on update  restrict;
Alter table vmpsauth add Foreign Key (AuthVlan) references vlan (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (status) references vstatus (id) on delete  restrict on update  restrict;
Alter table stat_systems add Foreign Key (vstatus) references vstatus (id) on delete  restrict on update  restrict;
Alter table nac_wsusupdatesstatuspercomputer add Foreign Key (UpdateID) references nac_wsuspatches (UpdateID) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (os2) references sys_os2 (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (os1) references sys_os1 (id) on delete  restrict on update  restrict;
Alter table systems add Foreign Key (os3) references sys_os3 (id) on delete  restrict on update  restrict;
Alter table nac_wsusosmap add Foreign Key (ProcessorArchitecture) references nac_wsusprocessor (Pid) on delete  restrict on update  restrict;
Alter table patchcable add Foreign Key (type) references cabletype (id) on delete  restrict on update  restrict;


/* Users permissions */


