echo "remaking input.txt..."
php make_input.php ../../pywork/ben.xml input.txt
echo "remaking sqlite table..."
rm ben.sqlite
sqlite3 ben.sqlite < def.sql
