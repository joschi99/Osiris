#!/bin/bash

FILE_LIC_TXT="osiris.lic.txt"
FILE_LIC="osiris.lic"
PATH_TMP="/tmp"
PATH_LICFILE="/etc"
SHUTDOWN_CMD="/opt/bi-s/software/scripts/lic/shutdown"
TMP_MAIL_TEXT="$PATH_TMP/mail.txt"
EMAIL_SENDER="osiris@bi-s.it"
EMAIL_RECIPIENT="support@bi-s.it"

send_mail() {
	SUBJECT="$MAIL_SUBJECT"
	echo "$MAIL_TEXT\n" > $TMP_MAIL_TEXT
	echo "EMail: $LICMAIL\n" >> $TMP_MAIL_TEXT
	echo "$SYSGUID\n" >> $TMP_MAIL_TEXT
	echo "HOSTNAME: `hostname`\n" >> $TMP_MAIL_TEXT
	echo "IFCONFIG:\n" >> $TMP_MAIL_TEXT
	echo "`ifconfig`" >> $TMP_MAIL_TEXT
	TEXT="$(cat $TMP_MAIL_TEXT)"
	MAIL="Subject: $SUBJECT\nFrom: $EMAIL_SENDER\nTo: $EMAIL_RECIPIENT\n\n$TEXT"
	rm -rf $TMP_MAIL_TEXT
	echo -e $MAIL | sendmail -t
	exit $?
}

send_shutdown_command() {
	#create a empty shutdown file
	rm -rf $SHUTDOWN_CMD
	touch $SHUTDOWN_CMD
	chmod +x $SHUTDOWN_CMD

	echo "$CMD_PARAM" >> $SHUTDOWN_CMD
}

#read system uuid
SYSGUID="$(dmidecode |grep UUID)"
SYSGUID=${SYSGUID:7}

#check if license file exists
if [ ! -f $PATH_LICFILE/$FILE_LIC ]; then
	echo "there is NO Osiris license file" | logger -s
	echo "there is NO Osiris license file" >> /etc/issue

	#create shutdown command
	CMD_PARAM="shutdown -P now"
	send_shutdown_command

	#send email
	MAIL_SUBJECT="NO Osiris license file"
	MAIL_TEXT="Es ist kein Osiris Lizenzfile vorhanden, shutdown geplant"
	send_mail
fi

#decode license file
openssl enc -d -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in $PATH_LICFILE/$FILE_LIC -out $PATH_TMP/$FILE_LIC_TXT

#read license file
COMP_NAME_LIC="$(cat $PATH_TMP/$FILE_LIC_TXT |grep 'Company:')"
COMP_NAME_LIC=${COMP_NAME_LIC:9}
SN_LIC="$(cat $PATH_TMP/$FILE_LIC_TXT |grep 'Serial number:')"
SN_LIC=${SN_LIC:15}
EXP_DATE="$(cat $PATH_TMP/$FILE_LIC_TXT |grep 'Expire date:')"
EXP_DATE=${EXP_DATE:13}
LICMAIL="$(cat $PATH_TMP/$FILE_LIC_TXT |grep 'EMail:')"
LICMAIL=${LICMAIL:7}

#delete txt license file
rm -rf $PATH_TMP/$FILE_LIC_TXT

#recalculate SN
S1="$SYSGUID $COMP_NAME_LIC $EXP_DATE"
SN="$(echo -n "$S1" | md5sum)"

echo "$(cat /opt/bi-s/software/scripts/lic/issue)" > /etc/issue

#Date Diff rechnen
if [ "$EXP_DATE" != "never" ]; then
	EXP_DATE2=$(date -d $EXP_DATE +%s)
	TODAY=$(date +%s)
	DIFF_DAYS=$(( ($EXP_DATE2-$TODAY)/(60*60*24) ))
else
	DIFF_DAYS=999
fi

if [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" >= 1 )); then
	echo "Osiris license is valid" | logger -s
	echo "The system has a valid license key" >> /etc/issue

	#NO shutdown command
	CMD_PARAM=""
	send_shutdown_command
		
	if (( "$DIFF_DAYS" < 30 )); then
		echo "your Osiris license will be expire in $DIFF_DAYS days" | logger -s
		echo "your Osiris license will be expire in $DIFF_DAYS days" >> /etc/issue

		#send email
		MAIL_SUBJECT="$COMP_NAME_LIC: Osiris licensed will expire in $DIFF_DAYS days"
		MAIL_TEXT="Osiris Lizenz verfällt in $DIFF_DAYS Tagen"
		send_mail
    fi
elif [ "$SN" != "$SN_LIC" ] || (( "$DIFF_DAYS" <= 0 )); then
	if  (( "$DIFF_DAYS" <= 0 )); then
		echo "your Osiris license key is expired" | logger -s
		echo "your Osiris license key is EXPIRED" >> /etc/issue

		#create shutdown command
		CMD_PARAM="shutdown -P now"
		send_shutdown_command

		#send email
		MAIL_SUBJECT="$COMP_NAME_LIC: Osiris licensed is expired"
		MAIL_TEXT="Osiris lizenziert ist verfallen, shutdown geplant"
		send_mail
	else
		echo "your Osiris license key is not valid" | logger -s
		echo "your Osiris license key is NOT valid" >> /etc/issue

		#create shutdown command
		CMD_PARAM="shutdown -P now"
		send_shutdown_command

		#send email
		MAIL_SUBJECT="$COMP_NAME_LIC: Osiris not licensed"
		MAIL_TEXT="Osiris ist nicht lizenziert, shutdown geplant"
		send_mail
	fi
fi