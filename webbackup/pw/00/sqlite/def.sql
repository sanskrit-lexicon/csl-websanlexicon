DROP TABLE pw;
CREATE TABLE pw (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt pw
create index datum on pw(key);
pragma table_info (pw);
select count(*) from pw;
.exit
