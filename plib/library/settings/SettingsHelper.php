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
                'host' => null,
                'username' => null,
                'password' => null,
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
}