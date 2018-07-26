echo "remaking input.txt..."
php make_input.php ../../pywork/mw72.xml input.txt
echo "remaking sqlite table..."
rm mw72.sqlite
sqlite3 mw72.sqlite < def.sql
# removing input.txt
rm input.txt
