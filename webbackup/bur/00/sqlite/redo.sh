echo "remaking input.txt..."
php make_input.php ../../pywork/bur.xml input.txt
echo "remaking sqlite table..."
rm bur.sqlite
sqlite3 bur.sqlite < bur.sql
