DROP TABLE mwe;
CREATE TABLE mwe (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt mwe
create index datum on mwe(key);
pragma table_info (mwe);
select count(*) from mwe;
.exit
