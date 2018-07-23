echo "remaking input.txt..."
php make_input.php ../../pywork/pd.xml input.txt
echo "remaking sqlite table..."
rm pd.sqlite
sqlite3 pd.sqlite < def.sql
