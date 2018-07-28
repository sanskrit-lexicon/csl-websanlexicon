DROP TABLE sch;
CREATE TABLE sch (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt sch
create index datum on sch(key);
pragma table_info (pwg);
select count(*) from sch;
.exit
