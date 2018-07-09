DROP TABLE cae;
CREATE TABLE cae (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt cae
create index datum on cae(key);
pragma table_info (cae);
select count(*) from cae;
.exit
