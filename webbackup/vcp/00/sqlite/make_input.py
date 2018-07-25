""" make_input.py (generic)
   Assumes input is utf8-unicode, and similarly writes.
   Jan 31, 2014
   Converts unicode to ascii &#nnnn; entities
   This avoids problems in webtc2/query_gather.php
"""
import re,sys
import codecs
def make(filein,fileout):
 f = codecs.open(filein,encoding='utf-8',mode='r')
 fout = codecs.open(fileout,'w','utf-8')
 n = 0
 for line in f:
  line = line.rstrip()
  if not re.search(r'^<H1>',line):
   continue
  n = n + 1
  m = re.search(r'<key1>(.*?)</key1>.*<L>(.*?)</L>',line)
  if not m:
   print "ERROR: Could not find key1,lnum from line:",line
   exit(1)
  key1 = m.group(1)
  lnum = m.group(2)
  data = line.encode('ascii','xmlcharrefreplace')  # &#nnnn
  out = "%s\t%s\t%s" % (key1,lnum,data)
  fout.write("%s\n" % out)
 f.close()
 fout.close()
#-----------------------------------------------------
if __name__=="__main__":
 filein = sys.argv[1]
 fileout = sys.argv[2]
 make(filein,fileout)
