<?php

/**
 * This File is part of the plesk-acronis extension
 * (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Eitan Schuler <schuler@strato-rz.de>
 *
 * Date: 3/13/16
 * Time: 2:00 PM
 *
 * Contains the Modules_AcronisBackup_backups_BackupHelper class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

require_once(__DIR__ . '/../settings/SettingsHelper.php');
require_once(__DIR__ . '/../webapi/Request.php');

/**
 * Class Modules_AcronisBackup_backups_BackupHelper
 *
 * Provides library-functions
 *
 * @category Helper
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class Modules_AcronisBackup_backups_BackupHelper
{
    /**
     * getBackupPlans
     *
     * Retrieves the backup plans configured in the Acronis-Frontend and returns them
     *
     * @return array|void
     * @throws Exception
     */
    public static function getBackupPlans()
    {
        $settings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();
        if (! isset($settings['password'])) {
            return;
        }

        $request = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
        $response = $request->request('GET', '/api/ams/backup/plans');

        if ($response['code'] != 200 || !isset ($response['body'])){
            throw new Exception('API returned unexpected response');
        }

        $responseArray = json_decode($response['body'],true);

        $planNames = [];
        foreach ($responseArray['data'] as $instance) {
            $planNames[] = $instance['name'];
        }

        return $planNames;
    }

    /**
     * getRecoveryPoints
     *
     * Description
     *
     *
     */
    public static function getRecoveryPoints()
    {
        $settings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();
        if (! isset($settings['password'])) {
            return;
        }

        $request = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
        $machineId = Modules_AcronisBackup_settings_SettingsHelper::getMachineId();
        $response = $request->request('GET', '/api/ams/resources/' . $machineId . '/recoverypoints');

        if ($response['code'] != 200 || !isset($response['body'])) {
            throw new Exception('API returned unexpected response');
        }

        $responseArray = json_decode($response['body'], true);
        $backupSettings = Modules_AcronisBackup_settings_SettingsHelper::getBackupSettings();

        $recoveryPoints = [];
        foreach ($responseArray['data'] as $instance) {
            if ($instance['backupPlan'] === $backupSettings['backupPlan']) {
                $recoveryPoints[] = array(
                    'ItemSliceName' => $instance['ItemSliceName'],
                    'ItemSliceTime' => $instance['ItemSliceTime'],
                    'ItemSliceFile' => $instance['ItemSliceFile']
                );
            }
        }

        return $recoveryPoints;
    }

    /**
     * getWebspaceBackup
     *
     * Description
     *
     * @param $itemSliceFile
     *
     * @throws Exception
     */
    public static function getWebspaceBackup($itemSliceFile, $domain) {
        if (! isset($itemSliceFile)) {
            throw new Exception('itemSliceFile missing');
        }

        $settings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();
        if (! isset($settings['password'])) {
            return;
        }

        $request = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
        $machineId = Modules_AcronisBackup_settings_SettingsHelper::getMachineId();
        $response = $request->request('POST', '/api/ams/archives/dummy/backups/dummy/items?machineId=' . $machineId . '&backupId=' . urlencode($itemSliceFile) . '&type=files');
        $responseArray = json_decode($response['body'], true);

      //  $domain = pm_Session::getCurrentDomain()->getName();
        $filePath = trim($responseArray["data"][0]["name"]) . "/var/www/vhosts/" . $domain;

        $payload = [
            "format" => "ZIP",
            "machineId" => $machineId,
             "backupId" => $itemSliceFile,
             "backupUri" => $itemSliceFile,
             "items" => [$filePath,],
             "credentials" => []];

        $response = $request->request('POST', '/api/ams/archives/downloads?machineId=' . $machineId, $payload);
        $responseArray = json_decode($response['body'], true);

        $request2 = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
        $response2 = $request2->request('GET', '/api/ams/archives/downloads/' . $responseArray["SessionID"] . '/dummy?format=ZIP&machineId=' . $machineId . '&fileName=backup.zip&start_download=1');
        $filename = $domain . '.zip';
        $fh = fopen('/usr/local/psa/var/modules/acronis-backup/tmp/' . $filename, 'w');
        fwrite($fh, $response2['body']);
        fclose($fh);
        
        return $domain;
    }
}