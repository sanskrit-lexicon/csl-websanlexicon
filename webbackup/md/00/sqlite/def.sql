DROP TABLE md;
CREATE TABLE md (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt md
create index datum on md(key);
pragma table_info (md);
select count(*) from md;
.exit
