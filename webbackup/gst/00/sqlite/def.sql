DROP TABLE gst;
CREATE TABLE gst (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt gst
create index datum on gst(key);
pragma table_info (gst);
select count(*) from gst;
.exit
