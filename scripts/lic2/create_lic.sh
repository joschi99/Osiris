#!/bin/bash
###############################################################################
#
# create_lic.sh - Erstellt das Osiris2.2 Lizenzfile
#
# Copyright (c) 2016 Osiris 2.2 (Contact: info@bi-s.it)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.0
#
# Changelog
#	16.01.2016: Erste Version für Osiris2.2
###############################################################################

declare -r FILE_LIC_CSV="osiris2.lic.csv"
declare -r FILE_LIC="osiris2.lic"

RANCID="not active"
NEDI="not active"
SYSLOG="not active"
GLPI="not active"

function menu () {
  echo "Osiris2.2 Lizenz generator"
  echo "1) General license information"
  echo "2) Activate Centreon"
  echo "3) Activate Rancid"
  echo "4) Activate NeDi"
  echo "5) Activate Syslog"
  echo "6) Activate GLPI"
  echo "8) View License file"
  echo "9) Generate license"
  echo "0) Exit"
  read n
  case $n in
    1) insert_gen_lic_data;;
    2) insert_centreon_lic_data;;
    3) insert_rancid_lic;;
    4) insert_nedi_lic;;
    5) insert_syslog_lic;;
    6) insert_glpi_lic;;
    8) cat $FILE_LIC_CSV;menu;;
    9) create_license;;
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

function create_license () {
  create_GUID
  echo "Osiris License File" > $FILE_LIC_CSV
  echo "Company,$COMPANY" >> $FILE_LIC_CSV
  echo "EMail,$EMAIL" >> $FILE_LIC_CSV
  echo "Create-Date,$CREATE_DATE" >> $FILE_LIC_CSV
  if [ "$A4" = "OK" ]
  then
    create_centreon_LIC
    echo "Centreon-Typ,$CENTREON_TYPE" >> $FILE_LIC_CSV
    echo "Centreon-NoHost,$CENTREON_NOHOST" >> $FILE_LIC_CSV
    echo "Centreon-NoService,$CENTREON_NOSERVICE" >> $FILE_LIC_CSV
    echo "Centreon-NoPoller,$CENTREON_NOPOLLER" >> $FILE_LIC_CSV
    echo "Centreon-LIC-Typ,$CENTREON_LIC_TYPE" >> $FILE_LIC_CSV
    echo "Centreon-LIC-NoHost,$CENTREON_LIC_NOHOST" >> $FILE_LIC_CSV
    echo "Centreon-LIC-NoService,$CENTREON_LIC_NOSERVICE" >> $FILE_LIC_CSV
    echo "Centreon-LIC-NoPoller,$CENTREON_LIC_NOPOLLER" >> $FILE_LIC_CSV
  fi
  create_other_lic
  echo "Rancid,$RANCID" >> $FILE_LIC_CSV
  echo "NeDi,$NEDI" >> $FILE_LIC_CSV
  echo "Syslog,$SYSLOG" >> $FILE_LIC_CSV
  echo "GLPI,$GLPI" >> $FILE_LIC_CSV
  echo "Rancid-LIC,$RANCID_LIC" >> $FILE_LIC_CSV
  echo "NeDi-LIC,$NEDI_LIC" >> $FILE_LIC_CSV
  echo "Syslog-LIC,$SYSLOG_LIC" >> $FILE_LIC_CSV
  echo "GLPI-LIC,$GLPI_LIC" >> $FILE_LIC_CSV
  encrypt_licfile
  echo "License generated"
  menu
}

function create_GUID () {
  GUID="$UUID $COMPANY $CREATE_DATE"
}

function create_centreon_LIC () {
  S1="$GUID Centreon-LIC-Typ $CENTREON_TYPE"
  CENTREON_LIC_TYPE="$(echo -n "$S1" | md5sum)"
  S1="$GUID Centreon-LIC-NoHost $CENTREON_NOHOST"
  CENTREON_LIC_NOHOST="$(echo -n "$S1" | md5sum)"
  S1="$GUID Centreon-LIC-NoService $CENTREON_NOSERVICE"
  CENTREON_LIC_NOSERVICE="$(echo -n "$S1" | md5sum)"
  S1="$GUID Centreon-LIC-NoPoller $CENTREON_NOPOLLER"
  CENTREON_LIC_NOPOLLER="$(echo -n "$S1" | md5sum)"
}

function create_other_lic () {
  if [ "$RANCID" = "active" ]
  then
    S1="$GUID Rancid $RANCID"
    RANCID_LIC="$(echo -n "$S1" | md5sum)"
  fi
  if [ "$NEDI" = "active" ]
  then
    S1="$GUID NeDi $NEDI"
    NEDI_LIC="$(echo -n "$S1" | md5sum)"
  fi
  if [ "$SYSLOG" = "active" ]
  then
    S1="$GUID Syslog $SYSLOG"
    SYSLOG_LIC="$(echo -n "$S1" | md5sum)"
  fi
  if [ "$GLPI" = "active" ]
  then
    S1="$GUID GLPI $GLPI"
    GLPI_LIC="$(echo -n "$S1" | md5sum)"
  fi
}

function encrypt_licfile () {
  openssl enc -aes-256-cbc -pass file:/opt/bi-s/software/scripts/gpg/.gpg_passwd.txt -in $FILE_LIC_CSV -out $FILE_LIC
}

menu
