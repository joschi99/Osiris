#!/bin/bash
echo "Bitte den Company Namen eingeben:"
read COMP_NAME

GUID="$(dmidecode |grep UUID)"

S1="$GUID $COMP_NAME"
SN="$(echo -n "$S1" | md5sum)"

echo "Die Seriennummer für $COMP_NAME lautet:"
echo "Company: $COMP_NAME"
echo "Serial Number: $SN"

echo "Osiris License File" > osiris.lic
echo "Company: $COMP_NAME" >> osiris.lic
echo "Serial Number: $SN" >> osiris.lic