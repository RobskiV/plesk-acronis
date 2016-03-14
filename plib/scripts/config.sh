##
## @Copyright В© 2002-2016 Acronis International GmbH. All rights reserved.
##

## Configuration file for Acronis Backup extension for Plesk

EXTENSION_DATA_DIR="/usr/local/psa/var/modules/acronis-backup"

## Backup databases 
BACKUP_DATABASES=true
SKIP_DATABASES=(information_schema mysql performance_schema)
DB_DUMPS_LOCATION="$EXTENSION_DATA_DIR/databases"

## Plesk DB user
MYSQL_USER="admin"
LOGFILE="$EXTENSION_DATA_DIR/logs/acronis-backup-extension.log"
