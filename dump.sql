-- CREATE ROLE webclient LOGIN ENCRYPTED PASSWORD 'md5f6a63055f8b8a350438c9893fe9de09d' VALID UNTIL 'infinity';
-- CREATE DATABASE wc2storage WITH ENCODING='UTF8' OWNER=webclient CONNECTION LIMIT=-1;

CREATE TABLE "ticker" (
    "cid"        INT NOT NULL,
    "time_stamp" INT NOT NULL,
    "host_time"  VARCHAR (40) NOT NULL,
    "gps_time"   VARCHAR (40),
    "latitude"   VARCHAR (16),
    "longitude"  VARCHAR (16)
);

CREATE TABLE "detections" (
    "id"             BIGSERIAL PRIMARY KEY,
    "cid"            INT NOT NULL,
    "time_stamp"     INT NOT NULL,
    "time_marker"    BIGINT NOT NULL,
    "url"            VARCHAR (255) NOT NULL,
    "detect_objects" JSON,
    "gps_time"       VARCHAR (40),
    "latitude"       VARCHAR (16),
    "longitude"      VARCHAR (16),
    "cam_mode"       VARCHAR (16),
    "file_name"      VARCHAR (45),
    "image"          VARCHAR (40),
    "imgfull"        VARCHAR (10),
    "session_id"     VARCHAR (48),
    "track_id"       VARCHAR (10)
);

CREATE TABLE "sessionlist" (
    "complex_id"        INT NOT NULL,
    "session_id"        VARCHAR (48),
    "session_type"      VARCHAR (10),
    "session_start"     VARCHAR (40),
    "session_stop"      VARCHAR (40),
    "session_timestamp" INT
);

CREATE TABLE "alarms" (
    "cid"        INT NOT NULL,
    "time_stamp" INT NOT NULL,
    "code"       INT NOT NULL,
    "aname"      VARCHAR (50) NOT NULL,
    "message"    VARCHAR (70) NOT NULL,
    "aux_data"   VARCHAR (70)
);

CREATE TABLE "departures" (
    "id"           SERIAL PRIMARY KEY,
    "complex_id"   INT NOT NULL,
    "complex_name" VARCHAR (50) NOT NULL,
    "cam_mode"     VARCHAR (20) NOT NULL,
    "action_timestamp"  INT NOT NULL,
    "action_user"  VARCHAR (100) NOT NULL,
    "status"       VARCHAR (11) NOT NULL,
    "session_id"   VARCHAR (48)
);

CREATE TABLE "userlist" (
    "id"        SERIAL PRIMARY KEY,
    "ulogin"    VARCHAR (100) NOT NULL UNIQUE,
    "upassword" VARCHAR (42) NOT NULL,
    "urole"     VARCHAR (10) NOT NULL,
    "uilang"    VARCHAR (2) DEFAULT 'uk',
    "fname"     VARCHAR (100),
    "phone"     VARCHAR (50),
    "email"     VARCHAR (150),
    "dept"      VARCHAR (100),
    "squad"     VARCHAR (50),
    "syncok"    INT NOT NULL DEFAULT 0
);

CREATE TABLE "complexlist" (
    "id"      SERIAL PRIMARY KEY,
    "cid"     VARCHAR (50) NOT NULL UNIQUE,
    "cip"     VARCHAR (50) NOT NULL UNIQUE,
    "cpt"     INT,
    "ckey"    VARCHAR (70) NOT NULL,
    "colour"  VARCHAR (10) UNIQUE,
    "cstatus" VARCHAR (3) NOT NULL,
    "camres"  VARCHAR(2) NOT NULL
);

CREATE TABLE "complexmodes" (
    "complex_id" INT NOT NULL UNIQUE,
    "modes"      TEXT
);

CREATE TABLE "attrs" (
    "central_server_url" VARCHAR (250) UNIQUE,
    "diagnostic_temp"    TEXT,
    "block_notify_from"  INT,
    "use_logger"         VARCHAR (3)
);

CREATE TABLE "sessiondata" (
    "id"             BIGSERIAL PRIMARY KEY,
    "cid"            INT NOT NULL,
    "time_stamp"     INT NOT NULL,
    "url"            VARCHAR (255) NOT NULL,
    "detect_objects" JSON,
    "gps_time"       VARCHAR (40),
    "latitude"       VARCHAR (16),
    "longitude"      VARCHAR (16),
    "cam_mode"       VARCHAR (16),
    "imgfull"        VARCHAR (40) NOT NULL,
    "session_id"     VARCHAR (48) NOT NULL,
    "track_id"       VARCHAR (10) NOT NULL
);

CREATE TABLE "contactslist" (
    "id"      SERIAL PRIMARY KEY,
    "ctype"   VARCHAR (10)  NOT NULL,
    "contact" VARCHAR (255) NOT NULL UNIQUE
);

CREATE TABLE "sessions" (
    "id" TEXT NOT NULL UNIQUE,
    "last_updated" INT NOT NULL,
    "expiry" INT NOT NULL,
    "data" TEXT NOT NULL
);
CREATE INDEX "valid_sessions" ON "sessions"("id");
CREATE INDEX "nonexpired_sessions" ON "sessions"("id","expiry");

--CREATE UNIQUE INDEX t_vio_v_fact_postid_v_fact_guid_key ON public.t_vio USING btree (v_fact_postid, v_fact_guid);
--CREATE UNIQUE INDEX CONCURRENTLY sessiondata_uniq ON sessiondata (cid, imgfull, session_id, track_id);
--ALTER TABLE sessiondata ADD CONSTRAINT sessiondata_uniq_cons UNIQUE USING INDEX sessiondata_uniq;
ALTER TABLE sessiondata ADD CONSTRAINT sessiondata_uniq UNIQUE (cid, imgfull, session_id, track_id);
--CREATE UNIQUE INDEX detections_uniq ON detections (cid, time_marker, session_id, track_id);
--ALTER TABLE detections ADD CONSTRAINT detections_uniq_cons UNIQUE USING INDEX detections_uniq;
ALTER TABLE detections ADD CONSTRAINT detections_uniq UNIQUE (cid, time_marker, session_id, track_id);
INSERT INTO attrs (diagnostic_temp, central_server_url, block_notify_from, use_logger) VALUES (NULL, NULL, NULL, 'off');

ALTER TABLE public.alarms OWNER TO webclient;
ALTER TABLE public.attrs OWNER TO webclient;
ALTER TABLE public.complexlist OWNER TO webclient;
ALTER TABLE public.complexmodes OWNER TO webclient;
ALTER TABLE public.departures OWNER TO webclient;
ALTER TABLE public.detections OWNER TO webclient;
ALTER TABLE public.sessionlist OWNER TO webclient;
ALTER TABLE public.ticker OWNER TO webclient;
ALTER TABLE public.userlist OWNER TO webclient;
ALTER TABLE public.sessiondata OWNER TO webclient;
ALTER TABLE public.contactslist OWNER TO webclient;
ALTER TABLE public.sessions OWNER TO webclient;

-- ALTER TABLE complexlist ADD COLUMN camres VARCHAR(2);
-- ALTER TABLE attrs ADD COLUMN last_send_notify INT;
-- ALTER TABLE attrs ADD COLUMN block_notify_from INT;
-- ALTER TABLE attrs ADD COLUMN use_logger VARCHAR(3);
-- ALTER TABLE detections ADD COLUMN time_marker BIGINT;
-- ALTER TABLE detections ADD COLUMN file_name VARCHAR(45);
