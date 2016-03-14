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

require_once(__DIR__ . '/../backups/BackupHelper.php');

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
        try {
            $request = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
            $request->request('GET', '/api/ams/backup/plans');
        } catch (RuntimeException $e) {
            $this->_status->addMessage('error', pm_Locale::lmsg('configFailedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'account')));
        }
var_dump($response);

        return array(array(
            "name" => "foo",
            "date"=>  new DateTime(now)
        ),
            array(
                "name" => "bar",
                "date" => new DateTime(now)
            )
        );
    }
}