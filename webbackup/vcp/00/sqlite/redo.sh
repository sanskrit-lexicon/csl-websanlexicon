echo "remaking input.txt..."
python make_input.py ../../pywork/vcp.xml input.txt
echo "remaking sqlite table..."
rm vcp.sqlite
sqlite3 vcp.sqlite < vcp.sql
