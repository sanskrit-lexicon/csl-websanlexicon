DROP TABLE ieg;
CREATE TABLE ieg (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt ieg
create index datum on ieg(key);
pragma table_info (ieg);
select count(*) from ieg;
.exit
