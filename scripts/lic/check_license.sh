#!/bin/bash

FILE_LIC_TXT="osiris.lic.txt"
FILE_LIC_CSV="osiris.lic.csv"
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
        echo "`/sbin/ifconfig`" >> $TMP_MAIL_TEXT
        TEXT="$(cat $TMP_MAIL_TEXT)"

        #add License mail to recipient if exists
        #if [[ "$LICMAIL" =~ "^[A-Za-z0-9._%+-]+<b>@</b>[A-Za-z0-9.-]+<b>\.</b>[A-Za-z]{2,4}$" ]]; then
        #       EMAIL_RECIPIENT = "$EMAIL_RECIPIENT,$LICMAIL
        #fi
        MAIL="Subject: $SUBJECT\nFrom: $EMAIL_SENDER\nTo: $EMAIL_RECIPIENT\n\n$TEXT"
        rm -rf $TMP_MAIL_TEXT
        echo -e $MAIL | /usr/sbin/sendmail -t
}

send_shutdown_command() {
        #create a empty shutdown file
        rm -rf $SHUTDOWN_CMD
        touch $SHUTDOWN_CMD
        chmod +x $SHUTDOWN_CMD

        echo "$CMD_PARAM" >> $SHUTDOWN_CMD
}

parse_licfile() {
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

update_licstatus () {
        local LICSTATCSV

        LICSTATCSV="$(cat $PATH_LICFILE/$FILE_LIC_CSV |grep 'Status,')"
        if [ -z "$LICSTATCSV" ]; then
                echo "Status,$LICSTATUS" >> $PATH_LICFILE/$FILE_LIC_CSV
        else
                #Update line 6 in CSV File
                sed -i "6s/.*/Status,${LICSTATUS}/" $PATH_LICFILE/$FILE_LIC_CSV
        fi
}

#read system uuid
SYSGUID="$(/usr/sbin/dmidecode |grep UUID)"
SYSGUID=${SYSGUID:7}

#check if license file exists
if [ ! -f $PATH_LICFILE/$FILE_LIC ]; then
        LICSTATUS = "there is NO Osiris license file"
        echo "$LICSTATUS" | logger -s
        echo "$LICSTATUS" >> /etc/issue
        update_licstatus

        #create shutdown command
        CMD_PARAM="/sbin/shutdown -P now"
        send_shutdown_command

        #send email
        MAIL_SUBJECT="NO Osiris license file"
        MAIL_TEXT="Es ist kein Osiris Lizenzfile vorhanden, shutdown geplant"
        send_mail
        exit $?
fi

#read license file
parse_licfile

#recalculate SN
S1="$SYSGUID $COMP_NAME_LIC $EXP_DATE"
SN="$(echo -n "$S1" | md5sum)"

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
        LICSTATUS="Osiris license is valid"
        echo "$LICSTATUS" | logger -s
        echo "$LICSTATUS" >> /etc/issue
        update_licstatus

        #NO shutdown command
        CMD_PARAM=""
        send_shutdown_command

        if (( "$DIFF_DAYS" < 30 )); then
                LICSTATUS="your Osiris license will be expire in $DIFF_DAYS days"
                echo "$LICSTATUS" | logger -s
                echo "$LICSTATUS" >> /etc/issue
                update_licstatus

                #send email
                MAIL_SUBJECT="$COMP_NAME_LIC: Osiris licensed will expire in $DIFF_DAYS days"
                MAIL_TEXT="Osiris Lizenz verfällt in $DIFF_DAYS Tagen"
                send_mail
                exit $?
    fi
elif [ "$SN" != "$SN_LIC" ] || (( "$DIFF_DAYS" <= 0 )); then
        if  (( "$DIFF_DAYS" <= 0 )); then
                LICSTATUS="your Osiris license key is expired"
                echo "$LICSTATUS" | logger -s
                echo "$LICSTATUS" >> /etc/issue
                update_licstatus

                #create shutdown command
                CMD_PARAM="/sbin/shutdown -P now"
                send_shutdown_command

                #send email
                MAIL_SUBJECT="$COMP_NAME_LIC: Osiris licensed is expired"
                MAIL_TEXT="Osiris lizenziert ist verfallen, shutdown geplant"
                send_mail
        else
                LICSTATUS="your Osiris license key is not valid"
                echo "$LICSTATUS" | logger -s
                echo "$LICSTATUS" >> /etc/issue
                update_licstatus

                #create shutdown command
                CMD_PARAM="/sbin/shutdown -P now"
                send_shutdown_command

                #send email
                MAIL_SUBJECT="$COMP_NAME_LIC: Osiris not licensed"
                MAIL_TEXT="Osiris ist nicht lizenziert, shutdown geplant"
                send_mail
        fi
fi