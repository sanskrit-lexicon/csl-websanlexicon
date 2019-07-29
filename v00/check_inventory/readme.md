## check_inventory


The original purpose of the csl-websanlexicon repository was to facilitate 
changes to the displays of the various dictionaries. The repository accomplishes
this by use of templates for *most* of the files which occur in the *web* 
directory of each dictionary.

In recent work, the csl-websanlexicon is viewed as one of several repositories which, acting together, allow for the recreation of all the files required to maintain both the data and the displays.

In particular, we need to account not just for *most* of the files which occur in the *web* directory, but for *all* such files.

In preparation for this increase in functionality,  we need to know exactly which files in the web directory for each dictionary are *and are not* accounted for by csl-websanlexicon repository.

## webinventory

```
  python webinventory.py webinventory.txt
```

The webinventory program compares two collections of files:
* The current [inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/master/v00/inventory.txt) of template files managed by csl-websanlexicon (via generate.py -- see [redo_cologne_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/master/v00/redo_cologne_all.sh) and two similar redo bash scripts.)
* For each dictionary managed by csl-websanlexicon, the *actual* inventory of files in the *web* directory for that dictionary.

The comparison appears in [webinventory.txt]([inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/master/v00/check_inventory/webinventory.txt)

See the issues of this repository for further discussion of *webinventory.txt*.

