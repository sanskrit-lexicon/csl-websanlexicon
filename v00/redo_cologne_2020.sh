#!/bin/bash
# Regenerate the web display code for specified dictionaries.
# Usage - bash redo_cologne_2020.sh [dictcode].
# If dictcode is not specified, web display code of all the dictionaries are updated.
if [ -z "$1" ]
  then
	dicts=(BUR INM MWE PWG SKD STC VCP ACC AE AP90 AP BEN BHS BOP BOR CAE CCS GRA GST IEG KRM MCI MD MW72 MW PD PE PGN PUI PW SCH SHS SNP VEI WIL YAT)
  else
    dicts=(${1^^}) # Uppercase
fi

echo "Generating the web display code for the dictionaries."
for dict in ${dicts[*]}
do
	python generate.py "$dict" inventory.txt  makotemplates ../../"$dict"Scan/2020/web
done

