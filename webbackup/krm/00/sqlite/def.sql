DROP TABLE krm;
CREATE TABLE krm (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt krm
create index datum on krm(key);
pragma table_info (krm);
select count(*) from krm;
.exit
