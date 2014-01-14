#!/bin/bash
###############################################################################
#
# check_gpg.sh - Dieses Skript macht einen XML-Export der Syslogtabelle und
#                signiert das File.
#
# Copyright (c) 2009-2011 Osiris 2.0 NMS (Contact: info@bi-s.it)
#
# Development:
#  Jochen Platzgummer
#
# Version 2.0
#
# Changelog
#   12.09.2011: Script an Osiris 2.0 angepasst
#   20.02.2010: check Signatur aller Files sowie check ob die Signatur
#               vorhanden ist (ausser bei den Files des aktuellen Tages)
#
###############################################################################

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
