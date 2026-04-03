""" removedup.py
"""
import sys,re,codecs
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
  except:
   # pw, stc, wil, gra
   assert dictlo in ['gra','pw','stc','wil']
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

def analyze_refs_p(dobj,refs):
 n = len(refs)
 irefs = [int(ref) for ref in refs]
 first = irefs[0]
 last = irefs[-1]
 if (first == 1) and (last == n):
  status = 'SIMPLE'
 else:
  status = 'COMPLEX'
 if status != 'SIMPLE':
  #print('%6s'%status,dobj.dictlo,first,last,n)
  pass

def analyze_refs_vp(dobj,refs):
 n = len(refs)
 #print(dobj.dictlo,'vp not analyzed')

def sequences(dobj):
 if dobj.catcount[0] == 0: 
  # page
  refs = [r.ref for r in dobj.recs if re.search(r'^[0-9]+$',r.ref)]
  analyze_refs_p(dobj,refs)
 elif dobj.catcount[1] == 0:
  refs = [r.ref for r in dobj.recs if re.search(r'^[1-9]-[0-9]+$',r.ref)]
  analyze_refs_vp(dobj,refs)
 else:
  print('sequences error. ',dobj.dictlo)
  exit(1)

 
def one_dict(dictlo):
 pdffiles_filename = "../distinctfiles/%s/web/webtc/pdffiles.txt"%dictlo
 with codecs.open(pdffiles_filename,"r","utf-8") as f:
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

def remove_dups(dictlo):
 dups,newrecs,recs = find_dups(dictlo)
 if len(dups) == 0:
  print(dictlo,"no duplicates")
  return
 print(dictlo,len(recs),'#dups=',len(dups),len(newrecs))
 pdffiles_filename = "../distinctfiles/%s/web/webtc/pdffiles.txt"%dictlo
 with codecs.open(pdffiles_filename,"w","utf-8") as f:
  for rec in newrecs:
   line = rec.line
   f.write(line + '\n')
 print(len(newrecs),"records without dups written to",pdffiles_filename)
 fileout = '%s_dups.txt' %dictlo
 with codecs.open(fileout,"w","utf-8") as f:
  for rec in dups:
   line = rec.line
   f.write(line + '\n')
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
if __name__ == "__main__":
 option = 2 #1
 if option == 1:
  listdups()
 elif option == 2:
  dictlo = sys.argv[1]
  remove_dups(dictlo)
