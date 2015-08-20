CENT_USER="admin"
CENT_PWD="password"

function find_poller() {
  /usr/share/centreon/www/modules/centreon-clapi/core/centreon -u $CENT_USER -p $CENT_PWD -o INSTANCE -a show > /tmp/poller.txt
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
