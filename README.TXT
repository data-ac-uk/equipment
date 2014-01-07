equipment.data.ac.uk

by Christopher Gutteridge, University of Southampton, 2013

Dependencies
 assumes php is installed (at /usr/bin/php) 
 assumes rapper is installed (and is in the path)
 For Debian/Ubuntu `apt-get install libapache2-mod-php5 php5-cli raptor2-utils`


Quickstart:

 mkdir lib
 mkdir var
 mkdir var/item
 mkdir var/tmp
 mkdir var/datasets
 mkdir htdocs/data
 mkdir htdocs/data/org
 mkdir htdocs/tmp
 chmod o+w htdocs/tmp
 cd lib
 git clone https://github.com/cgutteridge/Graphite.git
 git clone https://github.com/semsol/arc2.git
 git clone https://github.com/cgutteridge/ntcat.git
 git clone https://github.com/data-ac-uk/OPDLib.git
 git clone https://github.com/bluerhinos/phpLocation.git
 wget https://github.com/PHPOffice/PHPExcel/archive/PHPExcel_1.7.8.tar.gz 
 tar xzvf PHPExcel_1.7.8 
 ln -s PHPExcel-PHPExcel_1.7.8/ PHPExcel


Config:
 To get started symlink the test-list.tsv (Todo: move to autodiscovery)
	`ln -s etc/test-list.tsv.example etc/test-list.tsv`

Make htdocs/ available via the web (assumes it's the root of a vhost)

TODO List:

 Use hub.data.ac.uk (or it's code) for autodiscovery
 add location info
 Add something clever with licenses
 CERIF?
 per-site timeout

ADDITIONAL CREDITS:

Icons licensed as CC-by 2.5 from http://www.famfamfam.com/lab/icons/silk/
