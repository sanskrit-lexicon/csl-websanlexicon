echo "remaking input.txt..."
php make_input.php ../../pywork/pgn.xml input.txt
echo "remaking sqlite table..."
rm pgn.sqlite
sqlite3 pgn.sqlite < def.sql
