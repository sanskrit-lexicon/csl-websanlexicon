echo "remaking input.txt..."
php make_input.php ../../pywork/pe.xml input.txt
rm pe.sqlite
echo "remaking sqlite table..."
sqlite3 pe.sqlite < def.sql
