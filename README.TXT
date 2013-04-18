equipment.data.ac.uk

by Christopher Gutteridge, University of Southampton, 2013

Dependencies
 assumes php is installed (at /usr/bin/php)
 assumes rapper is installed (and is in the path)

Quickstart:

 mkdir lib
 mkdir var
 mkdir var/item
 mkdir htdocs/data
 mkdir htdocs/data/org
 mkdir htdocs/tmp
 chmod o+w htdocs/tmp
 cd lib
 git clone https://github.com/cgutteridge/Graphite.git
 git clone https://github.com/semsol/arc2.git
 git clone git@github.com:cgutteridge/ntcat.git

Make htdocs/ available via the web (assumes it's the root of a vhost)

TODO List:

 Use hub.data.ac.uk (or it's code) for autodiscovery
 add location info
 Add something clever with licenses
 CERIF?
 per-site timeout
 accessible version of search

ADDITIONAL CREDITS:

Icons licensed as CC-by 2.5 from http://www.famfamfam.com/lab/icons/silk/
