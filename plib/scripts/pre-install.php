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

$memoryLimit = ini_get('memory_limit');
switch (true) {
    case false !== strpos($memoryLimit, 'K'):
        $memoryLimit = (int)$memoryLimit * 1024;
        break;
    case false !== strpos($memoryLimit, 'M'):
        $memoryLimit = (int)$memoryLimit * 1024 * 1024;
        break;
    default:
        $memoryLimit = (int)$memoryLimit;
}
if ($memoryLimit < 32 * 1024 * 1024) {
    echo "$memoryLimit is too small\n";
    exit(1);
}
exit(0);