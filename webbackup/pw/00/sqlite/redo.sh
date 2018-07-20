echo "remaking input.txt..."
php make_input.php ../../pywork/pw.xml input.txt
echo "remaking sqlite table..."
rm pw.sqlite
sqlite3 pw.sqlite < def.sql
