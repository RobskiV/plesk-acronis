<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 11.03.16
 * Time: 16:25
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */


if (!file_exists('/usr/local/psa/var/modules/acronis-backup')) {
    mkdir('/usr/local/psa/var/modules/acronis-backup', 0777, true);
}
chown('/usr/local/psa/var/modules/acronis-backup', 'psaadm');