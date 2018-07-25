echo "remaking input.txt..."
php make_input.php ../../pywork/bop.xml input.txt
echo "remaking sqlite table..."
rm bop.sqlite
sqlite3 bop.sqlite < def.sql
