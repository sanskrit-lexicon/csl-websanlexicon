echo "remaking input.txt..."
php make_input.php ../../pywork/ap.xml input.txt
echo "remaking sqlite table..."
rm ap.sqlite
sqlite3 ap.sqlite < def.sql
