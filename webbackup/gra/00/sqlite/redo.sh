echo "remaking input.txt..."
php make_input.php ../../pywork/gra.xml input.txt
rm gra.sqlite
echo "remaking sqlite table..."
sqlite3 gra.sqlite < def.sql
