DROP TABLE yat;
CREATE TABLE yat (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt yat
create index datum on yat(key);
pragma table_info (yat);
select count(*) from yat;
.exit
