#Installation instruction

MySQL Backup Benutzer erstellen, falls nicht vorhanden mit Leserecht auf alle Datenbanken:

    CREATE USER 'backup'@'localhost' IDENTIFIED BY '***';

    GRANT SELECT ,
    RELOAD ,
    SHOW DATABASES ,
    LOCK TABLES ON * . * TO 'backup'@'localhost' 
    IDENTIFIED BY '***' 
    WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
    
Das Password des Backup-Users muss noch im Skript /opt/bi-s/software/scripts/backup/backup.sh angepasst werden.


Crontab für Backup-User einspielen und anpassen
- das File backup unter /etc/cron.d/ einspielen.
- Cronjob kann im File angepasst werden.