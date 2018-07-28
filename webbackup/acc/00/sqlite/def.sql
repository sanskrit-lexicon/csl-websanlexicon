DROP TABLE acc;
CREATE TABLE acc (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt acc
create index datum on acc(key);
pragma table_info (acc);
select count(*) from acc;
.exit
