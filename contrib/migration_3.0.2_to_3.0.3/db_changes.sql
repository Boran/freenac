INSERT INTO `config` SET `type`='integer', `name`='report_old_users_kill_days', `value`='0', `comment`='Kill systems belonging to users who haven\'t been seen in the directory for longer than X days';
UPDATE `config` SET `value`='-A -sS -n -P0' WHERE `name`='nmap_flags';
