#!/bin/bash
###############################################################################
#
# sign_gpg.sh - Dieses Skript signiert alle Dateien, die sich in $AKT_PATH
#               befinden.
#               Files vor $RETTIME werden gelöscht
#
# Copyright (c) 2009-2011 Osiris 2.0 NMS (Contact: info@bi-s.it)
#
# Development:
#  Jochen Platzgummer
#
# Version 2.0
#
# Changelog
#   12.09.2011: Anpassung an Osiris 2.0
#   04.06.2010: SMB-Berechtigungen angepasst
#   28.04.2010: setze Berechtigung auf Verzeichnis für SMB-Zugriff
#   09.04.2010: die leeren Verzeichnisse werden nun gelöscht.
#   20.02.2010: automatische signieren aller Dateien *.log im Verzeichnis
#               mit Check der Signierung.
#
###############################################################################

#RSYSLOG_PATH muss mit Path in /etc/rsyslog.conf zusammenstimmen
RSYSLOG_PATH=/opt/bi-s/cifs/rsyslog
DATUM=$(date -d '1 day ago' +%Y%m%d)
PERIODE=$(date -d '1 day ago' +%Y%m)
AKT_PATH=$RSYSLOG_PATH/$PERIODE/$DATUM
RETTIME=180

echo "$AKT_PATH"

echo "$(date +%Y.%m.%d-%H:%M:%S) Signing files in path $AKT_PATH"
for FILE in $AKT_PATH/*.log; do
  echo "$(date +%Y.%m.%d-%H:%M:%S) Signing file $FILE"
  gpg --batch --passphrase-fd 0 --output $FILE.sig --detach-sig $FILE < /opt/bi-s/software/scripts/gpg/.gpg_passwd.txt 2>&1
  gpg --verify $FILE.sig $FILE 2>&1
  echo "$(date +%Y.%m.%d-%H:%M:%S) File $FILE signed"
done

# BEGIN RETENTION
echo "$(date +%Y.%m.%d-%H:%M:%S) Begin retention"
find $RSYSLOG_PATH -mtime +$RETTIME -type f | xargs rm -f
find $RSYSLOG_PATH -depth -type d -empty -exec rmdir {} \;
echo "$(date +%Y.%m.%d-%H:%M:%S) Retention completed"
