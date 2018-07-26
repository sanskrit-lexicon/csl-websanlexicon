DROP TABLE mw72;
CREATE TABLE mw72 (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt mw72
create index datum on mw72(key);
pragma table_info (mw72);
select count(*) from mw72;
.exit
