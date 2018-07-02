readme.txt for graweb download.  
July 07-02, 2014
Jim Funderburk

The transcoder software component is based upon software designed and
developed by Ralph Bunker,  Malcolm Hyman, and Peter Scharf
(www.sanskritlibrary.org).

The purpose of the files in this directory is to make it possible for
a user to install on a local computer a web-browser display of gra data.

unzip the graweb.zip archive. This creates a folder called 'web'.
Rename 'web' to something such as 'gra'.

It is assumed that you have an Apache server with php installed on your
local computer.  On computers with the Microsoft Windows operating system,
you may install Server2go. Apple Macintosh OSX computers come with Apache
and php. Apache servers with php (a 'lamp' stack) are readily available for
Unix operating systems.  Mysql is not required.

Move the 'gra' folder to the 'htdocs' subfolder of your Apache installation.
Be sure the Apache server is started and that you know the 'home' page
url for your server (For example, with Server2go the home page url is
http://127.0.0.1:4001/ or http://localhost:4001/).
Then, use the url <home>/gra/ to get the home page of the gra displays;
for instance, http://127.0.0.1:4001/gra/.

For server2go, you may need to slightly adjust the pms_config.ini file to
the one contained in the server2go subdirectory of this graweb download.


For Mac installation, move the gra folder to the Sites folder, and make 
sure web sharing is turned on.
You may need to changed the permissions on the gra 
folder and its subfolders and directories; e.g., in a terminal, change to
the Sites folder and 'chmod -RE 0777 gra'. (you may need to use sudo,
e.g., 'sudo chmod -RE 0777 gra').

