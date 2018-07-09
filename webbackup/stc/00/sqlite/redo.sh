echo "remaking input.txt..."
php make_input.php ../../pywork/stc.xml input.txt
echo "remaking sqlite table..."
rm stc.sqlite
sqlite3 stc.sqlite < def.sql
