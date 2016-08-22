#!/bin/bash
###############################################################################
#
# send_licreq.sh - sync the plugin directories to active pollers
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.1
#
# Changelog
#   20.08.2016: Update PGUM GmbH
#	16.01.2016: Erste Version für Osiris2.2
###############################################################################

CENT_USER="support"
CENT_PWD="password"

function find_poller() {
  /usr/share/centreon/bin/centreon -u $CENT_USER -p $CENT_PWD -o INSTANCE -a show > /tmp/poller.txt
  IFS=";"
  while read ID NAME LOCALHOST IP ACTIVATE STATUS OTHERS
  do
    #echo $ID $NAME $LOCALHOST $IP $ACTIVATE $STATUS
    if [ "$LOCALHOST" = "0" ] && [ "$ACTIVATE" = "1"  ]; then
      sync_poller
    fi
  done < /tmp/poller.txt
  rm -rf /tmp/poller.txt
}

function sync_poller() {
  if ping -c 1 $IP &> /dev/null
  then
    rsync -azrP --delete --progress /usr/lib/nagios/plugins/ root@$IP:/usr/lib/nagios/plugins/ >> /var/log/sync-poller.log 2>&1
  else
    echo "Poller $IP not reachable" >> /var/log/sync-poller.log 2>&1
  fi
}

find_poller