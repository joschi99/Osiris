#!/bin/bash

FILE_LIC_TXT="osiris.lic.txt"
FILE_LIC_CSV="osiris.lic.csv"
FILE_LIC="osiris.lic"

echo "Bitte den Company Namen eingeben:"
read COMP_NAME
echo "Bitte die GUID eingeben:"
read GUID
echo "Bitte das Gültigkeitsdatum eingeben (YYYYMMDD oder never):"
read EXPIRE_DATE
echo "Bitte die E-Mail Adresse eingeben:"
read EMAIL

#calculate SN
S1="$GUID $COMP_NAME $EXPIRE_DATE"
SN="$(echo -n "$S1" | md5sum)"

echo "Die Seriennummer für $COMP_NAME lautet:"
echo "Company: $COMP_NAME"
echo "Expire date: $EXPIRE_DATE"
echo "Serial number: $SN"

#create osiris license txt file
echo "Osiris License File" > $FILE_LIC_TXT
echo "Company: $COMP_NAME" >> $FILE_LIC_TXT
echo "EMail: $EMAIL" >> $FILE_LIC_TXT
echo "Serial number: $SN" >> $FILE_LIC_TXT
echo "Expire date: $EXPIRE_DATE" >> $FILE_LIC_TXT

#encrypt license file
openssl enc -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in $FILE_LIC_TXT -out $FILE_LIC

#remove license txt file
rm -rf $FILE_LIC_TXT

#create osiris license csv file
echo "Company,$COMP_NAME" >> $FILE_LIC_CSV
echo "E-Mail,$EMAIL" >> $FILE_LIC_CSV
echo "Serial Number,$SN" >> $FILE_LIC_CSV
echo "Expire Date,$(date -d $EXPIRE_DATE +%d/%m/%Y)" >> $FILE_LIC_CSV
echo "Created,$(date +%d/%m/%Y)" >> $FILE_LIC_CSV
