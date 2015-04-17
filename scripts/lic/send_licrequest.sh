#!/bin/bash

PATH_TMP="/tmp"
TMP_MAIL_TEXT="$PATH_TMP/mail.txt"
EMAIL_RECIPIENT="support@bi-s.it"

send_mail() {
	SUBJECT="$MAIL_SUBJECT"
	echo "$MAIL_TEXT\n" > $TMP_MAIL_TEXT
	echo "$SYSGUID\n" >> $TMP_MAIL_TEXT
	echo "HOSTNAME: `hostname`\n" >> $TMP_MAIL_TEXT
	echo "IFCONFIG:\n" >> $TMP_MAIL_TEXT
	echo "`ifconfig`" >> $TMP_MAIL_TEXT
	TEXT="$(cat $TMP_MAIL_TEXT)"
	MAIL="Subject: $SUBJECT\nFrom: $EMAIL\nTo: $EMAIL_RECIPIENT\n\n$TEXT"
	rm -rf $TMP_MAIL_TEXT
	echo -e $MAIL | sendmail -t
	exit $?
}

echo "Bitte den Company Namen eingeben:"
read COMP_NAME
echo "Bitte eine gültige E-Mail angeben:"
read EMAIL

#read system uuid
SYSGUID="$(dmidecode |grep UUID)"

MAIL_SUBJECT="NEW lizenz request"
MAIL_TEXT="Company Name: $COMP_NAME"
send_mail