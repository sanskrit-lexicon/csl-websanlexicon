DROP TABLE bop;
CREATE TABLE bop (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt bop
create index datum on bop(key);
pragma table_info (bop);
select count(*) from bop;
.exit
