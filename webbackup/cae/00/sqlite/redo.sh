echo "remaking input.txt..."
php make_input.php ../../pywork/cae.xml input.txt
echo "remaking sqlite table..."
rm cae.sqlite
sqlite3 cae.sqlite < def.sql
