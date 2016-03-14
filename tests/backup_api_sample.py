# @copyright (c) 2002-2016 Acronis International GmbH. All righs reserved.
# @author Alexey Sakharov (alexey.sakharov@acronis.com)

from acronis.tools.http import Session, urljoin
from argparse import ArgumentParser
from datetime import datetime
import sys
import time
import traceback
from urllib.parse import quote


PLESK_BACKUP_PLAN = "PleskforH2074836"
PLESK_SERVER_IP = "81.169.158.35"


def get_plesk_machine(session):
    resources = session.get("{}/api/ams/resources".format(session.host))["data"]
    return next((resource for resource in resources if "ip" in resource and PLESK_SERVER_IP in resource['ip']), None)


def get_recovery_points(session, resource):
    recovery_points = session.get("{}/api/ams/resources/{}/recoverypoints".format(session.host, resource["id"]))["data"]
    return (point for point in recovery_points if point['backupPlan'] == PLESK_BACKUP_PLAN)


def get_last_recovery_point(points):
    newest = None
    max_time = 0
    for item in points:
        t = to_unixtime(item["ItemSliceTime"])
        if t > max_time:
            max_time = t
            newest = item
    return newest


def to_unixtime(isotime):
    mytime = datetime.strptime(isotime[:-6], '%Y-%m-%dT%H:%M:%S')
    return int(time.mktime(mytime.timetuple()))


def quote_uri(uri):
    return quote(uri, safe='')


def restore(session, machine_id, recovery_point):
    backup_id = recovery_point['ItemSliceFile']
    backupUriEncoded = quote_uri(backup_id)
    print("{}/api/ams/archives/{}/backups/{}/items?machineId={}&backupId={}&type=files".format(session.host, "dummy", "dummy", machine_id, backupUriEncoded))
    disks = session.post("{}/api/ams/archives/{}/backups/{}/items?machineId={}&backupId={}&type=files".format(session.host, "dummy", "dummy", machine_id, backupUriEncoded))
    file_path = "{}{}".format(disks["data"][0]["name"].strip(), "/usr/local/psa/var/modules/acronis-backup/metadata.xml")
    payload = {
        "format": "PLAIN",
	"machineId": machine_id,
	"backupId": backup_id,
	"backupUri": backup_id,
	"items": [file_path,],
        "credentials": {},
    }
    data = session.post("{}/api/ams/archives/downloads?machineId={}".format(session.host, machine_id), data=payload)
    output = session.get("{}/api/ams/archives/downloads/{}/<pathfileName>?format=PLAIN&machineId={}&fileName=metadata.xml&start_download=1".format(session.host, data["SessionID"], machine_id))
    with open("metadata.xml", "wb") as out:
        out.write(output)

    zip_path = "{}{}".format(disks["data"][0]["name"].strip(), "/var/www/vhosts")
    zip_payload = {
        "format": "ZIP",
	"machineId": machine_id,
	"backupId": backup_id,
	"backupUri": backup_id,
	"items": [zip_path,],
        "credentials": {},
    }

    data = session.post("{}/api/ams/archives/downloads?machineId={}".format(session.host, machine_id), data=zip_payload)
    output = session.get("{}/api/ams/archives/downloads/{}/<pathfileName>?format=ZIP&machineId={}&fileName=backup.zip&start_download=1".format(session.host, data["SessionID"], machine_id))
    with open("backup.zip", "wb") as out:
        out.write(output)


def get_backup_api_session(hostname, username, password):
    rain_session = Session()
    rain_session.post("https://{}/api/1/login".format(hostname), data={'username': username, 'password': password})
    backup_console = rain_session.get('https://{}/api/1/groups/self/backupconsole'.format(hostname))
    backup_session = Session()
    backup_session.host = backup_console['host']
    backup_session.post("{}/api/remote_connection".format(backup_session.host), data={'access_token': backup_console['token']})
    return backup_session


def main(args):
    try:
        session = get_backup_api_session(args.hostname, args.username, args.password)
        resource = get_plesk_machine(session)
        if not resource:
            raise Exception("Failed to find machine")

        recovery_points = get_recovery_points(session, resource)
        restore(session, resource["id"], get_last_recovery_point(recovery_points))
    except Exception as err:
        traceback.print_exc(file=sys.stdout)
        return

    print('Done.')


def parse_arguments():
    parser = ArgumentParser(add_help=True)
    parser.add_argument('--hostname', help='hostname', default='beta-cloud.acronis.com')
    parser.add_argument('--username', help='account name', required=True)
    parser.add_argument('--password', help='account password', required=True)
    return parser.parse_args()


if __name__ == '__main__':
    main(parse_arguments())
