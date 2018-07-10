DROP TABLE pwg;
CREATE TABLE pwg (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pwg
create index datum on pwg(key);
pragma table_info (ap);
select count(*) from pwg;
.exit
