echo "remaking input.txt..."
php make_input.php ../../pywork/krm.xml input.txt
echo "remaking sqlite table..."
rm krm.sqlite
sqlite3 krm.sqlite < def.sql
