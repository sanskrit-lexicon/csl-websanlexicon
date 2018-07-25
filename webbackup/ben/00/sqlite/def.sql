DROP TABLE ben;
CREATE TABLE ben (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt ben
create index datum on ben(key);
pragma table_info (ben);
select count(*) from ben;
.exit
