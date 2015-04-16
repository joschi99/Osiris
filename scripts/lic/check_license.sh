#!/bin/bash

#decode license file
openssl enc -d -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in /etc/osiris.lic -out /tmp/osiris.lic.txt

#read system uuid
SYSGUID="$(dmidecode |grep UUID)"

#read license file
COMP_NAME_LIC="$(cat /tmp/osiris.lic.txt |grep 'Company:')"
COMP_NAME_LIC=${COMP_NAME_LIC:9}
SN_LIC="$(cat /tmp/osiris.lic.txt |grep 'Serial number:')"
SN_LIC=${SN_LIC:15}
EXP_DATE="$(cat /tmp/osiris.lic.txt |grep 'Expire date:')"
EXP_DATE=${EXP_DATE:13}

#delete txt license file
rm -rf /tmp/osiris.lic.txt

S1="$SYSGUID $COMP_NAME_LIC $EXP_DATE"
SN="$(echo -n "$S1" | md5sum)"

echo "$(cat /opt/bi-s/software/scripts/lic/issue)" > /etc/issue

rm -rf /opt/bi-s/software/scripts/lic/shutdown
touch /opt/bi-s/software/scripts/lic/shutdown
chmod +x /opt/bi-s/software/scripts/lic/shutdown

#Date Diff rechnen
if [ "$EXP_DATE" != "never" ]; then
  EXP_DATE2=$(date -d $EXP_DATE +%s)
  TODAY=$(date +%s)
  DIFF_DAYS=$(( ($EXP_DATE2-$TODAY)/(60*60*24) ))
else
  DIFF_DAYS=999
fi

if [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" >= 1 )); then
  echo "license is valid"
  echo "The system has a valid license key" >> /etc/issue
    if (( "$DIFF_DAYS" >= 1 )) && (( "$DIFF_DAYS" < 30 )); then
      echo "Your license will be expire in $DIFF_DAYS days"
      echo "Your license will be expire in $DIFF_DAYS days" >> /etc/issue
      SUBJECT="$COMP_NAME_LIC: Osiris licensed will expire in $DIFF_DAYS days"
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
elif [ "$SN" != "$SN_LIC" ] || (( "$DIFF_DAYS" <= 0 )); then
  if  (( "$DIFF_DAYS" <= 0 )); then
    echo "your license key is expired"
    echo "The license key is EXPIRED" >> /etc/issue

    echo "shutdown -P now" >> /opt/bi-s/software/scripts/lic/shutdown

    SUBJECT="$COMP_NAME_LIC: Osiris licensed is expired"
    echo "Osiris lizenziert ist verfallen, Shutdown in 4 Stunden" > email.txt
  else
    echo "license is not valid"
    echo "The system has a NON valid license key" >> /etc/issue

    echo "shutdown -P now" >> /opt/bi-s/software/scripts/lic/shutdown

    SUBJECT="$COMP_NAME_LIC: Osiris not licensed"
    echo "Osiris ist nicht lizenziert, Shutdown in 4 Stunden" > email.txt
  fi
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