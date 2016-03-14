<?php

/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 14.03.16
 * Time: 09:00
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */
class Modules_AcronisBackup_settings_SettingsHelper
{
    public static function getAccountSettings()
    {
        $settings = pm_Settings::get('accountSettings', null);

        if ($settings == null) {
            $settings = array(
                'host' => null,
                'username' => null,
                'password' => null,
                'serverIp' => null,
            );
        } else {
            $settings = json_decode($settings, true);
        }

        return $settings;
    }

    public static function setAccountSettings($settings)
    {
        $settings = json_encode($settings);

        pm_Settings::set('accountSettings', $settings);
    }

    public static function getBackupSettings()
    {
        $settings = pm_Settings::get('backupSettings', null);

        if ($settings == null) {
            $settings = array(
                'encryptionPassword' => null,
                'backupPlan' => null,
            );
        } else {
            $settings = json_decode($settings, true);
        }

        return $settings;
    }

    public static function setBackupSettings($settings)
    {
        $settings = json_encode($settings);

        pm_Settings::set('backupSettings', $settings);
    }

    public static function getMachineId()
    {
        $machineId = pm_Settings::get('machineId');

        if ($machineId == null) {
            $machineId = self::_retrieveMachineId();
            self::setMachineId($machineId);
        }

        return $machineId;
    }

    public static function setMachineId($machineId)
    {
        pm_Settings::set('machineId', $machineId);
    }

    private static function _retrieveMachineId()
    {
        $accountSettings = self::getAccountSettings();

        if ($accountSettings['host'] == null) {
            throw new Exception('Configuration is empty');
        }

        $client = new Modules_AcronisBackup_webapi_Request($accountSettings['host'], $accountSettings['username'], $accountSettings['password']);

        $response = $client->request('get', '/api/ams/resources');

        if ($response['code'] != 200 || !isset($response['body'])) {
            throw new Exception('API returned unexpected response');
        }

        $result = json_decode($response['body'], true);

        $data = $result['data'];
        $machines = [];

        foreach ($data as $item) {

            if ($item['type'] == 'machine' && isset($item['ip']) && in_array($accountSettings['serverIp'], $item['ip'])) {
                $machines[] = $item;
            }
        }

        if (count($machines) != 1) {
            throw new Exception('No unique result available');
        }

        return $machines[0]['id'];
    }
}