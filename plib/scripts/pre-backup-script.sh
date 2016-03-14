#!/bin/bash

##
## @Copyright В© 2002-2016 Acronis International GmbH. All rights reserved
##

## The script is executed before Acronis Backup creates a snapshot.
## It creates metadata.xml file with list of Plesk subscriptions and dumps databases.
## metadata.xml is used for listing of webspaces and databases during recovery.

source config.sh

EXTENSION_DATA_DIR="/usr/local/psa/var/modules/acronis-backup"

function log() {
  echo "$(date -Ins):    $1" >> "$LOGFILE"
}

function create_metadata() {
  log "Creating the metadata file..."
  METADATA_FILE="$EXTENSION_DATA_DIR/metadata.xml"

  plesk bin extension --exec acronis-backup create-meta-xml.php
  EXIT_CODE="$?"

  if [ $EXIT_CODE = "0" ]
  then
    if [ -f $METADATA_FILE ]
    then
        log "The metadata file has been generated succeffully."
    else
        log "Error: The metadata file $METADATA_FILE is not found."
        exit -1
    fi
  else
    exit $EXIT_CODE
  fi
}

function dump_databases() {
  log "Dumping databases..."
  MYSQL_PASSWORD=`cat /etc/psa/.psa.shadow`
  databases=`mysql --user=$MYSQL_USER --password=$MYSQL_PASSWORD -e "SHOW DATABASES;" | tr -d "| " | grep -v Database`
  
  for db in $databases; do
    skip_db=false
    for ((index=0; $index<${#SKIP_DATABASES[@]}; index++)); do 
      if [ "${SKIP_DATABASES[$index]}" = "$db" ]; then
        log "Skipping database: $db"
        skip_db=true
        fi
    done
    if [ $skip_db != true ]
    then
      log "Dumping database: $db"
      mysqldump -u$MYSQL_USER -p$MYSQL_PASSWORD --databases $db > $DB_DUMPS_LOCATION/$db.sql 2>>$LOGFILE
      gzip $DB_DUMPS_LOCATION/$db.sql
    fi 
  done
  log "The dumps have been created."
}

## Free database to create consistent snapshot.
## TBD.

function main() {
  log "$(date -Ins) ---------------------------------------------------------------------"
  log "Backup started"
  log "Executing pre data capture scripts..."
  create_metadata
  if [ $BACKUP_DATABASES = true ]
  then
    dump_databases
  fi
  log "Pre pre data capture scripts have been executed."
}

main

exit 0
