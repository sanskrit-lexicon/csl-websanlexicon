DROP TABLE gra;
CREATE TABLE gra (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt gra
create index datum on gra(key);
pragma table_info (gra);
select count(*) from gra;
.exit
