<?php
/**
 * Diese Datei ist Bestandteil des Strato Name Bundle
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 * © Strato AG
 *
 * Date: 08.03.16
 * Time: 15:28
 *
 * KurzInfo zur Datei
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