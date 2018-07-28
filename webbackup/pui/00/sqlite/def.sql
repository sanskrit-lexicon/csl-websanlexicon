DROP TABLE pui;
CREATE TABLE pui (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pui
create index datum on pui(key);
pragma table_info (pui);
select count(*) from pui;
.exit
