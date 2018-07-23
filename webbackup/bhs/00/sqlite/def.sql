DROP TABLE bhs;
CREATE TABLE bhs (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt bhs
create index datum on bhs(key);
pragma table_info (bhs);
select count(*) from bhs;
.exit
