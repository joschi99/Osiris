#!/bin/bash
###############################################################################
#
# geoip-update.sh - Download and update GeoLite2 Database
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.1
#
# Changelog
#       10.11.2016: ELK 5.x supports now GeoLite2 databases
#       30.09.2015: Initial release
###############################################################################

cd /opt/bi-s/software/ELK
wget http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz
gzip -d GeoLite2-City.mmdb.gz
mv -f GeoLite2-City.mmdb /etc/logstash

# restart Logstash
/sbin/initctl restart logstash