echo "remaking input.txt..."
php make_input.php ../../pywork/inm.xml input.txt
echo "remaking sqlite table..."
rm inm.sqlite
sqlite3 inm.sqlite < def.sql
