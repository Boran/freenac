INSERT INTO `config` SET `type`='integer', `name`='report_old_users_kill_days', `value`='0', `comment`='Kill systems belonging to users who haven\'t been seen in the directory for longer than X days';
UPDATE `config` SET `value`='-A -sS -n -P0' WHERE `name`='nmap_flags';

-- New config variable 
INSERT INTO `config` SET `type`='boolean', `name`='check_clear_mac', `value`='false', `comment`='Enable the clear_mac function, for CISCO IOS. Replaces port_restart no newer IOS versions';

-- Systems table
ALTER TABLE `systems` ADD COLUMN `clear_mac` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `systems` ADD KEY `clear_mac` (`clear_mac`);

-- Switch table
ALTER TABLE `switch` ADD COLUMN `switch_type` TINYINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `switch` ADD KEY `switch_type` (`switch_type`);


