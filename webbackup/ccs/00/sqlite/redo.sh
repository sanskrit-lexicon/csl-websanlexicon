echo "remaking input.txt..."
php make_input.php ../../pywork/ccs.xml input.txt
rm ccs.sqlite
echo "remaking sqlite table..."
sqlite3 ccs.sqlite < def.sql
