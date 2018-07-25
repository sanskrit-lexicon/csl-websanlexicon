DROP TABLE bor;
CREATE TABLE bor (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt bor
create index datum on bor(key);
pragma table_info (bor);
select count(*) from bor;
.exit
