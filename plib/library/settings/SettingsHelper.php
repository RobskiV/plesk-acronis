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

/**
 * Class Modules_AcronisBackup_settings_SettingsHelper
 *
 * Helper managing the settings defined by the administrator
 *
 * @category Helper
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class Modules_AcronisBackup_settings_SettingsHelper
{
    /**
     * getAccountSettings
     *
     * Gets the account settings defined by the administrator
     *
     * @return array
     */
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

    /**
     * setAccountSettings
     *
     * Sets the account settings defined by the administrator
     *
     * @param array $settings Settings-Array
     */
    public static function setAccountSettings($settings)
    {
        $settings = json_encode($settings);

        pm_Settings::set('accountSettings', $settings);
    }

    /**
     * getBackupSettings
     *
     * Gets the Backup-Settings defined by the administrator
     *
     * @return array
     */
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

    /**
     * setBackupSettings
     *
     * Sets the backup settings defined by the administrator
     *
     * @param array $settings Settings-Array
     */
    public static function setBackupSettings($settings)
    {
        $settings = json_encode($settings);

        pm_Settings::set('backupSettings', $settings);
    }

    /**
     * getMachineId
     *
     * Gets the Machine-ID as configured in Acronis
     *
     * @return string
     * @throws Exception
     */
    public static function getMachineId()
    {
        $machineId = pm_Settings::get('machineId');

        if ($machineId == null) {
            $machineId = self::retrieveMachineId();
            self::setMachineId($machineId);
        }

        return $machineId;
    }

    /**
     * setMachineId
     *
     * Sets the machine-ID defined in acronis
     *
     * @param string $machineId Machine-ID in the Acronis-Frontend
     */
    private static function setMachineId($machineId)
    {
        pm_Settings::set('machineId', $machineId);
    }

    /**
     * getIpAddresses
     *
     * Gets all Ip-Addresses configured for the server via plesk API
     *
     * @return array
     */
    public static function getIpAddresses()
    {
        $request = "<ip>
            <get/>
        </ip>";

        $elements = json_encode(pm_ApiRpc::getService()->call($request, 'admin'));
        $elements = json_decode($elements, true);

        $elements = $elements['ip']['get']['result']['addresses'];

        $ipAddresses = [];

        foreach ($elements as $element) {
            $ipAddresses[] = $element['ip_address'];
        }

        return $ipAddresses;
    }

    /**
     * retrieveMachineId
     *
     * Retrieves the machine-ID from the Acronis-Server via API call
     *
     * @return string
     * @throws Exception
     */
    private static function retrieveMachineId()
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

        $ipAddresses = Modules_AcronisBackup_settings_SettingsHelper::getIpAddresses();

        foreach ($data as $item) {

            if ($item['type'] == 'machine' && isset($item['ip']) && !empty(array_intersect($ipAddresses, $item['ip']))) {
                $machines[] = $item;
            }
        }

        if (count($machines) != 1) {
            throw new Exception('No unique result available');
        }

        return $machines[0]['id'];
    }
}