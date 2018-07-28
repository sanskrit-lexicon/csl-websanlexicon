echo "remaking input.txt..."
php make_input.php ../../pywork/pui.xml input.txt
echo "remaking sqlite table..."
rm pui.sqlite
sqlite3 pui.sqlite < def.sql
