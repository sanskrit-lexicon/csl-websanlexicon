""" removedup.py
"""
import sys,re,codecs,os,tempfile
imagetypes = ['pdf','png','jpg']
class Image(object):
 def __init__(self,line,dictlo):
  line = line.rstrip('\r\n')
  parts = line.split(':')
  self.line = line
  self.ref = parts[0]
  self.name = parts[1]
  try:
   self.title = parts[2]
  except IndexError:
   # pw, stc, wil, gra
   if dictlo not in ['gra','pw','stc','wil']:
    raise IndexError(
     "%s: line has no title field (allowed only for gra/pw/stc/wil); offending line: %s"
     % (dictlo,line))
   self.title = 'NONE'

  if re.search(r'^[1-9]-[0-9]+$',self.ref):
   self.cat = 'vp' # volume-page
  elif re.search(r'^[0-9]+$',self.ref):
   self.cat = 'p'  # page
  else:
   self.cat = 'x'  # other

  self.imagetype='NONE'
  for t in imagetypes:
   if self.name.endswith('.' + t):
    self.imagetype=t
  if self.imagetype == 'NONE':
   print(dictlo,"unknown image type(%s)"%self.name)
   exit(1)

def get_imagetype(recs):
 t0 = recs[0].imagetype
 types = list(set([r.imagetype for r in recs]))
 if len(types) == 1:
  return types[0]
 else:
  print('multiple image types:',types)
  return ','.join(types)

class Dict(object):
 def __init__(self,recs,dictlo):
  self.dictlo = dictlo
  self.recs = recs

def sequences(dobj):
 # H7960: the old analyze_refs_p/analyze_refs_vp analysis stubs are gone
 # (they computed SIMPLE/COMPLEX and discarded it / were empty). Only the
 # observable guard stays: a dict whose scan refs mix vp and p classes is
 # an error.
 if (dobj.catcount[0] != 0) and (dobj.catcount[1] != 0):
  print('sequences error. ',dobj.dictlo)
  exit(1)


def one_dict(dictlo):
 pdffiles_filename = "../distinctfiles/%s/web/webtc/pdffiles.txt"%dictlo
 with open(pdffiles_filename,"r",encoding="utf-8") as f:
  recs = [Image(x,dictlo) for x in f]
 dobj = Dict(recs,dictlo)

 catdata = []
 catcount = []
 for cat in cats:
  reccat = [r for r in recs if r.cat == cat]
  catdata.append(reccat)
  catcount.append(len(reccat))
 imagetype=get_imagetype(recs)
 dobj.catdata = catdata
 dobj.catcount = catcount
 dobj.imagetype = imagetype
 sequences(dobj)
 return dobj

cats = ['vp','p','x']
titles = ['dictlo','#vp','#p','#other','img-type']

alldicts = ["acc","ae","ap90","ben","bhs","bop","bor","bur","cae",
"ccs","gra","gst","ieg","inm","krm","mci","md","mw","mw72",
"mwe","pe","pgn","pui","pw","pwg","sch","shs","skd",
"snp","stc","vcp","vei","wil","yat"]

def find_dups(dictlo):
 dobj = one_dict(dictlo)
 recs = dobj.recs
 newrecs = []
 prevref = None
 dups = []
 for i,imagerec in enumerate(recs):
  if prevref == imagerec.ref:
   dups.append(imagerec)
  else:
   newrecs.append(imagerec)
   prevref = imagerec.ref
 return dups,newrecs,recs

def atomic_write_lines(path,lines):
 # H7960: crash-safe rewrite — write a temp file in the SAME directory,
 # then os.replace() it over the target. A crash or failure mid-write can
 # never leave a half-written (row-losing) pdffiles.txt behind.
 directory = os.path.dirname(path) or '.'
 fd,tmp = tempfile.mkstemp(dir=directory,prefix='.removedup-',suffix='.tmp')
 try:
  with os.fdopen(fd,'w',encoding='utf-8') as f:
   for line in lines:
    f.write(line + '\n')
   f.flush()
   os.fsync(f.fileno())
  os.replace(tmp,path)
  tmp = None
 finally:
  if tmp is not None:
   try:
    os.unlink(tmp)
   except OSError:
    pass

def remove_dups(dictlo):
 dups,newrecs,recs = find_dups(dictlo)
 if len(dups) == 0:
  print(dictlo,"no duplicates")
  return
 print(dictlo,len(recs),'#dups=',len(dups),len(newrecs))
 pdffiles_filename = "../distinctfiles/%s/web/webtc/pdffiles.txt"%dictlo
 fileout = '%s_dups.txt' %dictlo
 # H7960: the removed-duplicates file is written BEFORE the source is
 # replaced, so no failure path can lose pdffiles.txt rows.
 with open(fileout,"w",encoding="utf-8") as f:
  for rec in dups:
   line = rec.line
   f.write(line + '\n')
 atomic_write_lines(pdffiles_filename,[rec.line for rec in newrecs])
 print(len(newrecs),"records without dups written to",pdffiles_filename)
 print(len(dups),"dups written to",fileout)

def listdups():
 for dictlo in alldicts:
  dups,newrecs,recs = find_dups(dictlo)
  if len(dups) == 0:
   #print(dictlo,"no duplicates")
   pass
  else:
   print(dictlo,len(recs),'#dups=',len(dups),len(newrecs))
 return

USAGE = "usage: removedup.py list | removedup.py remove <dictlo>"

def main(argv):
 if len(argv) == 2 and argv[1] == 'list':
  listdups()
  return 0
 if len(argv) == 3 and argv[1] == 'remove':
  remove_dups(argv[2])
  return 0
 print(USAGE,file=sys.stderr)
 return 2

if __name__ == "__main__":
 sys.exit(main(sys.argv))
