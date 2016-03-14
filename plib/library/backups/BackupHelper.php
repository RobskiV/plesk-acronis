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
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

require_once(__DIR__ . '/../settings/SettingsHelper.php');
require_once(__DIR__ . '/../webapi/Request.php');

class Modules_AcronisBackup_backups_BackupHelper
{
    /**
     * getBackupPlans
     *
     * Description
     *
     *
     * @return array
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
}