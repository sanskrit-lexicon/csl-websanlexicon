echo "remaking input.txt..."
php make_input.php ../../pywork/ap90.xml input.txt
rm ap90.sqlite
echo "remaking sqlite table..."
sqlite3 ap90.sqlite < def.sql
