DROP TABLE wil;
CREATE TABLE wil (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt wil
create index datum on wil(key);
pragma table_info (wil);
select count(*) from wil;
.exit
