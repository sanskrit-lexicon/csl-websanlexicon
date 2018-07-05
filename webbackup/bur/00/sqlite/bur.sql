DROP TABLE bur;
CREATE TABLE bur (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt bur
create index datum on bur(key);
pragma table_info (bur);
select count(*) from bur;
.exit
