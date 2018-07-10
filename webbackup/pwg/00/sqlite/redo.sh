echo "remaking input.txt..."
php make_input.php ../../pywork/pwg.xml input.txt
echo "remaking sqlite table..."
rm pwg.sqlite
sqlite3 pwg.sqlite < pwg.sql
