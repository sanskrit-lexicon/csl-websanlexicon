echo "remaking input.txt..."
php make_input.php ../../pywork/snp.xml input.txt
echo "remaking sqlite table..."
rm snp.sqlite
sqlite3 snp.sqlite < def.sql
