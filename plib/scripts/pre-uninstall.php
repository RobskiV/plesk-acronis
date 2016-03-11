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

pm_Context::init('acronisbackup');
$id = pm_Settings::get('customButtonId');
$request = <<<APICALL
<ui>
    <delete-custombutton>
        <filter>
            <custombutton-id>$id</custombutton-id>
        </filter>
    </delete-custombutton>
</ui>
APICALL;
try {
    $response = pm_ApiRpc::getService()->call($request);
    $result = $response->ui->{"delete-custombutton"}->result;
    if (true || 'ok' == $result->status) {
        echo "done\n";
        exit(0);
    } else {
        echo "error $result->errcode: $result->errtext\n";
        exit(1);
    }
} catch(PleskAPIParseException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}