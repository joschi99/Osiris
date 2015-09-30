#!/bin/bash

cd /opt/bi-s/software/ELK/geolite
wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
gzip -d GeoLiteCity.dat.gz
mv -f GeoLiteCity.dat /etc/logstash
