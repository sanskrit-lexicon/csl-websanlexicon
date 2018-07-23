DROP TABLE ap;
CREATE TABLE ap (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt ap
create index datum on ap(key);
pragma table_info (ap);
select count(*) from ap;
.exit
