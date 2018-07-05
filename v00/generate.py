# coding=utf-8
""" backup.py
 Mar 30, 2018
  
"""
import sys,re
import codecs
import os.path,time
from shutil import copyfile
from dictparms import alldictparms
# use mako templates
from mako.template import Template

def makedirs(webdirname):
 if not os.path.exists(webdirname):
  os.makedirs(webdirname)
 else:
  print('makedirs WARNING: target directory already exists:',webdirname)
  return
 subdirs = ['images','js','sqlite',
  'utilities','utilities/transcoder',
  'mobile1','webtc','webtc2',
  'webtc1','webtc1/help','webtc1/help/images','webtc1/transcoderjs']
 for subdir in subdirs:
  os.makedirs('%s/%s' %(webdirname,subdir))

def copyfiles(filenames,olddir,newdir):
 for filename in filenames:
  src = '%s/%s' %(olddir,filename)
  dst = '%s/%s' %(newdir,filename)
  copyfile(src,dst)
 print(len(filenames),'copied from',olddir,'to',newdir)

def init_inventory(filein):
 # read inventory. all paths assumed relative
 ans = []
 with codecs.open(filein,"r","utf-8") as f:
  for x in f:
   if x.startswith(';'): # comment
    continue 
   x = x.rstrip('\r\n')
   (filename,category) = x.split(':')
   ans.append((category,filename))
 return ans
if __name__=="__main__":
 dictcode = sys.argv[1]
 filein = sys.argv[2]  # inventory
 oldweb = sys.argv[3]  # "templates"
 newweb = sys.argv[4]
 # make newweb directory, and needed subdirectories
 makedirs(newweb)
 dictparms = alldictparms[dictcode]
 inventory = init_inventory(filein)
 # copy
 for category,filename in inventory:
  filename1 = "%s/%s" %(oldweb,filename)
  newfile = "%s/%s" %(newweb,filename)
  if category == 'C':
   # just copy
   copyfile(filename1,newfile)
  elif category == 'T':
   # process as a template
   template = Template(filename=filename1,input_encoding='utf-8')
   renderedtext = template.render(**dictparms)
   with codecs.open(newfile,"w","utf-8") as f:
    f.write(renderedtext)
  else:
   print "unexpected inventory category:",category,filename
   exit(1)

