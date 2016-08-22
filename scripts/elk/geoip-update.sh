#!/bin/bash
###############################################################################
#
# geoip-update.sh - Download and update GEOIP Database
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.0
#
# Changelog
#   30.09.2015: Initial release
###############################################################################

cd /opt/bi-s/software/ELK/geolite
wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
gzip -d GeoLiteCity.dat.gz
mv -f GeoLiteCity.dat /etc/logstash