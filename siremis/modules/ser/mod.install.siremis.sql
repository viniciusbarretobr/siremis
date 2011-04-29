
DROP TABLE IF EXISTS acc;

CREATE TABLE `acc` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `method` varchar(16) NOT NULL default '',
  `from_tag` varchar(64) NOT NULL default '',
  `to_tag` varchar(64) NOT NULL default '',
  `callid` varchar(128) NOT NULL default '',
  `sip_code` char(3) NOT NULL default '',
  `sip_reason` varchar(32) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `src_ip` varchar(64) NOT NULL default '',
  `dst_user` varchar(64) NOT NULL default '',
  `dst_domain` varchar(128) NOT NULL default '',
  `src_user` varchar(64) NOT NULL default '',
  `src_domain` varchar(128) NOT NULL default '',
  `cdr_id` integer NOT NULL default '0',
  INDEX acc_callid (`callid`),
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS missed_calls;

CREATE TABLE `missed_calls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `method` varchar(16) NOT NULL default '',
  `from_tag` varchar(64) NOT NULL default '',
  `to_tag` varchar(64) NOT NULL default '',
  `callid` varchar(128) NOT NULL default '',
  `sip_code` char(3) NOT NULL default '',
  `sip_reason` varchar(32) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `src_ip` varchar(64) NOT NULL default '',
  `dst_user` varchar(64) NOT NULL default '',
  `dst_domain` varchar(128) NOT NULL default '',
  `src_user` varchar(64) NOT NULL default '',
  `src_domain` varchar(128) NOT NULL default '',
  `cdr_id` integer NOT NULL default '0',
  INDEX mc_callid (`callid`),
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `cdrs`;

CREATE TABLE `cdrs` (
  `cdr_id` bigint(20) NOT NULL auto_increment,
  `src_username` varchar(64) NOT NULL default '',
  `src_domain` varchar(128) NOT NULL default '',
  `dst_username` varchar(64) NOT NULL default '',
  `dst_domain` varchar(128) NOT NULL default '',
  `call_start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` int(10) unsigned NOT NULL default '0',
  `sip_call_id` varchar(128) NOT NULL default '',
  `sip_from_tag` varchar(128) NOT NULL default '',
  `sip_to_tag` varchar(128) NOT NULL default '',
  `src_ip` varchar(64) NOT NULL default '',
  `cost` integer NOT NULL default '0',
  `rated` integer NOT NULL default '0',
  `created` datetime NOT NULL,
  PRIMARY KEY  (`cdr_id`),
  UNIQUE KEY `uk_cft` (`sip_call_id`,`sip_from_tag`,`sip_to_tag`)
);

DROP PROCEDURE IF EXISTS `kamailio_cdrs`;

DELIMITER %%

CREATE PROCEDURE `kamailio_cdrs`()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE bye_record INT DEFAULT 0;
  DECLARE v_src_user,v_src_domain,v_dst_user,v_dst_domain,v_callid,v_from_tag,
     v_to_tag,v_src_ip VARCHAR(64);
  DECLARE v_inv_time, v_bye_time DATETIME;
  DECLARE inv_cursor CURSOR FOR SELECT src_user, src_domain, dst_user,
     dst_domain, time, callid,from_tag, to_tag, src_ip FROM openser.acc
     where method='INVITE' and cdr_id='0';
  DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
  OPEN inv_cursor;
  REPEAT
    FETCH inv_cursor INTO v_src_user, v_src_domain, v_dst_user, v_dst_domain,
            v_inv_time, v_callid, v_from_tag, v_to_tag, v_src_ip;
    IF NOT done THEN
      SET bye_record = 0;
      SELECT 1, time INTO bye_record, v_bye_time FROM openser.acc WHERE
                 method='BYE' AND callid=v_callid AND ((from_tag=v_from_tag
                 AND to_tag=v_to_tag)
                 OR (from_tag=v_to_tag AND to_tag=v_from_tag))
                 ORDER BY time ASC LIMIT 1;
      IF bye_record = 1 THEN
        INSERT INTO openser.cdrs (src_username,src_domain,dst_username,
                 dst_domain,call_start_time,duration,sip_call_id,sip_from_tag,
                 sip_to_tag,src_ip,created) VALUES (v_src_user,v_src_domain,
                 v_dst_user,v_dst_domain,v_inv_time,
                 UNIX_TIMESTAMP(v_bye_time)-UNIX_TIMESTAMP(v_inv_time),
                 v_callid,v_from_tag,v_to_tag,v_src_ip,NOW());
        UPDATE acc SET cdr_id=last_insert_id() WHERE callid=v_callid
                 AND from_tag=v_from_tag AND to_tag=v_to_tag;
      END IF;
      SET done = 0;
    END IF;
  UNTIL done END REPEAT;
END

%%

DELIMITER ;

DROP TABLE IF EXISTS `statistics`;

CREATE TABLE `statistics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time_stamp` int(10) unsigned NOT NULL default '0',
  `shm_used_size` int(10) unsigned NOT NULL default '0',
  `shm_real_used_size` int(10) unsigned NOT NULL default '0',
  `shm_max_used_size` int(10) unsigned NOT NULL default '0',
  `shm_free_used_size` int(10) unsigned NOT NULL default '0',
  `ul_users` int(10) unsigned NOT NULL default '0',
  `ul_contacts` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

