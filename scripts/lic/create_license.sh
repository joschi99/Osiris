#!/bin/bash
echo "Bitte den Company Namen eingeben:"
read COMP_NAME
echo "Bitte das Gültigkeitsdatum eingeben (YYYYMMDD oder never):"
read EXPIRE_DATE

GUID="$(dmidecode |grep UUID)"

S1="$GUID $COMP_NAME $EXPIRE_DATE"
SN="$(echo -n "$S1" | md5sum)"

echo "Die Seriennummer für $COMP_NAME lautet:"
echo "Company: $COMP_NAME"
echo "Expire date: $EXPIRE_DATE"
echo "Serial number: $SN"

echo "Osiris License File" > osiris.lic.txt
echo "Company: $COMP_NAME" >> osiris.lic.txt
echo "Serial number: $SN" >> osiris.lic.txt
echo "Expire date: $EXPIRE_DATE" >> osiris.lic.txt

openssl enc -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in osiris.lic.txt -out osiris.lic