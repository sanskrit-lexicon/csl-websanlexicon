""" imagesequence.py
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

def refs_p_sequence(dictlo,refs,n1,n2,ninc,missing):
 n = len(refs)
 ix = 0
 for i,ref in enumerate(refs):
  j = i + ix
  refcalc = (n1 + (j*ninc))
  if refcalc in missing:
   ix = ix + 1
   pass
  elif refcalc != ref:
   print(dictlo,"problem at line %s: %s != %s" %(i+1,ref,refcalc))
   exit(1)
  i = i + 1
 j = n + len(missing)-1
 refcalc = (n1 + (j*ninc))
 if refcalc != n2:
  print(dictlo,"maximum error: %s != %s" %(n2,refcalc))
  exit(1)
 return True

def refs_p_snp(dictlo,refs):
 refs1 = [r for r in refs if (520<=r<=611)]
 refs2 = [r for r in refs if not r in refs1]

 refs_p_sequence(dictlo,refs1,520,611,1,[])
 refs_p_sequence(dictlo,refs2,425,465,1,[])

 print(dictlo,"page pattern in two parts")
 print("  ",dictlo,"  part1:",len(refs1),520,611,1)
 print("  ",dictlo,"  part2:",len(refs2),425,465,1)

def refs_p_pw(dictlo,refs):
 subrefs = {}
 vols = [1,2,3,4,5,6,7]
 for v in vols:
  subrefs[v] = [r for r in refs if 0<=(r-1000*v) < 1000]
 print(dictlo,'page pattern in 7 parts')
 for v in vols:
  refs1 = subrefs[v]
  n1 = refs1[0]
  n2 = refs1[-1]
  ninc = 1
  missing = []
  refs_p_sequence(dictlo,refs1,n1,n2,ninc,missing)
  print("  ",dictlo,"  part%s:"%v,len(refs1),n1,n2,ninc)

def refs_p_yat(dictlo,refs):
 n1 = 1
 n2 =928
 ninc = 1
 n = len(refs)
 missing = [924,925,926,927]  # missing from our scanned edition.
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 temp = ['%s'% m for m in missing]
 missing_str = ','.join(temp)
 print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc,'missing=',missing_str)

def refs_p_vcp(dictlo,refs):
 n1 = 35
 n2 = 5441
 ninc = 1
 n = len(refs)
 missing = []  # 
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc)

def refs_p_stc(dictlo,refs):
 n1 = 1
 n2 = 894
 ninc = 1
 n = len(refs)
 missing = [580]  # between 'mleC' and ya'
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 if missing == []:
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc)
 else:
  temp = ['%s'% m for m in missing]
  missing_str = ','.join(temp)
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc,'missing=',missing_str)

def refs_p_sch(dictlo,refs):
 n1 = 1
 n2 = 396
 ninc = 1
 n = len(refs)
 missing = [380]
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 if missing == []:
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc)
 else:
  temp = ['%s'% m for m in missing]
  missing_str = ','.join(temp)
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc,'missing=',missing_str)

def refs_p_gra(dictlo,refs):
 n1 = 1
 n2 = 1775
 ninc = 2
 n = len(refs)
 missing = []
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 if missing == []:
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc)
 else:
  temp = ['%s'% m for m in missing]
  missing_str = ','.join(temp)
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc,'missing=',missing_str)

def refs_p_bur(dictlo,refs):
 n1 = 4
 n2 = 758
 ninc = 2
 n = len(refs)
 missing = []
 refs_p_sequence(dictlo,refs,n1,n2,ninc,missing)
 if missing == []:
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc)
 else:
  temp = ['%s'% m for m in missing]
  missing_str = ','.join(temp)
  print(dictlo.ljust(4),"COMPLX page pattern",n,n1,n2,ninc,'missing=',missing_str)

def analyze_refs_p(dobj,refs):
 #print('analyze_refs_p',dobj.dictlo)
 n = len(refs)
 irefs = [int(ref) for ref in refs]
 first = irefs[0]
 last = irefs[-1]
 if (first == 1) and (last == n):
  status = 'SIMPLE'
  ninc = 1
  print(dobj.dictlo.ljust(4),'SIMPLE page pattern',n,first,last,ninc)
  return
 dictlo = dobj.dictlo
 if dictlo == 'bur':
  refs_p_bur(dictlo,irefs)
 elif dictlo == 'gra':
  refs_p_gra(dictlo,irefs)
 elif dictlo == 'sch':
  refs_p_sch(dictlo,irefs)
 elif dictlo == 'stc':
  refs_p_stc(dictlo,irefs)
 elif dictlo == 'vcp':
  refs_p_vcp(dictlo,irefs)
 elif dictlo == 'yat':
  refs_p_yat(dictlo,irefs)
 elif dictlo == 'snp':
  refs_p_snp(dictlo,irefs)
 elif dictlo == 'pw':
  refs_p_pw(dictlo,irefs)
 else:
  status = 'COMPLEX'
  print('%6s'%status,dobj.dictlo,first,last,n)

def refs_vp_generic(dictlo,refs,vols,ninc=1):
 subrefs = {}
 for v in vols:
  v1 = '%s-'%v
  subrefs[v] = [int(r[2:]) for r in refs if r.startswith(v1)]
 print(dictlo.ljust(4),'regular vol-page pattern in %s volumes'%len(vols))
 for v in vols:
  refs1 = subrefs[v]
  n1 = refs1[0]
  n2 = refs1[-1]
  #ninc = 1
  missing = []
  refs_p_sequence(dictlo,refs1,n1,n2,ninc,missing)
  print("  ",dictlo,"  part%s:"%v,len(refs1),n1,n2,ninc)

def analyze_refs_vp(dobj,refs):
 n = len(refs)
 dictlo = dobj.dictlo
 if dictlo in ['acc','pui']:
  refs_vp_generic(dictlo,refs,[1,2,3])
 elif dictlo == 'pwg':
  refs_vp_generic(dictlo,refs,[1,2,3,4,5,6,7],ninc=2)
 elif dictlo == 'skd':
  refs_vp_generic(dictlo,refs,[1,2,3,4,5])
 elif dictlo == 'vei':
  refs_vp_generic(dictlo,refs,[1,2])
 else:
  print(dobj.dictlo,'VP NOT ANAYZED!!!!')

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

def totals(allrecs):
 catcount = [0,0,0]
 for dictlo in alldicts:
  drec = allrecs[dictlo]
  for i in [0,1,2]:
   catcount[i] = catcount[i] + drec.catcount[i]
 outarr = ['ALL'] + ['%s'%c for c in catcount] + ['']
 return outarr

if __name__ == "__main__":
 allrecs = {}
 for dictlo in alldicts:
  drec = one_dict(dictlo)
  allrecs[dictlo] = drec

  outarr = [dictlo] + ['%s'%c for c in drec.catcount] + [drec.imagetype]
  #print('|'.join(outarr))
 # totals
 outarr = totals(allrecs)
 #print('|'.join(outarr))
