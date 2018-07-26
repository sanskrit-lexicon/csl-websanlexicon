DROP TABLE ccs;
CREATE TABLE ccs (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt ccs
create index datum on ccs(key);
pragma table_info (ccs);
select count(*) from ccs;
.exit
