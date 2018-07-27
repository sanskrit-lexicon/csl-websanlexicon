DROP TABLE inm;
CREATE TABLE inm (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt inm
create index datum on inm(key);
pragma table_info (inm);
select count(*) from inm;
.exit
