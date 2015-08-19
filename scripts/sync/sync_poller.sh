#!/bin/bash

if ping -c 1 $1 &> /dev/null
then
  rsync -azrP --delete --progress /usr/lib/nagios/plugins/ root@$1:/usr/lib/nagios/plugins/ >> /var/log/sync-poller.log 2>&1
else
 echo "Poller $1 not reachable" >> /var/log/sync-poller.log 2>&1
fi