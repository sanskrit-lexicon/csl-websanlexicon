echo "remaking input.txt..."
php make_input.php ../../pywork/shs.xml input.txt
echo "remaking sqlite table..."
rm shs.sqlite
sqlite3 shs.sqlite < def.sql
