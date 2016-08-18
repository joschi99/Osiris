#!/bin/bash
###############################################################################
#
# sflow2elk.sh - Konvertiert nfdump für ELK
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.0
#
# Changelog
#   17.08.2016: Konvertierungsprozess mit Archivierung, Logfile und Retention
#
###############################################################################

source /opt/bi-s/software/scripts/sflow/sflow.cfg
PRG_NFDUMP=/usr/local/bin/nfdump

echo "$(date +%Y.%m.%d-%H:%M:%S) start convert process"

for FILE in $SFLOW_PATH/*
do
  # check filename is not nfcapd.current.* and file is a file (not a directory)
  if [[ "$FILE" != *nfcapd.current.* ]] && [ -f $FILE ]
  then
    $PRG_NFDUMP -r $FILE -q -o csv >> $LOGSTASH_FILE
    echo $FILE converted
    /bin/mv $FILE $SFLOW_ARCHIVE_PATH
	echo $FILE moved to archive
  fi
done

echo "$(date +%Y.%m.%d-%H:%M:%S) end convert process"

# BEGIN RETENTION
echo "$(date +%Y.%m.%d-%H:%M:%S) begin retention"
find $SFLOW_ARCHIVE_PATH -mtime +$ARCHIVE_RETTIME -type f | xargs rm -f
echo "$(date +%Y.%m.%d-%H:%M:%S) end retention"