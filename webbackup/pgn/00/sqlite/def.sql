DROP TABLE pgn;
CREATE TABLE pgn (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pgn
create index datum on pgn(key);
pragma table_info (pgn);
select count(*) from pgn;
.exit
