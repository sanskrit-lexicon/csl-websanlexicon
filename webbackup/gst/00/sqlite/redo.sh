echo "remaking input.txt..."
php make_input.php ../../pywork/gst.xml input.txt
rm gst.sqlite
echo "remaking sqlite table..."
sqlite3 gst.sqlite < def.sql
