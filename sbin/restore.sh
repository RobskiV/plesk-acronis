#!/bin/bash

#
# This script is part of the Acronis Plesk Extension and licensed under Apache v.2
# Author: Vincent Fahrenholz <fahrenholz@strato-rz.de>
#

COMMAND="acropsh /usr/local/psa/admin/plib/modules/acronis-backup/scripts/restore.py --subscription=$1"
eval $COMMAND