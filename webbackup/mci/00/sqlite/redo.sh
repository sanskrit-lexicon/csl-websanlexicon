echo "remaking input.txt..."
php make_input.php ../../pywork/mci.xml input.txt
echo "remaking sqlite table..."
rm mci.sqlite
sqlite3 mci.sqlite < def.sql
