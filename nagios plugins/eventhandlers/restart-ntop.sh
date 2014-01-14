#!/bin/sh
#
# Event Handler-Skript um den Ntop-Server auf der lokalen Maschine neu zu starten
#
# Achtung: Dieses Skript startet den Ntop-Server neu, wenn 2 Mal (in einem soften Status)
#          versucht wurde den Dienst zu erreichen  oder der Ntop-Dienst aus entsprechenden
#          Grund in einen harten Fehler-Status wechselt.
#
#Version 1.0

case "$1" in
OK)
        ;;
WARNING)
        ;;
UNKNOWN)
        ;;
CRITICAL)
        case "$2" in
        SOFT)
                case "$3" in
                2)
                        echo -n "Restarting NTOP service (2rd soft critical state)..."
                        # Aufruf des Init-Skriptes zum Neustart des NTOP-Servers
                        sudo /etc/init.d/ntopng restart
                        ;;
                        esac
                ;;

        HARD)
                echo -n "Restarting NTOP service..."
                # Aufruf des Init-Skriptes zum Neustart des NTOP-Servers
                sudo /etc/init.d/ntopng restart
                ;;
        esac
        ;;
esac
exit 0
