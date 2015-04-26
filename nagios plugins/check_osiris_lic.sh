#!/bin/bash

FILE_LIC="osiris.lic"
PATH_TMP="/tmp"
PATH_LICFILE="/etc"

STATE_OK=0
STATE_WARNING=1
STATE_CRITICAL=2
STATE_UNKNOWN=3

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

function read_sysuuid() {
	SYSGUID="$(sudo /usr/sbin/dmidecode |grep UUID)"
	SYSGUID=${SYSGUID:7}
}

function calc_diff_date() {
	#calculate date difference
	if [ "$EXP_DATE" != "never" ]; then
		EXP_DATE2=$(date -d $EXP_DATE +%s)
		TODAY=$(date +%s)
		DIFF_DAYS=$(( ($EXP_DATE2-$TODAY)/(60*60*24) ))
	else
		DIFF_DAYS=999
	fi
}

function calc_sn() {
	local S1

	read_sysuuid
	S1="$SYSGUID $COMP_NAME_LIC $EXP_DATE"
	SN="$(echo -n "$S1" | md5sum)"
}

function license_status () {
	#check if license file exists
	if [ ! -f $PATH_LICFILE/$FILE_LIC ]; then
		LIC_STATUS="NO LIC FILE"
	else
		parse_licfile
		calc_sn
		calc_diff_date
		if [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" >= 30 )); then
			LIC_STATUS="OK"
		elif [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" < 30 )); then
				LIC_STATUS="EXPIRE"
		elif [ "$SN" = "$SN_LIC" ] && (( "$DIFF_DAYS" <= 0 )); then
			LIC_STATUS="EXPIRED"
		elif [ "$SN" != "$SN_LIC" ]; then
			LIC_STATUS="KEY NOT VALID"
		fi
	fi
}

function main () {
	license_status
	if [ "$LIC_STATUS" = "OK" ]; then
		echo "OK - License is valid"
		exit $STATE_OK
	elif [ "$LIC_STATUS" = "EXPIRE" ]; then
		echo "WARNING - License will be expire in $DIFF_DAYS days"
		exit $STATE_WARNING=1
	elif [ "$LIC_STATUS" = "EXPIRED" ]; then
		echo "ERROR - License is expired"
		exit $STATE_CRITICAL
	elif [ "$LIC_STATUS" = "KEY NOT VALID" ]; then
		echo "ERROR - Key is not valid"
		exit $STATE_CRITICAL
	elif [ "$LIC_STATUS" = "NO LIC FILE" ]; then
		echo "ERROR - No license file"
		exit $STATE_CRITICAL
	else
		exit $STATE_UNKNOWN
	fi
}

main
