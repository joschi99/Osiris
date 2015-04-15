#!/bin/bash

SYSGUID="$(dmidecode |grep UUID)"
COMP_NAME_LIC="$(cat /etc/osiris.lic |grep 'Company:')"
COMP_NAME_LIC=${COMP_NAME_LIC:9}
SN_LIC="$(cat /etc/osiris.lic |grep 'Serial Number:')"
SN_LIC=${SN_LIC:15}

S1="$SYSGUID $COMP_NAME_LIC"
SN="$(echo -n "$S1" | md5sum)"

echo "$(cat /opt/bi-s/software/scripts/lic/issue)" > /etc/issue

rm -rf /opt/bi-s/software/scripts/lic/shutdown
touch /opt/bi-s/software/scripts/lic/shutdown
chmod +x /opt/bi-s/software/scripts/lic/shutdown

if [ "$SN" = "$SN_LIC" ]; then
  echo "license is valid"
  echo "The system has a valid license key" >> /etc/issue
else
  echo "license is not valid"
  echo "The system has a NON valid license key" >> /etc/issue

  echo "shutdown -P now" >> /opt/bi-s/software/scripts/lic/shutdown

  SUBJECT="Osiris not licensed"
  echo "Osiris ist nicht lizenziert, Shutdown in 4 Stunden" > email.txt
  echo "$SYSGUID" >> email.txt
  echo "HOSTNAME: `hostname`" >> email.txt
  echo "IFCONFIG:" >> email.txt
  echo "`ifconfig`" >> email.txt
  TEXT="$(cat email.txt)"
  MAIL="Subject: $SUBJECT\nFrom: osiris@bi-s.it\nTo: jochen.platzgummer@bi-s.it\n\n$TEXT"
  rm -rf email.txt
  echo -e $MAIL | sendmail -t
  exit $?
fi
