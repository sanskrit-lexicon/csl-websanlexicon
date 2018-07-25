DROP TABLE vcp;
CREATE TABLE vcp (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt vcp
create index datum on vcp(key);
pragma table_info (vcp);
select count(*) from vcp;
.exit
