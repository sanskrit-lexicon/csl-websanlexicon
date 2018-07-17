DROP TABLE ae;
CREATE TABLE ae (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt ae
create index datum on ae(key);
pragma table_info (ae);
select count(*) from ae;
.exit
