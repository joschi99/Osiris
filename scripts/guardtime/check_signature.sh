#!/bin/bash
###############################################################################
#
# check_signature.sh - check if the GuardTime signature is valid
#
# Copyright (c) 2016 i-Vertix NMS (info@pgum.eu)
#
# Development:
#  Jochen Platzgummer
#
# Version 1.0
#
# Changelog
#   29.12.2016: Initial version to check the signature of a logfile
#
###############################################################################

version="1.0"

function check_sign () {
  if [ -f "$file" ]
  then
    /bin/rsgtutil -t -s -P http://verify.guardtime.com/ksi-publications.bin $file
  else
    echo "File $file does not exist"
  fi
}

function help () {
  echo "check_signature version $version"
  echo "Copyright (c) 2016 PGUM Gmbh <info@pgum.eu>"
  echo "Copyright (c) 2016 Jochen Platzgummer"
  echo ""
  echo "This script test if a file has a valid GuardTime signature"
  echo ""
  echo "Usage:"
  echo "check_signature.sh -f file"
  echo ""
  echo "Options:"
  echo "-f FILE"
  echo "   Filename with full path to file"
  echo "-h -help"
  echo "   Show this help"
  echo ""
}


while getopts :hhelpf: option
do
  case "${option}"
  in
    h|help) help
       exit 2
       ;;
    f) file=${OPTARG}
       check_sign
       ;;
  esac
done
