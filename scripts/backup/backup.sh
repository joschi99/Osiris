#!/bin/bash
###############################################################################
#
# backup.sh - Skript, das ein Backup aller wichtigen Komponenten des Servers
#             macht.
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 2.3
#
# Changelog
#   20.08.2016: Logstash config backup
#	20.12.2015: Remove backup from centreon_syslog DB
#   09.04.2014: Osiris 2.1 Anpassungen
#   23.01.2014: Backup DB LogAnalyser
#   09.09.2011: Anpassung an Osiris 2.0
#   22.09.2010: Backup um Jasper Server Repository erweitert
#   22.09.2010: Backup um die MySQL-DB von OTRS & JasperServer erweitert
#   20.04.2010: Zielverzeichnis f�r Backup syslog war falsch
#   09.04.2010: leere Verzeichnisse werden nun gel�scht
#   15.02.2010: Backup um CIFS Syslog (PGP) erweitert
#   05.02.2010: CVS-Repository Backup hinzugefuegt (Rancid)
#   31.01.2010: Datenbank syslog in das Backup aufgenommen
#   27.01.2010: Grundversion: 
#                - Backup der folgender Datenbanken:
#                    centreon, glpi, wikidb, nedi, ocsweb, phpmyadmin, syslog
#                - Backup der wichtigsten Files und Verzeichnisse
#
###############################################################################

BACKUP_PATH=/opt/bi-s/cifs/backup
DIR=$(date +%Y%m%d)
FILE=$(date +%Y%m%d_%H%M).sql.gz
BACKUP_RETTIME=5
DB_USER=backup
DB_PWD=mFRiQYIuwHhCIk6s753Q

# create backup directory if not exists
if [ ! -d $BACKUP_PATH/$DIR ]
then
  mkdir $BACKUP_PATH/$DIR
  echo "$(date +%Y.%m.%d-%H:%M:%S) $BACKUP_PATH/$DIR created"
fi

# delete backup file if exists
if [ -f $BACKUP_PATH/$DIR/$FILE ]
then
  rm -rf $BACKUP_PATH/$DIR/$FILE
  echo "$(date +%Y.%m.%d-%H:%M:%S) duplicated File canceled"
fi

# BEGIN DB BACKUP
echo "$(date +%Y.%m.%d-%H:%M:%S) Begin MySQL database backup"

echo "Start backup db CENTREON"
mysqldump -u $DB_USER -p$DB_PWD centreon| gzip > $BACKUP_PATH/$DIR/centreon_$FILE
echo "End backup db CENTREON"

echo "Start backup db NEDI"
mysqldump -u $DB_USER -p$DB_PWD nedi | gzip > $BACKUP_PATH/$DIR/nedi_$FILE
echo "End backup db NEDI"

echo "Start backup db MEDIAWIKI"
mysqldump -u $DB_USER -p$DB_PWD mediawiki | gzip > $BACKUP_PATH/$DIR/wikidb_$FILE
echo "End backup db MEDIAWIKI"

echo "Start backup db GLPI"
mysqldump -u $DB_USER -p$DB_PWD glpi | gzip > $BACKUP_PATH/$DIR/glpi_$FILE
echo "End backup db GLPI"

echo "Start backup db PHPMYADMIN"
mysqldump -u $DB_USER -p$DB_PWD phpmyadmin | gzip > $BACKUP_PATH/$DIR/phpmyadmin_$FILE
echo "End backup db OCSWEB"

echo "$(date +%Y.%m.%d-%H:%M:%S) End MySQL database backup"
# END BACKUP

#BEGIN FILE BACKUP
echo "$(date +%Y.%m.%d-%H:%M:%S) Begin file backup"
if [ -d $BACKUP_PATH/$DIR/files ]
then
  rm -rf $BACKUP_PATH/$DIR/files
fi  
mkdir $BACKUP_PATH/$DIR/files

tar czfvP $BACKUP_PATH/$DIR/files/std_plugins.tar.gz /usr/lib/nagios/plugins
tar czfvP $BACKUP_PATH/$DIR/files/smokeping.tar.gz /usr/local/smokeping/etc/
tar czfvP $BACKUP_PATH/$DIR/files/bis_scripts.tar.gz /opt/bi-s/software/scripts/
tar czfvP $BACKUP_PATH/$DIR/files/rancid_cvs.tar.gz /usr/local/rancid/var/
tar czfvP $BACKUP_PATH/$DIR/files/logstash.tar.gz /etc/logstash/
echo "$(date +%Y.%m.%d-%H:%M:%S) File backup completed"
#END FILE BACKUP

# BEGIN BACKUP RETENTION
echo "$(date +%Y.%m.%d-%H:%M:%S) Begin retention"
find $BACKUP_PATH -mtime +$BACKUP_RETTIME -type f | xargs rm -f
find $BACKUP_PATH -depth -type d -empty -exec rmdir {} \;
echo "$(date +%Y.%m.%d-%H:%M:%S) Retention completed"