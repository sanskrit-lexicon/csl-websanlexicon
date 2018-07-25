DROP TABLE shs;
CREATE TABLE shs (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt shs
create index datum on shs(key);
pragma table_info (shs);
select count(*) from shs;
.exit
