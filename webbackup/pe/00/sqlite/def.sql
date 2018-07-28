DROP TABLE pe;
CREATE TABLE pe (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pe
create index datum on pe(key);
pragma table_info (pe);
select count(*) from pe;
.exit
