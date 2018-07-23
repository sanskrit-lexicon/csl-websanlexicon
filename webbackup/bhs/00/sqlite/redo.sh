echo "remaking input.txt..."
php make_input.php ../../pywork/bhs.xml input.txt
rm bhs.sqlite
echo "remaking sqlite table..."
sqlite3 bhs.sqlite < def.sql
