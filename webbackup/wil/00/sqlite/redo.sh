echo "remaking input.txt..."
php make_input.php ../../pywork/wil.xml input.txt
echo "remaking sqlite table..."
rm wil.sqlite
sqlite3 wil.sqlite < def.sql
