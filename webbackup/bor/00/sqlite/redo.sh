echo "remaking input.txt..."
php make_input.php ../../pywork/bor.xml input.txt
echo "remaking sqlite table..."
rm bor.sqlite
sqlite3 bor.sqlite < def.sql
