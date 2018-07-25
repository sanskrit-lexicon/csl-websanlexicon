echo "remaking input.txt..."
php make_input.php ../../pywork/yat.xml input.txt
echo "remaking sqlite table..."
rm yat.sqlite
sqlite3 yat.sqlite < def.sql
