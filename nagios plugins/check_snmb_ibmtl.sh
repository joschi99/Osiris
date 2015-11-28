#!/bin/bash

#   Author:  Jonathan Kaiser
#   Date:    2013
#   Purpose: SNMP OID check for the Tape Library devices listed, and returns perfdata compatible format
#      Devices: TL4000, TL2000
#   Dependances: net-snmp
#   Version: 0.1

## Devault Variables
HOSTNAME=
SNMP_VERSION=2c
COMMUNITY=public
OUTPUT_PERFDATA=false
TIMEOUT=1
RETRY=1
TYPE_CHECK=

WARN_THRESHOLD=
CRIT_THRESHOLD=
THRESHOLDS_SET=false

OK=0
WARNING=1
CRITICAL=2
UNKNOWN=3
OUTPUT_DETAIL=


## Help declaration
function help ()
{
    echo "use : $0 -H hostname -C community -t [check type] -p -h"
    echo ""
    echo "  -H  : hostname or IP address of ups device"
    echo "  -C  : snmp community (default:public)"
    echo ""
    echo "  Check Type Options"
    echo "      info  : Tape Library information"
    echo "      status  : Tape Library status"
    echo ""
    echo "  -p  : Include perfdata"
    echo ""
    echo "  -h  : print this message"
    exit $UNKNOWN
}

while getopts "H:C:t:ph" Input;
do
    case ${Input} in
    H)      HOSTNAME=${OPTARG};;
    C)      COMMUNITY=${OPTARG};;
    t)      case ${OPTARG} in
                 info)      TYPE_CHECK=info;;
                 status)   TYPE_CHECK=status;;
             esac;;
    p)      OUTPUT_PERFDATA=true;;
    h)      help;;
    *)      echo "Invalid input"
            exit $UNKNOWN
            ;;
    esac
done

get="snmpget -c "$COMMUNITY" -v "$SNMP_VERSION" -t "$TIMEOUT" -r "$RETRY" -O qv "$HOSTNAME" "

# Used to check for connectrivity
sysDescr=".1.3.6.1.2.1.1.1.0"

# shadowId
shadowIdDisplayName="1.3.6.1.4.1.674.10893.2.102.1.1.0"
shadowIdDescription="1.3.6.1.4.1.674.10893.2.102.1.2.0"
shadowAgentVendor="1.3.6.1.4.1.674.10893.2.102.1.3.0"
shadowIdAgentVersion="1.3.6.1.4.1.674.10893.2.102.1.4.0"
shadowIdBuildNumber="1.3.6.1.4.1.674.10893.2.102.1.5.0"
shadowIdURL="1.3.6.1.4.1.674.10893.2.102.1.6.0"

# shadowStatus
shadowStatusGlobalStatus="1.3.6.1.4.1.674.10893.2.102.2.1.0"
shadowStatusLastGlobalStatus="1.3.6.1.4.1.674.10893.2.102.2.2.0"
shadowStatusTimeStamp="1.3.6.1.4.1.674.10893.2.102.2.3.0"
shadowStatusGetTimeOut="1.3.6.1.4.1.674.10893.2.102.2.4.0"
shadowStatusRefreshRate="1.3.6.1.4.1.674.10893.2.102.2.5.0"
shadowStatusGeneratingTrapFlag="1.3.6.1.4.1.674.10893.2.102.2.6.0"


# Connectivity Check
check=`$get$sysDescr 2>&1`
if [[ $check == Timeout* ]]; then
    echo $check
    exit $UNKNOWN
fi


function info () {
    TL_Display=$($get$shadowIdDisplayName 2>&1 | tr -d "\"")
    TL_Desc=$($get$shadowIdDescription 2>&1 | tr -d "\"")
    TL_Vendor=$($get$shadowAgentVendor 2>&1 | tr -d "\"")
    TL_Build=$($get$shadowIdBuildNumber 2>&1 | tr -d "\"")
    TL_URL=$($get$shadowIdURL 2>&1 | tr -d "\"")
    
    OUTPUT_DETAIL=$TL_Vendor" "$TL_Display" v:"$TL_Build" : "$TL_Desc
    RET_CODE=$OK
    OUTPUT_PERFDATA=false
}


function stat () {
    TL_STAT=$($get$shadowStatusGlobalStatus 2>&1)
    
    case $TL_STAT in
        1)
            OUTPUT_DETAIL="Library is in an OTHER state "
            RET_CODE=$UNKNOWN;;
        2)
            OUTPUT_DETAIL="Library is in an UNKNOWN state "
            RET_CODE=$UNKNOWN;;
        3)
            OUTPUT_DETAIL="Library is ok "
            RET_CODE=$OK;;
        4)
            OUTPUT_DETAIL="Library is in a NON-CRITICAL state "
            RET_CODE=$WARNING;;
        5)
            OUTPUT_DETAIL="Library is in a CRITICAL state "
            RET_CODE=$CRITICAL;;
        6)
            OUTPUT_DETAIL="Library is in a NON-RECOVERABLE state "
            RET_CODE=$CRITICAL;;
        *)
            OUTPUT_DETAIL="$get$shadowStatusGlobalStatus = $TL_STAT "
            RET_CODE=$UNKNOWN;;
    esac
    PERFDATA="globalStatus=$TL_STAT"
}


case ${TYPE_CHECK} in
    info)       info;;
    status)       stat;;
    *)          exit $UNKNOWN;;
esac

case $RET_CODE in
    0)
        NAGIOS_OUT="OK: $OUTPUT_DETAIL";;
    1)
        NAGIOS_OUT="WARNING: $OUTPUT_DETAIL";;
    2)
        NAGIOS_OUT="CRITICAL: $OUTPUT_DETAIL";;
    *)
        NAGIOS_OUT="UNKNOWN: $OUTPUT_DETAIL";;
esac

if $OUTPUT_PERFDATA ; then
       echo $NAGIOS_OUT"| "$PERFDATA
else
       echo $NAGIOS_OUT
fi

exit $RET_CODE