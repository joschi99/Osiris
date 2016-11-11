#!/bin/bash
###############################################################################
#
# send_licreq.sh - Schickt eine neue Lizenzanfrage
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.2
#
# Changelog
#	11.11.2016: Update license request email
#	20.08.2016: Update PGUM GmbH
#	16.01.2016: Erste Version für Osiris2.2
###############################################################################

FROM="i-vertix-lic@pgum.local"
TO="license@pgum.eu"

declare -r FILE_LIC="/tmp/.ivertix.lic.tmp"

function send_mail () {
	local SUBJECT
	local MESSAGE
	
	SUBJECT="$1"
	MESSAGE="$2"
	
	mail -s "$SUBJECT" -r "$FROM" "$TO" <<< "$MESSAGE"
}

function get_UUID () {
	UUID="$(/usr/sbin/dmidecode -s system-uuid)"
}

function menu () {
	echo "i-Vertix license request"
	echo "1) Activate Centreon"
	echo "2) Activate Rancid"
	echo "3) Activate NeDi"
	echo "4) Activate Syslog"
	echo "5) Activate GLPI"
	echo "9) Send license request"
	echo "0) Exit"
	read n
	case $n in
		1) insert_centreon_lic_data;;
		2) insert_rancid_lic;;
		3) insert_nedi_lic;;
		4) insert_syslog_lic;;
		5) insert_glpi_lic;;
		9) send_license;;
		0) exit;;
	esac

}

function insert_glpi_lic () {
	echo "Acticate GLPI (y/n):"
	read G1
	if [ "$G1" = "y" ]
	then
		GLPI="active"
	else
		GLPI="not active"
	fi
	menu
}

function insert_syslog_lic () {
	echo "Acticate Syslog (y/n):"
	read SY1
	if [ "$SY1" = "y" ]
	then
		SYSLOG="active"
	else
		SYSLOG="not active"
	fi
	menu
}

function insert_rancid_lic () {
	echo "Acticate Rancid (y/n):"
	read R1
	if [ "$R1" = "y" ]
	then
		RANCID="active"
	else
		RANCID="not active"
	fi
	menu
}

function insert_nedi_lic () {
	echo "Acticate NeDi (y/n):"
	read NE1
	if [ "$NE1" = "y" ]
	then
		NEDI="active"
	else
		NEDI="not active"
	fi
	menu
}

function insert_gen_lic_data () {
	echo "Enter the company name:"
	read COMPANY
	echo "Enter the system UUID:"
	read UUID
	echo "Enter the email:"
	read EMAIL
	CREATE_DATE=$(date +"%d-%m-%y")
	menu
}

function insert_centreon_lic_data () {
	if [ "$A1" != "OK" ]
	then
		echo "Enter Centreon-Typ (Premise/SaaS):"
		read CENTREON_TYPE
		if [ "$CENTREON_TYPE" = "Premise" -o "$CENTREON_TYPE" = "SaaS" ]
		then
			A1="OK"
		else
			A1="NOK"
			echo "Wrong Input"
			insert_centreon_lic_data
		fi
	fi
	if [ "$A2" != "OK" ]
	then
		echo "Enter number of Host Checks (1-9999, unlimited):"
		read CENTREON_NOHOST
		if [ "$CENTREON_NOHOST" = "unlimited" ]
		then
			A2="OK"
		else
			if [ "$CENTREON_NOHOST" -ge 1 -a "$CENTREON_NOHOST" -le 9999 ]
			then
				A2="OK"
			else
				A2="NOK"
				echo "Wrong Input"
				insert_centreon_lic_data
			fi
		fi
	fi
	if [ "$A3" != "OK" ]
	then
		echo "Enter number of Service Checks (1-999999, unlimited):"
		read CENTREON_NOSERVICE
		if [ "$CENTREON_NOSERVICE" = "unlimited" ]
		then
			A3="OK"
		else
			if [ "$CENTREON_NOSERVICE" -ge 1 -a "$CENTREON_NOSERVICE" -le 999999 ]
			then
				A3="OK"
			else
				A3="NOK"
				echo "Wrong Input"
				insert_centreon_lic_data
			fi
		fi
	fi
	if [ "$A4" != "OK" ]
	then
		echo "Enter number of Pollers (0-99, unlimited):"
		read CENTREON_NOPOLLER
		if [ "$CENTREON_NOPOLLER" = "unlimited" ]
		then
			A4="OK"
		else
			if [ "$CENTREON_NOPOLLER" -ge 0 -a "$CENTREON_NOPOLLER" -le 99 ]
			then
				A4="OK"
			else
				A4="NOK"
				echo "Wrong Input"
				insert_centreon_lic_data
			fi
		fi
	fi
	menu
}

function send_license () {
	echo "i-Vertix license request" > $FILE_LIC
	echo "Company: $COMPANY" >> $FILE_LIC
	echo "EMail: $EMAIL" >> $FILE_LIC
	get_UUID
	echo "UUID: $UUID" >> $FILE_LIC
	if [ "$A4" = "OK" ]
	then
		echo "Centreon-Typ: $CENTREON_TYPE" >> $FILE_LIC
		echo "Centreon-NoHost: $CENTREON_NOHOST" >> $FILE_LIC
		echo "Centreon-NoService: $CENTREON_NOSERVICE" >> $FILE_LIC
		echo "Centreon-NoPoller: $CENTREON_NOPOLLER" >> $FILE_LIC
	fi
	echo "Rancid: $RANCID" >> $FILE_LIC
	echo "NeDi: $NEDI" >> $FILE_LIC
	echo "Syslog: $SYSLOG" >> $FILE_LIC
	echo "GLPI: $GLPI" >> $FILE_LIC
	
	echo "---------------" >> $FILE_LIC
	echo "hostname: `hostname`" >> $FILE_LIC
	echo "ifconfig:" >> $FILE_LIC
	echo "`ifconfig`" >> $FILE_LIC
	
	send_mail "License request" "`cat $FILE_LIC`"
	rm -rf $FILE_LIC
	exit
}

echo "Please enter the company name:"
read COMPANY
echo "Please enter a valid email:"
read EMAIL
menu