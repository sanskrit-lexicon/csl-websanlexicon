echo "remaking input.txt..."
rm ae.sqlite
php make_input.php ../../pywork/ae.xml input.txt
echo "remaking sqlite table..."
sqlite3 ae.sqlite < def.sql
