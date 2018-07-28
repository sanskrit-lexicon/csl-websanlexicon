DROP TABLE snp;
CREATE TABLE snp (
 key VARCHAR(100)  NOT NULL,
 lnum DECIMAL(10,2) UNIQUE,
 data TEXT NOT NULL
);
.separator "\t"
.import input.txt snp
create index datum on snp(key);
pragma table_info (snp);
select count(*) from snp;
.exit
