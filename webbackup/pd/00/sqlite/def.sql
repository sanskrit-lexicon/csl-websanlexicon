DROP TABLE pd;
CREATE TABLE pd (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pd
create index datum on pd(key);
pragma table_info (pd);
select count(*) from pd;
.exit
