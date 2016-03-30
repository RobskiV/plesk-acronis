# @copyright (c) 2002-2016 Acronis International GmbH. All righs reserved.
# @author Alexey Sakharov (alexey.sakharov@acronis.com)

from argparse import ArgumentParser
from datetime import datetime
import logging
import os
import shutil
import sys
import zipfile

PLESK_WORKSPACES_ROOT = '/var/www/vhosts'
ACRONIS_BACKUPS_PATH  = '/usr/local/psa/var/modules/acronis-backup/tmp'
LOG_FILE_PATH         = '/usr/local/psa/var/modules/acronis-backup/logs/acronis-backup-extension.log'


def _parse_arguments():
    parser = ArgumentParser(add_help=True)
    parser.add_argument('--subscription', help='subscription name')
    return parser.parse_args()


def _configure_logging():
    logging.basicConfig(filename=LOG_FILE_PATH, level=logging.DEBUG)


def _get_backup_path(subscription):
    return os.path.join(ACRONIS_BACKUPS_PATH, "{}.zip".format(subscription))


def _validate_environment(subscription):
    backup_path = _get_backup_path(subscription)
    if not os.path.isfile(backup_path):
        raise Exception("Backup archive '{}' not found".format(backup_path))
    if not os.path.exists(os.path.join(PLESK_WORKSPACES_ROOT, subscription)):
        raise Exception("Workspace '{}' is absent.".format(subscription))


def _rename_legacy_subscription(subscription):
    workspace = os.path.join(PLESK_WORKSPACES_ROOT, subscription)
    date = "{}-{}-{}".format(datetime.now().year, datetime.now().month, datetime.now().day)
    new_name = os.path.join(PLESK_WORKSPACES_ROOT, "{}.{}.backup".format(subscription, date))
    if os.path.exists(new_name):
        import shutil
        shutil.rmtree(new_name)
    logging.debug("Rename legacy workspace, new name %s.", new_name)
    os.rename(workspace, new_name)


def _restore_workspace(subscription):
    backup_path = _get_backup_path(subscription)
    logging.debug("Restore archive for %s subscription.", subscription)
    with zipfile.ZipFile(backup_path, "r") as z:
        root_path = z.namelist()[0]
        z.extractall(PLESK_WORKSPACES_ROOT)
        shutil.move(os.path.join(PLESK_WORKSPACES_ROOT, root_path), PLESK_WORKSPACE_ROOT))
    logging.debug("Restore of %s subscription completed.", subscription)


def main(args):
    try:
        _configure_logging()
        logging.debug("Start restore of %s subscription.", args.subscription)
        _validate_environment(args.subscription)
        _rename_legacy_subscription(args.subscription)
        _restore_workspace(args.subscription)
        logging.debug("Restore of %s subscription finished successfully.", args.subscription)
        sys.exit(0)
    except Exception as err:
        import traceback
        traceback.print_exc(file=sys.stdout)
        logging.exception(err)
        sys.exit(1)


if __name__ == '__main__':
    main(_parse_arguments())
