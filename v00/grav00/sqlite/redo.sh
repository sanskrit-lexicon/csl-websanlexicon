echo "remaking input.txt..."
php make_input.php ../../pywork/${dictlo}.xml input.txt
rm ${dictlo}.sqlite
echo "remaking sqlite table..."
sqlite3 ${dictlo}.sqlite < def.sql
