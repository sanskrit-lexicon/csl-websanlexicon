""" imagecat.py
"""
import sys,re,codecs
imagetypes = ['pdf','png','jpg']
class Image(object):
 def __init__(self,line,dictlo):
  line = line.rstrip('\r\n')
  parts = line.split(':')
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
 return dobj

cats = ['vp','p','x']
titles = ['dictlo','#vp','#p','#other','img-type']

alldicts = ["acc","ae","ap90","ben","bhs","bop","bor","bur","cae",
"ccs","gra","gst","ieg","inm","krm","mci","md","mw","mw72",
"mwe","pe","pgn","pui","pw","pwg","sch","shs","skd",
"snp","stc","vcp","vei","wil","yat"]

def totals(allrecs):
 catcount = [0,0,0]
 for dictlo in alldicts:
  drec = allrecs[dictlo]
  for i in [0,1,2]:
   catcount[i] = catcount[i] + drec.catcount[i]
 outarr = ['ALL'] + ['%s'%c for c in catcount] + ['']
 return outarr

if __name__ == "__main__":
 #dictlo = sys.argv[1]
 #fileout = sys.argv[2]
 allrecs = {}
 for dictlo in alldicts:
  drec = one_dict(dictlo)
  allrecs[dictlo] = drec

  outarr = [dictlo] + ['%s'%c for c in drec.catcount] + [drec.imagetype]
  print('|'.join(outarr))
 # totals
 outarr = totals(allrecs)
 print('|'.join(outarr))
