# coding=utf-8
""" backup.py
 Mar 30, 2018
  
"""
from __future__ import print_function
import sys,re
import codecs
import os.path,time
from shutil import copyfile

def makedirs(webdirname):
 if not os.path.exists(webdirname):
  os.makedirs(webdirname)
 else:
  print('makedirs ERROR: directory already exists:',webdirname)
  exit(1)
 subdirs = ['images','js','sqlite','fonts',
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

if __name__=="__main__":
 filein = sys.argv[1]
 oldweb = sys.argv[2]
 newweb = sys.argv[3]
 # make newweb directory, and needed subdirectories
 makedirs(newweb)
 # read inventory. all paths assumed relative
 with codecs.open(filein,"r","utf-8") as f:
  filenames = [x.rstrip('\r\n') for x in f if not x.startswith(';')]
 # copy
 copyfiles(filenames,oldweb,newweb)
