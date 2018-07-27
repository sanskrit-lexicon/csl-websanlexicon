echo "remaking input.txt..."
php make_input.php ../../pywork/ieg.xml input.txt
echo "remaking sqlite table..."
rm ieg.sqlite
sqlite3 ieg.sqlite < def.sql
