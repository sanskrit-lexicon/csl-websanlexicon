DROP TABLE vei;
CREATE TABLE vei (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt vei
create index datum on vei(key);
pragma table_info (vei);
select count(*) from vei;
.exit
