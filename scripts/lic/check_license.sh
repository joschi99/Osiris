#!/bin/bash

FILE_LIC="osiris.lic"
PATH_TMP="/tmp"
PATH_LICFILE="/etc"
EMAIL_SENDER="osiris@bi-s.it"
EMAIL_RECIPIENT="support@bi-s.it"

declare -r OK_DESC="OK"
declare -r WARN_DESC="WARNING"
declare -r ERR_DESC="ERROR"

function send_mail() {
	local SUBJECT
	local TEXT
	local TMP_MAIL_TEXT="$PATH_TMP/mail.txt"
	local MAIL

	SUBJECT=$1
	echo "$2\n" > $TMP_MAIL_TEXT
	echo "EMail: $LICMAIL\n" >> $TMP_MAIL_TEXT
	echo "$SYSGUID\n" >> $TMP_MAIL_TEXT
	echo "HOSTNAME: `hostname`\n" >> $TMP_MAIL_TEXT
	echo "IFCONFIG:\n" >> $TMP_MAIL_TEXT
	echo "`/sbin/ifconfig`" >> $TMP_MAIL_TEXT
	TEXT="$(cat $TMP_MAIL_TEXT)"

	#add License mail to recipient if exists
	#if [[ "$LICMAIL" =~ "^[A-Za-z0-9._%+-]+<b>@</b>[A-Za-z0-9.-]+<b>\.</b>[A-Za-z]{2,4}$" ]]; then
		#EMAIL_RECIPIENT = "$EMAIL_RECIPIENT,$LICMAIL
	#fi
	MAIL="Subject: $SUBJECT\nFrom: $EMAIL_SENDER\nTo: $EMAIL_RECIPIENT\n\n$TEXT"
	rm -rf $TMP_MAIL_TEXT
	echo -e $MAIL | /usr/sbin/sendmail -t
}

send_shutdown_command() {
	local SHUTDOWN_CMD="/opt/bi-s/software/scripts/lic/shutdown"
		
	#create a empty shutdown file
	rm -rf $SHUTDOWN_CMD
	touch $SHUTDOWN_CMD
	chmod +x $SHUTDOWN_CMD

	echo "$CMD_PARAM" >> $SHUTDOWN_CMD
}

function parse_licfile() {
	local FILE_LIC_TXT="osiris.lic.txt"

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
}

function update_licstatus () {
	local FILE_LIC_CSV="osiris.lic.csv"
	local LICSTATCSV

	LICSTATCSV="$(cat $PATH_LICFILE/$FILE_LIC_CSV |grep 'Status,')"
	if [ -z "$LICSTATCSV" ]; then
		echo "Status,$LICSTATUS,$1" >> $PATH_LICFILE/$FILE_LIC_CSV
	else
		#Update line 6 in CSV File
		sed -i "6s/.*/Status,${LICSTATUS},${1}/" $PATH_LICFILE/$FILE_LIC_CSV
	fi
}

function read_sysuuid() {
	SYSGUID="$(/usr/sbin/dmidecode |grep UUID)"
	SYSGUID=${SYSGUID:7}
}

function calc_sn() {
	local S1

	read_sysuuid
	S1="$SYSGUID $COMP_NAME_LIC $EXP_DATE"
	SN="$(echo -n "$S1" | md5sum)"
}

#check if license file exists
if [ ! -f $PATH_LICFILE/$FILE_LIC ]; then
	LICSTATUS = "there is NO Osiris license file"
	echo "$LICSTATUS" | logger
	echo "$LICSTATUS" >> /etc/issue
	update_licstatus $ERR_DESC

	#create shutdown command
	CMD_PARAM="/sbin/shutdown -P now"
	send_shutdown_command

	#send email
	send_mail "NO Osiris license file" "Es ist kein Osiris Lizenzfile vorhanden, shutdown geplant"
	echo "$ERR_DESC - $LICSTATUS"
fi

#read license file
parse_licfile

#recalculate SN
calc_sn

echo "$(cat /opt/bi-s/software/scripts/lic/issue)" > /etc/issue

#calculate date difference
if [ "$EXP_DATE" != "never" ]; then
	EXP_DATE2=$(date -d $EXP_DATE +%s)
	TODAY=$(date +%s)
	DIFF_DAYS=$(( ($EXP_DATE2-$TODAY)/(60*60*24) ))
else
	DIFF_DAYS=999
fi

#Check license status
if [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" >= 1 )); then
	if (( "$DIFF_DAYS" < 30 )); then
		LICSTATUS="your Osiris license will be expire in $DIFF_DAYS days"
		echo "$LICSTATUS" | logger
		echo "$LICSTATUS" >> /etc/issue
		update_licstatus $WARN_DESC

		#send email
		send_mail "$COMP_NAME_LIC: Osiris licensed will expire in $DIFF_DAYS days" "Osiris Lizenz verfällt in $DIFF_DAYS Tagen"
		
		echo "$WARN_DESC - $LICSTATUS"
	fi
	LICSTATUS="Osiris license is valid"
	echo "$LICSTATUS" | logger
	echo "$LICSTATUS" >> /etc/issue
	update_licstatus $OK_DESC

	#NO shutdown command
	CMD_PARAM=""
	send_shutdown_command

	echo "$OK_DESC - $LICSTATUS"
elif [ "$SN" != "$SN_LIC" ] || (( "$DIFF_DAYS" <= 0 )); then
	if  (( "$DIFF_DAYS" <= 0 )); then
		LICSTATUS="your Osiris license key is expired"
		echo "$LICSTATUS" | logger
		echo "$LICSTATUS" >> /etc/issue
		update_licstatus $ERR_DESC

		#create shutdown command
		CMD_PARAM="/sbin/shutdown -P now"
		send_shutdown_command

		#send email
		send_mail "$COMP_NAME_LIC: Osiris licensed is expired" "Osiris lizenziert ist verfallen, shutdown geplant"
	else
		LICSTATUS="your Osiris license key is not valid"
		echo "$LICSTATUS" | logger
		echo "$LICSTATUS" >> /etc/issue
		update_licstatus $ERR_DESC

		#create shutdown command
		CMD_PARAM="/sbin/shutdown -P now"
		send_shutdown_command

		#send email
		send_mail "$COMP_NAME_LIC: Osiris not licensed" "Osiris ist nicht lizenziert, shutdown geplant"
	fi
	echo "$ERR_DESC - $LICSTATUS"
fi