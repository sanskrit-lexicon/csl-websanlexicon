# coding=utf-8
""" init_query.py for PW.  
June 28, 2014
Python program written since I don't know how to handle umlauts in php.
Creates query_dump.txt from xml file 

"""
import re,sys
import codecs
sanwords = [] # global
def query_construct(filein,fileout):
 f = codecs.open(filein,encoding='utf-8',mode='r')
 fout = codecs.open(fileout,'w','utf-8')
 n=0
 prevkey=''
 lnum1=0
 nfound=0
 nfound1=0
 prevkey=""
 key=''
 keydata=""

 for line in f:
  line = line.rstrip('\r\n')
  m = re.search(r'^<H.*?<key1>(.*?)</key1>.*<body>(.*?)</body>.*<L>(.*?)</L>',line)
  if m:
   n = n + 1
   key = m.group(1)
   body = m.group(2)
   L = m.group(3)
   data1 = query_line(body)
   data2 = query_sanskrit(body)
   if (prevkey == ""):
     prevkey = key
     keydata = data1
     keysanskrit = data2
   elif (prevkey == key):
     keydata += " :: %s" % data1
     keysanskrit += " :: %s" %data2
   else:
     fout.write("%s :: %s\t%s\n" %(prevkey,keysanskrit,keydata))
     nfound1 = nfound1 + 1
     prevkey = key
     keydata = data1
     keysanskrit = data2
  if False and (n >= 1000):
   print "debug stopping after n = ",n
   break

 # print last one
 fout.write("%s :: %s\t%s\n" %(prevkey,keysanskrit,keydata))
 f.close()
 fout.close()

 print n,"record read from", filein
 print nfound1,"records created in",fileout

def query_line(x):
 umlauts = [u"Ä",u"Ö",u"Ü",u"ß",u"ä",u"é",u"ê",u"ë",u"ö",u"ü"]
 nonumlauts = ["A","O","U","s","a","e","e","e","o","u"]
 for i in xrange(len(umlauts)):
  x = re.sub(umlauts[i],nonumlauts[i],x)

 # (a1) remove remaining extended ascii
 x = re.sub("&#x....;","",x) # 
 # (c) Remove markup
 x = re.sub(r'<s>.*?</s>','',x) # remove embedded SLP sanskrit
 x = re.sub(r'<.*?>',' ',x)
 x = re.sub(r'{#.*?#}','',x) # A few sanskrit letters coded as HK
 
 # (d) Remove punctuation
 x = re.sub(u"ƒPage(.*?)ƒ",'',x)
 x = re.sub(r'[~_;.,$ ?()\[\]]+',' ',x)
 x = re.sub(u'[†¨®]','',x)
 x = re.sub(r'[*\|=]','',x)
 x = re.sub(r'--',' ',x)
 # (e) downcase
 x = x.lower()
 
 # (f) replace AS codes (remove the number)
 x = re.sub("[0-9]","",x)
 x = re.sub(r"'s "," ",x)
 x = re.sub(r"  +"," ",x)
 return x

def query_sanskrit(x):
 global sanwords
 sanwords = []
 # Get all the <s>x</s> words
 # The subroutine modifies sanwords
 re.sub('<s>(.*?)</s>',query_sanskrit_helper1,x)
 ans = ' '.join(sanwords)
 return ans

def query_sanskrit_helper1(m):
 global sanwords
 s = m.group(1)
 # remove xml markup
 s = re.sub('<([^> ]*).*?>.*?</\1>',' ',s)
 s = re.sub('<.*?>',' ',s)
 # remove extended ascii, which is coded as html entity: &...
 s = re.sub('&.*?;',' ',s)
 # remove slp accent chars, if present
 s = re.sub(r'[\\/^]','',s)
 words = re.split("[^a-zA-Z|']",s)
 for w in words:
  sanwords.append(w)
 return "" # return value not important.


#-----------------------------------------------------
if __name__=="__main__":
 filein = sys.argv[1]
 fileout = sys.argv[2]
 query_construct(filein,fileout)
