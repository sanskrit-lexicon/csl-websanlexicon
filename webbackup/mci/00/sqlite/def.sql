DROP TABLE mci;
CREATE TABLE mci (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt mci
create index datum on mci(key);
pragma table_info (mci);
select count(*) from mci;
.exit
