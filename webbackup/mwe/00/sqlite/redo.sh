echo "remaking input.txt..."
php make_input.php ../../pywork/mwe.xml input.txt
echo "remaking sqlite table..."
rm mwe.sqlite
sqlite3 mwe.sqlite < mwe.sql
