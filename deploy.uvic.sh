#!/bin/sh
#
# Deploy Thinklog to UVic's server
# (c) Carlos E. Torchia under GPL2
#

ssh uvic -C 'rm -rf www/thinklog ; mkdir www/thinklog'
scp -r * uvic:www/thinklog/
ssh uvic -C 'mv www/thinklog/def.php.uvic www/thinklog/def.php'
