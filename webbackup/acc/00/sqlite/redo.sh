echo "remaking input.txt..."
php make_input.php ../../pywork/acc.xml input.txt
echo "remaking sqlite table..."
rm acc.sqlite
sqlite3 acc.sqlite < def.sql
