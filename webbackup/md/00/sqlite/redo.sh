echo "remaking input.txt..."
php make_input.php ../../pywork/md.xml input.txt
echo "remaking sqlite table..."
rm md.sqlite
sqlite3 md.sqlite < def.sql
