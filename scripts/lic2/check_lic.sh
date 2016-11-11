#!/bin/bash
###############################################################################
#
# check_lic.sh - Prüft die i-Vertix Lizenz
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.4
#
# Changelog
#	11.11.2016: ELK 5.0 update
#	20.08.2016: Update PGUM GmbH
#	12.05.2016: IP Adresse wird angezeigt
#	17.01.2016: check_lic.cfg
#	16.01.2016: Erste Version für Osiris2.2
###############################################################################

source /opt/bi-s/software/scripts/lic2/check_lic.cfg

declare -r version="1.4"

declare -r FILE_LIC_TMP="ivertix.lic.tmp"
declare -r FILE_LIC="ivertix.lic"
declare -r FILE_LIC_CSV="ivertix.lic.csv"
declare -r FILE_LIC_STATUS="ivertix.licstatus.csv"

declare -r PATH_LICFILE="/etc"
declare -r PATH_TMP="/tmp"

declare -r LIC_STATUS_OK=0
declare -r LIC_STATUS_ERR=1
declare -r LIC_STATUS_ERR_LIC=2
declare -r LIC_STATUS_NO_LIC=3
declare -r MAIL_LIC_SENDER="i-vertix-lic@pgum.local"
declare -r MAIL_LIC_REC="license@pgum.eu"

#CentEngine Status
declare -r STATUS_ERR=2
declare -r ERR_DESC="ERROR"
declare -r STATUS_WARN=1
declare -r WARN_DESC="WARNING"
declare -r STATUS_OK=0
declare -r OK_DESC="OK"

declare EXIT_STATUS="$STATUS_OK"

function start () {
	check_licfile_exist
	check_licenses
	actions
	write_statusfile
	exit "$EXIT_STATUS"
}

function set_exit_status () {

	if [ "$1" -gt "$EXIT_STATUS" ]
	then
		EXIT_STATUS="$1"
	fi
}

function actions () {
	action_centreon
	action_syslog
}

function action_syslog () {

	#Syslog License Key
	case $LIC_STATUS_SYSLOG in
		$LIC_STATUS_OK)
			startstop_ELK start
			set_exit_status "$STATUS_OK"
			;;
		$LIC_STATUS_ERR)
			startstop_ELK stop
			set_exit_status "$STATUS_ERR"
			;;
		$LIC_STATUS_ERR_LIC)
			startstop_ELK stop
			set_exit_status "$STATUS_ERR"
			;;
		$LIC_STATUS_NO_LIC)
			startstop_ELK stop
			set_exit_status "$STATUS_ERR"
			;;
	esac
}

function startstop_ELK () {
	if [ "$1" = "start" ]
	then
		#enable ELK
		if [ -f "/etc/init/logstash.conf.disable" ]
		then
			mv /etc/init/logstash.conf.disable /etc/init/logstash.conf
		fi
		/sbin/chkconfig elasticsearch on
		/sbin/chkconfig kibana on
	else
		#disable & stop ELK
		/sbin/initctl stop logstash
		/etc/init.d/elasticsearch stop
		/etc/init.d/kibana stop

		if [ -f "/etc/init/logstash.conf" ]
		then
			mv /etc/init/logstash.conf /etc/init/logstash.conf.disable
		fi
		/sbin/chkconfig elasticsearch off
		/sbin/chkconfig kibana off
		
		echo "Syslog license error" | logger
		echo "ELK disabled and stopped due a license error" | logger
		if [ ! $LIC_STATUS_SYSLOG = $LIC_STATUS_NO_LIC ]
		then
			send_mail "Syslog license Error" "ELK disabled and stopped due a license error"
		fi
	fi
}

function send_mail () {
	local SUBJECT
	local MESSAGE
	local FROM
	local TO
	
	SUBJECT="$1"
	MESSAGE="$2"
	
	if [ -z "$EMAIL" ]
	then
		FROM="$MAIL_LIC_SENDER"
		TO="$MAIL_LIC_REC"
	else
		FROM="$EMAIL"
		TO="$MAIL_LIC_REC,$EMAIL"
	fi

	if [ -e $PATH_LICFILE/$FILE_LIC_CSV ]
	then
		mail -s "$SUBJECT" -r "$FROM" -a "$PATH_LICFILE/$FILE_LIC_CSV" "$TO" <<< "$MESSAGE"
	else
		mail -s "$SUBJECT" -r "$FROM" "$TO" <<< "$MESSAGE"
	fi
}

function action_centreon () {

	#Centreon License Key
	case $LIC_STATUS in
		$LIC_STATUS_OK)
			#License OK
			startstop_centengine start
			set_exit_status "$STATUS_OK"
			;;
		$LIC_STATUS_ERR)
			;;
		$LIC_STATUS_ERR_LIC)
			#Centreon License Key not valid
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
		$LIC_STATUS_NO_LIC)
			#No Centreon License Key found
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
	esac

	#Centreon Host License
	case $LIC_STATUS_HOST in
		$LIC_STATUS_OK)
			#License Host OK
			startstop_centengine start
			set_exit_status "$STATUS_OK"
			;;
		$LIC_STATUS_ERR)
			#Host License problem
			send_mail "$LIC_STATUS_HOST_TEXT" "active hosts: $CENTREON_ACTIVE_HOSTS
licensed hosts: $CENTREON_NOHOST"
			set_exit_status "$STATUS_WARN"
			;;
		$LIC_STATUS_ERR_LIC)
			#Centreon Host License Key not valid
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
		$LIC_STATUS_NO_LIC)
			#No Centreon Host License Key found
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
	esac

	#Centreon Service License
	case $LIC_STATUS_SERVICE in
		$LIC_STATUS_OK)
			#Service License OK
			startstop_centengine start
			set_exit_status "$STATUS_OK"
			;;
		$LIC_STATUS_ERR)
			#Service License problem
			send_mail "$LIC_STATUS_SERVICE_TEXT" "active services: $CENTREON_ACTIVE_SERVICES
licensed services: $CENTREON_NOSERVICE"
			set_exit_status "$STATUS_WARN"
			;;
		$LIC_STATUS_ERR_LIC)
			#Centreon Service License Key not valid
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
		$LIC_STATUS_NO_LIC)
			#No Centreon Service License Key found
			startstop_centengine stop
			set_exit_status "$STATUS_ERR"
			;;
	esac

	#Centreon Poller License
	case $LIC_STATUS_POLLER in
		$LIC_STATUS_OK)
			#Centreon Poller License OK
			#startstop_centengine start
			set_exit_status "$STATUS_OK"
			;;
		$LIC_STATUS_ERR)
			#Poller License problem
			send_mail "$LIC_STATUS_POLLER_TEXT" "active poller: $CENTREON_ACTIVE_POLLERS
licensed poller: $CENTREON_NOPOLLER"
			set_exit_status "$STATUS_WARN"
			;;
		$LIC_STATUS_ERR_LIC)
			#Centreon Poller License Key not valid
			send_mail "$LIC_STATUS_POLLER_TEXT" "active poller: $CENTREON_ACTIVE_POLLERS"
			set_exit_status "$STATUS_WARN"
			;;
		$LIC_STATUS_NO_LIC)
			#No Centreon Poller License Key found
			#send_mail "$LIC_STATUS_POLLER_TEXT" "Active Poller: $CENTREON_ACTIVE_POLLERS"
			;;
	esac	
}

function startstop_centengine () {
	if [ "$1" = "start" ]
	then
		#enable centengine
		/sbin/chkconfig centengine on
	else
		#disable & stop centengine
		/sbin/chkconfig centengine off
		pidof centengine >/dev/null
		if [ -n $? ]
		then
			/etc/init.d/centengine stop
		fi
		echo "Centreon license error" | logger
		echo "CentEngine disabled and stopped due a license error" | logger
		if [ ! $LIC_STATUS = $LIC_STATUS_NO_LIC ]
		then
			send_mail "Centreon license error" "CentEngine disabled and stopped due a license error"
		fi
	fi
}

function check_licenses () {
	parse_licfile
	create_GUID
	check_centreon_licenses
	check_rancid_license
	check_nedi_license
	check_syslog_license
	check_glpi_license
}

function write_statusfile () {

	local current_time
	
	echo "$(cat /opt/bi-s/software/scripts/lic2/issue)" > /etc/issue

	IP=$(ifconfig |grep "inet addr" |grep -v "127.0.0.1" | awk '{ print $2 }' | awk -F: '{ print $2 }')
	echo "# Host IP address: $IP" >> /etc/issue
	echo "#" >> /etc/issue
	if [ $EXIT_STATUS = 0 ]
	then
		echo "# i-Vertix license valid" >> /etc/issue
	else
		echo "# ERROR: i-Vertix license error" >> /etc/issue
	fi
	echo "#########################################" >> /etc/issue

	current_time=$(date "+%d.%m.%Y-%H:%M:%S")
	echo "$LIC_GEN_STATUS_TEXT,$LIC_GEN_STATUS" > $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_TEXT,$LIC_STATUS" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_HOST_TEXT,$LIC_STATUS_HOST" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_SERVICE_TEXT,$LIC_STATUS_SERVICE" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_POLLER_TEXT,$LIC_STATUS_POLLER" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_RANCID_TEXT,$LIC_STATUS_RANCID" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_NEDI_TEXT,$LIC_STATUS_NEDI" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_SYSLOG_TEXT,$LIC_STATUS_SYSLOG" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "$LIC_STATUS_GLPI_TEXT,$LIC_STATUS_GLPI" >> $PATH_LICFILE/$FILE_LIC_STATUS
	echo "Timestamp,$current_time" >> $PATH_LICFILE/$FILE_LIC_STATUS
}

function check_glpi_license () {
	local a

	if [ -z "$GLPI_LIC" ]
	then
		LIC_STATUS_GLPI="$LIC_STATUS_NO_LIC"
		LIC_STATUS_GLPI_TEXT="No GLPI license found"
	else	
		#rebuild license key
		a="$GUID GLPI $GLPI"
		GLPI_LIC2="$(echo -n "$a" | md5sum)"
		
		if [ "$GLPI_LIC2" = "$GLPI_LIC" ]
		then
			LIC_STATUS_GLPI="$LIC_STATUS_OK"
			LIC_STATUS_GLPI_TEXT="GLPI license ok"
		else
			LIC_STATUS_GLPI="$LIC_STATUS_ERR_LIC"
			LIC_STATUS_GLPI_TEXT="GLPI license key not valid"
		fi
	fi
}

function check_syslog_license () {
	local a

	if [ -z "$SYSLOG_LIC" ]
	then
		LIC_STATUS_SYSLOG="$LIC_STATUS_NO_LIC"
		LIC_STATUS_SYSLOG_TEXT="No Syslog license found"
	else	
		#rebuild license key
		a="$GUID Syslog $SYSLOG"
		SYSLOG_LIC2="$(echo -n "$a" | md5sum)"
		
		if [ "$SYSLOG_LIC2" = "$SYSLOG_LIC" ]
		then
			LIC_STATUS_SYSLOG="$LIC_STATUS_OK"
			LIC_STATUS_SYSLOG_TEXT="Syslog license ok"
		else
			LIC_STATUS_SYSLOG="$LIC_STATUS_ERR_LIC"
			LIC_STATUS_SYSLOG_TEXT="Syslog license key not valid"
		fi
	fi
}

function check_rancid_license () {
	local a

	if [ -z "$RANCID_LIC" ]
	then
		LIC_STATUS_RANCID="$LIC_STATUS_NO_LIC"
		LIC_STATUS_RANCID_TEXT="No Rancid license found"
	else	
		#rebuild license key
		a="$GUID Rancid $RANCID"
		RANCID_LIC2="$(echo -n "$a" | md5sum)"
		
		if [ "$RANCID_LIC2" = "$RANCID_LIC" ]
		then
			LIC_STATUS_RANCID="$LIC_STATUS_OK"
			LIC_STATUS_RANCID_TEXT="Rancid license ok"
		else
			LIC_STATUS_RANCID="$LIC_STATUS_ERR_LIC"
			LIC_STATUS_RANCID_TEXT="Rancid license key not valid"
		fi
	fi
}

function check_nedi_license () {
	local a

	if [ -z "$NEDI_LIC" ]
	then
		LIC_STATUS_NEDI="$LIC_STATUS_NO_LIC"
		LIC_STATUS_NEDI_TEXT="No NeDi license found"
	else
		#rebuild license key
		a="$GUID NeDi $NEDI"
		NEDI_LIC2="$(echo -n "$a" | md5sum)"
		
		if [ "$NEDI_LIC2" = "$NEDI_LIC" ]
		then
			LIC_STATUS_NEDI="$LIC_STATUS_OK"
			LIC_STATUS_NEDI_TEXT="NeDi license ok"
		else
			LIC_STATUS_NEDI="$LIC_STATUS_ERR_LIC"
			LIC_STATUS_NEDI_TEXT="NeDi license key not valid"
		fi
	fi
}

function check_centreon_licenses () {
	#IF len(CENTREON_TYPE)=0
	if [ -z "$CENTREON_TYPE" ]
	then
		LIC_STATUS="$LIC_STATUS_NO_LIC"
		LIC_STATUS_TEXT="No Centreon license found"
	else
		check_centreon_lic_type
		check_centreon_hosts
		check_centreon_services
		check_centreon_pollers
	fi
}

function check_centreon_lic_type () {
	local a
	
	#rebuild license key
	a="$GUID Centreon-LIC-Typ $CENTREON_TYPE"
	CENTREON_LIC_TYPE2="$(echo -n "$a" | md5sum)"
	
	if [ "$CENTREON_LIC_TYPE2" = "$CENTREON_LIC_TYPE" ]
	then
		LIC_STATUS="$LIC_STATUS_OK"
		LIC_STATUS_TEXT="Centreon $CENTREON_TYPE license ok"
	else
		LIC_STATUS="$LIC_STATUS_ERR_LIC"
		LIC_STATUS_TEXT="Centreon Typ license key not valid"
	fi
}

function check_centreon_hosts () {
	local a
	
	#rebuild license key
	a="$GUID Centreon-LIC-NoHost $CENTREON_NOHOST"
	CENTREON_LIC_NOHOST2="$(echo -n "$a" | md5sum)"

	if [ "$CENTREON_LIC_NOHOST2" = "$CENTREON_LIC_NOHOST" ]
	then
		if [ "$CENTREON_NOHOST" = "unlimited" ]
		then
			LIC_STATUS_HOST="$LIC_STATUS_OK"
			LIC_STATUS_HOST_TEXT="$CENTREON_NOHOST hosts licensed"
		else
			get_centreon_active_hosts
			if [ "$CENTREON_ACTIVE_HOSTS" -gt "$CENTREON_NOHOST" ]
			then
				LIC_STATUS_HOST="$LIC_STATUS_ERR"
				let "a = ($CENTREON_NOHOST - $CENTREON_ACTIVE_HOSTS) * -1"
				LIC_STATUS_HOST_TEXT="$a missing host licenses"
			else
				LIC_STATUS_HOST="$LIC_STATUS_OK"
				LIC_STATUS_HOST_TEXT="$CENTREON_NOHOST hosts licensed"
			fi
		fi
	else
		LIC_STATUS_HOST="$LIC_STATUS_ERR_LIC"
		LIC_STATUS_HOST_TEXT="Centreon host license key not valid"
	fi
}

function get_centreon_active_hosts () {
	local a
	while read a
	do
		CENTREON_ACTIVE_HOSTS="$a"
	done < <(echo "select count(*) no_hosts from lic_centreon_active_hosts" | mysql $db -u $user -p$pwd)
}

function check_centreon_services () {
	local a
	
	#rebuild license key
	a="$GUID Centreon-LIC-NoService $CENTREON_NOSERVICE"
	CENTREON_LIC_NOSERVICE2="$(echo -n "$a" | md5sum)"

	if [ "$CENTREON_LIC_NOSERVICE2" = "$CENTREON_LIC_NOSERVICE" ]
	then
		if [ "$CENTREON_NOSERVICE" = "unlimited" ]
		then
			LIC_STATUS_SERVICE="$LIC_STATUS_OK"
			LIC_STATUS_SERVICE_TEXT="$CENTREON_NOSERVICE services licensed"
		else
			get_centreon_active_services
			if [ "$CENTREON_ACTIVE_SERVICES" -gt "$CENTREON_NOSERVICE" ]
			then
				LIC_STATUS_SERVICE="$LIC_STATUS_ERR"
				let "a = ($CENTREON_NOSERVICE - $CENTREON_ACTIVE_SERVICES) * -1"
				LIC_STATUS_SERVICE_TEXT="$a missing service licenses"
			else
				LIC_STATUS_SERVICE="$LIC_STATUS_OK"
				LIC_STATUS_SERVICE_TEXT="$CENTREON_NOSERVICE services licensed"
			fi
		fi
	else
		LIC_STATUS_SERVICE="$LIC_STATUS_ERR_LIC"
		LIC_STATUS_SERVICE_TEXT="Centreon services license key not valid"
	fi
}

function get_centreon_active_services () {
	local a
	while read a
	do
		CENTREON_ACTIVE_SERVICES="$a"
	done < <(echo "select count(*) no_services from lic_centreon_active_services" | mysql $db -u $user -p$pwd)
}

function check_centreon_pollers () {
	local a
	
	#rebuild license key
	a="$GUID Centreon-LIC-NoPoller $CENTREON_NOPOLLER"
	CENTREON_LIC_NOPOLLER2="$(echo -n "$a" | md5sum)"

	if [ "$CENTREON_LIC_NOPOLLER2" = "$CENTREON_LIC_NOPOLLER" ]
	then
		if [ "$CENTREON_NOPOLLER" = "unlimited" ]
		then
			LIC_STATUS_POLLER="$LIC_STATUS_OK"
			LIC_STATUS_POLLER_TEXT="$CENTREON_NOPOLLER poller licensed"
		else
			get_centreon_active_pollers
			if [ "$CENTREON_ACTIVE_POLLERS" -gt "$CENTREON_NOPOLLER" ]
			then
				LIC_STATUS_POLLER="$LIC_STATUS_ERR"
				let "a = ($CENTREON_NOPOLLER - $CENTREON_ACTIVE_POLLERS) * -1"
				LIC_STATUS_POLLER_TEXT="$a missing poller licenses"
			else
				LIC_STATUS_POLLER="$LIC_STATUS_OK"
				LIC_STATUS_POLLER_TEXT="$CENTREON_NOPOLLER poller licensed"
			fi
		fi
	else
		LIC_STATUS_POLLER="$LIC_STATUS_ERR_LIC"
		LIC_STATUS_POLLER_TEXT="Centreon poller license key not valid"
	fi
}

function get_centreon_active_pollers () {
	local a
	while read a
	do
		CENTREON_ACTIVE_POLLERS="$a"
	done < <(echo "select count(*) no_poller from lic_centreon_active_pollers" | mysql $db -u $user -p$pwd)
}

function check_licfile_exist () {
	if [ ! -f $PATH_LICFILE/$FILE_LIC ]; then
		LIC_GEN_STATUS="$LIC_STATUS_ERR_LIC"
		LIC_GEN_STATUS_TEXT="No i-Vertix license file found"
		#update_licstatus $ERR_DESC
	else
		LIC_GEN_STATUS="$LIC_STATUS_OK"
		LIC_GEN_STATUS_TEXT="i-Vertix license file found"
	fi
}

function update_licstatus () {
	local LICSTATCSV
	local help

	if [ "$1" = "$ERR_DESC" -o "$1" = "$WARN_DESC" ]
	then
		echo "$LICSTATUS" | logger
	fi
	LICSTATCSV="$(cat $PATH_LICFILE/$FILE_LIC_CSV |grep 'Status,')"
	if [ -z "$LICSTATCSV" ]; then
		#Insert status line
		echo "Status,$LICSTATUS,$1" >> $PATH_LICFILE/$FILE_LIC_CSV
	else
		#Update status line
		help=sed -n '/Status/=' $PATH_LICFILE/$FILE_LIC_CSV
		sed -i "$helps/.*/Status,${LICSTATUS},${1}/" $PATH_LICFILE/$FILE_LIC_CSV
	fi
}

function parse_licfile() {
	#decode license file
	openssl enc -d -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in $PATH_LICFILE/$FILE_LIC -out $PATH_TMP/$FILE_LIC_TMP

	#read license file
	COMPANY="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Company,')"
	COMPANY=${COMPANY:8}
	EMAIL="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'EMail,')"
	EMAIL=${EMAIL:6}
	CREATE_DATE="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Create-Date,')"
	CREATE_DATE=${CREATE_DATE:12}
	CENTREON_TYPE="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-Typ,')"
	CENTREON_TYPE=${CENTREON_TYPE:13}
	CENTREON_NOHOST="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-NoHost,')"
	CENTREON_NOHOST=${CENTREON_NOHOST:16}
	CENTREON_NOSERVICE="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-NoService,')"
	CENTREON_NOSERVICE=${CENTREON_NOSERVICE:19}
	CENTREON_NOPOLLER="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-NoPoller,')"
	CENTREON_NOPOLLER=${CENTREON_NOPOLLER:18}
	CENTREON_LIC_TYPE="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-LIC-Typ,')"
	CENTREON_LIC_TYPE=${CENTREON_LIC_TYPE:17}
	CENTREON_LIC_NOHOST="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-LIC-NoHost,')"
	CENTREON_LIC_NOHOST=${CENTREON_LIC_NOHOST:20}
	CENTREON_LIC_NOSERVICE="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-LIC-NoService,')"
	CENTREON_LIC_NOSERVICE=${CENTREON_LIC_NOSERVICE:23}
	CENTREON_LIC_NOPOLLER="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Centreon-LIC-NoPoller,')"
	CENTREON_LIC_NOPOLLER=${CENTREON_LIC_NOPOLLER:22}
	RANCID="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Rancid,')"
	RANCID=${RANCID:7}
	NEDI="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'NeDi,')"
	NEDI=${NEDI:5}
	SYSLOG="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Syslog,')"
	SYSLOG=${SYSLOG:7}
	GLPI="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'GLPI,')"
	GLPI=${GLPI:5}
	RANCID_LIC="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Rancid-LIC,')"
	RANCID_LIC=${RANCID_LIC:11}
	NEDI_LIC="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'NeDi-LIC,')"
	NEDI_LIC=${NEDI_LIC:9}
	SYSLOG_LIC="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'Syslog-LIC,')"
	SYSLOG_LIC=${SYSLOG_LIC:11}
	GLPI_LIC="$(cat $PATH_TMP/$FILE_LIC_TMP |grep 'GLPI-LIC,')"
	GLPI_LIC=${GLPI_LIC:9}
	rm -rf $PATH_TMP/$FILE_LIC_TMP
}

function get_UUID () {
	UUID="$(/usr/sbin/dmidecode -s system-uuid)"
}

function create_GUID () {
  get_UUID
  GUID="$UUID $COMPANY $CREATE_DATE"
}


if [ "$1" = "--version" ]
then
	echo "check_lic version $version"
	exit
fi
start