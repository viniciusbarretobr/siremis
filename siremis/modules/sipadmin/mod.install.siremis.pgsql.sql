
DROP TABLE IF EXISTS acc;

CREATE TABLE acc (
  id SERIAL PRIMARY KEY NOT NULL,
  method varchar(16) NOT NULL default '',
  from_tag varchar(64) NOT NULL default '',
  to_tag varchar(64) NOT NULL default '',
  callid varchar(128) NOT NULL default '',
  sip_code char(3) NOT NULL default '',
  sip_reason varchar(32) NOT NULL default '',
  time TIMESTAMP WITHOUT TIME ZONE DEFAULT '1900-01-01 00:00:01' NOT NULL,
  src_ip varchar(64) NOT NULL default '',
  dst_ouser VARCHAR(64) NOT NULL DEFAULT '',
  dst_user varchar(64) NOT NULL default '',
  dst_domain varchar(128) NOT NULL default '',
  src_user varchar(64) NOT NULL default '',
  src_domain varchar(128) NOT NULL default '',
  cdr_id integer NOT NULL default '0'
);

CREATE INDEX acc_callid ON acc (callid);

DROP TABLE IF EXISTS missed_calls;

CREATE TABLE missed_calls (
  id SERIAL PRIMARY KEY NOT NULL,
  method varchar(16) NOT NULL default '',
  from_tag varchar(64) NOT NULL default '',
  to_tag varchar(64) NOT NULL default '',
  callid varchar(128) NOT NULL default '',
  sip_code char(3) NOT NULL default '',
  sip_reason varchar(32) NOT NULL default '',
  time TIMESTAMP WITHOUT TIME ZONE DEFAULT '1900-01-01 00:00:01' NOT NULL,
  src_ip varchar(64) NOT NULL default '',
  dst_ouser VARCHAR(64) NOT NULL DEFAULT '',
  dst_user varchar(64) NOT NULL default '',
  dst_domain varchar(128) NOT NULL default '',
  src_user varchar(64) NOT NULL default '',
  src_domain varchar(128) NOT NULL default '',
  cdr_id integer NOT NULL default '0'
);

CREATE INDEX mc_callid ON missed_calls (callid);

DROP TABLE IF EXISTS cdrs;

CREATE TABLE cdrs (
  cdr_id SERIAL PRIMARY KEY NOT NULL,
  src_username varchar(64) NOT NULL default '',
  src_domain varchar(128) NOT NULL default '',
  dst_username varchar(64) NOT NULL default '',
  dst_domain varchar(128) NOT NULL default '',
  dst_ousername varchar(64) NOT NULL default '',
  call_start_time TIMESTAMP WITHOUT TIME ZONE DEFAULT '1900-01-01 00:00:01' NOT NULL,
  duration integer NOT NULL default '0',
  sip_call_id varchar(128) NOT NULL default '',
  sip_from_tag varchar(128) NOT NULL default '',
  sip_to_tag varchar(128) NOT NULL default '',
  src_ip varchar(64) NOT NULL default '',
  cost integer NOT NULL default '0',
  rated integer NOT NULL default '0',
  created TIMESTAMP WITHOUT TIME ZONE DEFAULT '1900-01-01 00:00:01' NOT NULL,
  CONSTRAINT uk_cft UNIQUE (sip_call_id,sip_from_tag,sip_to_tag)
);

DROP TABLE IF EXISTS billing_rates;

CREATE TABLE billing_rates (
  rate_id SERIAL PRIMARY KEY NOT NULL,
  rate_group varchar(64) NOT NULL default 'default',
  prefix varchar(64) NOT NULL default '',
  rate_unit integer NOT NULL default '0',
  time_unit integer NOT NULL default '60',
  CONSTRAINT uk_rp UNIQUE (rate_group,prefix)
);

-- DROP PROCEDURE IF EXISTS kamailio_cdrs;
-- DROP PROCEDURE IF EXISTS kamailio_rating;

-- DELIMITER %%

-- CREATE PROCEDURE kamailio_cdrs()
-- BEGIN
--   DECLARE done INT DEFAULT 0;
--   DECLARE bye_record INT DEFAULT 0;
--   DECLARE v_src_user,v_src_domain,v_dst_user,v_dst_domain,v_dst_ouser,v_callid,
--      v_from_tag,v_to_tag,v_src_ip VARCHAR(64);
--  DECLARE v_inv_time, v_bye_time TIMESTAMP;
--  DECLARE inv_cursor CURSOR FOR SELECT src_user, src_domain, dst_user,
--     dst_domain, dst_ouser, time, callid,from_tag, to_tag, src_ip
--     FROM acc
--     where method='INVITE' and cdr_id='0';
--  DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
--  OPEN inv_cursor;
--  REPEAT
--    FETCH inv_cursor INTO v_src_user, v_src_domain, v_dst_user, v_dst_domain,
--            v_dst_ouser, v_inv_time, v_callid, v_from_tag, v_to_tag, v_src_ip;
--    IF NOT done THEN
--      SET bye_record = 0;
--      SELECT 1, time INTO bye_record, v_bye_time FROM acc WHERE
--                 method='BYE' AND callid=v_callid AND ((from_tag=v_from_tag
--                 AND to_tag=v_to_tag)
--                 OR (from_tag=v_to_tag AND to_tag=v_from_tag))
--                 ORDER BY time ASC LIMIT 1;
--      IF bye_record = 1 THEN
--        INSERT INTO cdrs (src_username,src_domain,dst_username,
--                 dst_domain,dst_ousername,call_start_time,duration,sip_call_id,
--                 sip_from_tag,sip_to_tag,src_ip,created) VALUES (v_src_user,
--                 v_src_domain,v_dst_user,v_dst_domain,v_dst_ouser,v_inv_time,
--                 UNIX_TIMESTAMP(v_bye_time)-UNIX_TIMESTAMP(v_inv_time),
--                 v_callid,v_from_tag,v_to_tag,v_src_ip,NOW());
--        UPDATE acc SET cdr_id=last_insert_id() WHERE callid=v_callid
--                 AND from_tag=v_from_tag AND to_tag=v_to_tag;
--      END IF;
--      SET done = 0;
--    END IF;
--  UNTIL done END REPEAT;
--END
--
--%%
--
--CREATE PROCEDURE kamailio_rating(rgroup varchar(64))
--BEGIN
--  DECLARE done, rate_record, vx_cost INT DEFAULT 0;
--  DECLARE v_cdr_id BIGINT DEFAULT 0;
--  DECLARE v_duration, v_rate_unit, v_time_unit INT DEFAULT 0;
--  DECLARE v_dst_username VARCHAR(64);
--  DECLARE cdrs_cursor CURSOR FOR SELECT cdr_id, dst_username, duration
--     FROM cdrs WHERE rated=0;
--  DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done = 1;
--  OPEN cdrs_cursor;
--  REPEAT
--    FETCH cdrs_cursor INTO v_cdr_id, v_dst_username, v_duration;
--    IF NOT done THEN
--      SET rate_record = 0;
--      SELECT 1, rate_unit, time_unit INTO rate_record, v_rate_unit, v_time_unit
--             FROM billing_rates
--             WHERE rate_group=rgroup AND v_dst_username LIKE concat(prefix, '%')
--             ORDER BY prefix DESC LIMIT 1;
--      IF rate_record = 1 THEN
--        SET vx_cost = v_rate_unit * CEIL(v_duration/v_time_unit);
--        UPDATE cdrs SET rated=1, cost=vx_cost WHERE cdr_id=v_cdr_id;
--      END IF;
--      SET done = 0;
--    END IF;
--  UNTIL done END REPEAT;
--END
--
--%%
--
--DELIMITER ;

DROP TABLE IF EXISTS statistics;

CREATE TABLE statistics (
  id SERIAL PRIMARY KEY NOT NULL,
  time_stamp integer NOT NULL default '0',
  shm_used_size integer NOT NULL default '0',
  shm_real_used_size integer NOT NULL default '0',
  shm_max_used_size integer NOT NULL default '0',
  shm_free_used_size integer NOT NULL default '0',
  ul_users integer NOT NULL default '0',
  ul_contacts integer NOT NULL default '0',
  tm_active integer NOT NULL default '0',
  rcv_req_diff integer NOT NULL default '0',
  fwd_req_diff integer NOT NULL default '0',
  s2xx_trans_diff integer NOT NULL default '0'
);

INSERT INTO domain (domain, did) VALUES ('127.0.0.1', 'default');

