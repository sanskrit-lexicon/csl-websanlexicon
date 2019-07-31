"""delete_empty_sqlite.py  Jul 30, 2019
  For some dictionaries, there are empty xyz.sqlite files in web/sqlite.
  Identify and then delete these.
  python delete_empty_sqlite.py > delete_empty_sqlite.txt
"""
import sys,re
import codecs
import os
# dictyear has all dictionary codes, with the 'year'.
# This 'year' is required to locate the files
# This is a Python dictionary data structure, quite like a PHP associative array
dictyear={"ACC":"2014" , "AE":"2014" , "AP":"2014" , "AP90":"2014",
       "BEN":"2014" , "BHS":"2014" , "BOP":"2014" , "BOR":"2014",
       "BUR":"2013" , "CAE":"2014" , "CCS":"2014" , "GRA":"2014",
       "GST":"2014" , "IEG":"2014" , "INM":"2013" , "KRM":"2014",
       "MCI":"2014" , "MD":"2014" , "MW":"2014" , "MW72":"2014",
       "MWE":"2013" , "PD":"2014" , "PE":"2014" , "PGN":"2014",
       "PUI":"2014" , "PWG":"2013" , "PW":"2014" , "SCH":"2014",
       "SHS":"2014" , "SKD":"2013" , "SNP":"2014" , "STC":"2013",
       "VCP":"2013" , "VEI":"2014" , "WIL":"2014" , "YAT":"2014"}
standard_fonts = 'CharterIndoCapital.otf, Old Standard Indologique-Italic.otf, Old Standard Indologique-Italic.ttf, Old Standard Indologique-Regular.otf, oldstandard.otf, oldstandarditalic.otf, praja.ttf, sanskrit2003.ttf, siddhanta.ttf'

class wslRec(object):
 # csl-websanlexicon inventory.txt
 dirs = {}
 def __init__(self,line):
  line = line.rstrip('\r\n')
  (self.path,self.type) = line.split(':') # xyz/w.txt
  #self.webpath = 'web/%s' %self.path  # web/xyz/w.txt
  self.flag=False   # is this file in the dictionary's web directory?
 
def init_wslRecs(filename):
 recs = []
 with codecs.open(filename,"r","utf-8") as f:
  for line in f:
   if line.startswith(';'):
    continue # comment
   recs.append(wslRec(line))
 return recs

def walkdir(dirweb):
 paths = []
 start_path=dirweb 
 npdfs=0
 for dirpath, dirnames, filenames in os.walk(start_path):
  for f in filenames:
   path = os.path.join(dirpath,f)
   path = path.replace(dirweb ,'web')
   #path = os.path.normpath(path)
   if path.find('web/pdfpages') != -1:
    #npdfs=npdfs+1
    path = re.sub('^web/','',path)
    paths.append(path)
   else:
    path = re.sub('^web/','',path)
    paths.append(path)
 #print('#pdf pages =',npdfs)
 #paths.append('pdfpages %s' %npdfs)
 #paths.sort()
 #for p in paths:
 # print(p)
 return paths
 #print('dbg end in walkdir')
 #exit(1)

def getinfo(code):
  year = dictyear[code]
  dirmain = "%sScan/%s" %(code,year)
  # Take into account relative location of this program file
  dirbase = "../../../" + dirmain
  dirweb = "%s/web" %dirbase
  #print('dirweb=%s'%dirweb)
  filenames = walkdir(dirweb)
  #filenames = os.listdir(dirweb)
  files = []
  dirs = {}
  recs = [] 
  for filename in filenames:
   path = "%s/%s" %(dirweb,filename)
   if path.endswith('.sqlite'):
    # get size of file
    statinfo = os.stat(path)
    size = statinfo.st_size
    if size == 0:
     #print(size,path)
     recs.append(path)
    elif (size != 0) and not path.endswith('%s.sqlite' % code.lower()):
     # information on existing non-empty sqlite files, except the main
     print('FYI:',size,path)
  return recs   # contains ONLY 0-size sqlite files


if __name__=="__main__":
 #fileout = sys.argv[1] # output path
 outdir = {}
 for code in dictyear:
  #if code != 'ACC':
  # continue #debug
  outdir[code] = getinfo(code)
 for code in outdir:
  for rec in outdir[code]:
   print('removing empty sqlite file',rec)
   os.remove(rec)
 exit(0)  # no output to fileout 
 with codecs.open(fileout,"w","utf-8") as f:
  for code in outdir:
   for rec in outdir[code]:
    print('empty sqlite file',rec)
   continue
   year = dictyear[code]
   dirmain = "%sScan/%s" %(code,year)
   outline = dirmain
   f.write('%s\n' % outline)
   recs_diff(wslrecs,outdir[code])
   recs_diff(outdir[code],wslrecs)
   # records in wslrecs but not in dictionary web directory
   n=len([rec for rec in wslrecs if not rec.flag])
   f.write('%s files in csl-websanlexicon but not in web directory for %s\n'%
           (n,code))
   outlines = diff_details(wslrecs)
   for outline in outlines:
    f.write('%s\n' % outline)
   # records in dictionary web directory  but not in wslrecs
   n=len([rec for rec in outdir[code] if not rec.flag and not rec.path.startswith('pdfpages')])
   f.write('%s files in web directory (excluding pdfpages) for %s but not in csl-websanlexicon \n'%
           (n,code))
   
   outlines = diff_details(outdir[code])
   for outline in outlines:
    f.write('%s\n' % outline)
   outline = '-'*60
   f.write('%s\n' % outline)
   f.write('\n')
