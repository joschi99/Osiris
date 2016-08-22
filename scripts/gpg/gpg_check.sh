#!/bin/bash
###############################################################################
#
# check_gpg.sh - check if the gpg key is valid
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 2.2
#
# Changelog
#   20.08.2016: Update PGUM GmbH
#   03.09.2015: Abbruch, falls Programm bereits läuft (Issue #7)
#   12.09.2011: Script an Osiris 2.0 angepasst
#   20.02.2010: check Signatur aller Files sowie check ob die Signatur
#               vorhanden ist (ausser bei den Files des aktuellen Tages)
#
###############################################################################

#Prüfen, ob bereits eine Instanz läuft
if [ "$(pgrep -x $(basename $0))" != "$$" ]; then
 echo "Fehler: das Programm $(basename $0) läuft bereits -> Abbruch"
 exit 1
fi

#RSYSLOG_PATH muss mit Path in /etc/rsyslog.conf zusammenstimmen
RSYSLOG_PATH=/opt/bi-s/cifs/rsyslog
FIND="$(find $RSYSLOG_PATH -mindepth 3 -type f -name '*.log')"
DATUM=$(date +%Y%m%d)

#BEGIN CHECK DAILY GPG
echo "$(date +%Y.%m.%d-%H:%M:%S) Begin verify"
for FILE in $FIND; do
  if [ ! -f $FILE.sig ]
  then
    POS="$(expr index $FILE $DATUM)"
    if [ $POS = 0 ]
    then
      echo "BAD: no signatur file for $FILE"
    fi
  else
    echo "$(date +%Y.%m.%d-%H:%M:%S) check sign for file $FILE"
    gpg --verify $FILE.sig $FILE 2>&1
    echo "$(date +%Y.%m.%d-%H:%M:%S) finish check sign for file $FILE"
  fi
done
echo "$(date +%Y.%m.%d-%H:%M:%S) End verify"