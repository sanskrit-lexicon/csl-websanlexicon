DROP TABLE stc;
CREATE TABLE stc (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt stc
create index datum on stc(key);
pragma table_info (stc);
select count(*) from stc;
.exit
