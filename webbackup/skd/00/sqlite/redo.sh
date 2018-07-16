echo "remaking input.txt..."
php make_input.php ../../pywork/skd.xml input.txt
echo "remaking sqlite table..."
rm skd.sqlite
sqlite3 skd.sqlite < skd.sql
