#!/bin/bash
# generate (update or initialize) web code for a given dictionary
#  usage: sh generate_web.sh <dict> <parent-dir>
#  The files are put into <parent-dir>.
#  Th
if [ -z "$1" ] || [ -z "$2" ]
  then
   echo "usage:  sh generate_web.sh <dict> <parent-dir>"
   echo "Example: sh generate_web.sh acc tempparent/acc"
   echo "Example: sh generate_web.sh acc ../../ACCScan/2020"
   exit 1
  else
    dict=$1
    outdir=$2
fi


 echo "generate web code for dictionary $dict to $outdir"
 python generate.py "$dict" inventory.txt  makotemplates distinctfiles/$dict $outdir

