echo "remaking input.txt..."
php make_input.php ../../pywork/sch.xml input.txt
echo "remaking sqlite table..."
rm sch.sqlite
sqlite3 sch.sqlite < sch.sql
