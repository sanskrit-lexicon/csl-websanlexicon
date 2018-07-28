echo "remaking input.txt..."
php make_input.php ../../pywork/vei.xml input.txt
echo "remaking sqlite table..."
rm vei.sqlite
sqlite3 vei.sqlite < def.sql
