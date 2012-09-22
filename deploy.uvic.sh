#!/bin/sh
#
# Deploy Thinklog to UVic's server
# (c) Carlos E. Torchia under GPL2
#

# Deploy PHP app on UVic web server
ssh uvic -C 'rm -rf www/thinklog ; mkdir www/thinklog'
scp -r * uvic:www/thinklog/
ssh uvic -C 'mv www/thinklog/def.php.uvic www/thinklog/def.php'

# Create archive and put in on CSc web server
cd ..
zip -r thinklog-1.0.zip thinklog/*
zip -d thinklog-1.0.zip thinklog/def.php.uvic
scp thinklog-1.0.zip webhome:public_html/
rm thinklog-1.0.zip
