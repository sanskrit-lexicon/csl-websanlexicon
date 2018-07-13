echo "remaking input.txt..."
php make_input.php ../../pywork/mw.xml input.txt
echo "remaking sqlite table..."
rm mw.sqlite
sqlite3 mw.sqlite < def.sql
#echo "Removing input.txt"
#rm input.txt
echo "Keeping input.txt"
echo "done with web/sqlite/redo.sh"
