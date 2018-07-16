DROP TABLE skd;
CREATE TABLE skd (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt skd
create index datum on skd(key);
pragma table_info (skd);
select count(*) from skd;
.exit
