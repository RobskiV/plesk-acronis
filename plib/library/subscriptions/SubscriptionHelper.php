<?php
/**
 * This File is part of the plesk-acronis extension
 * (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Eitan Schuler <schuler@strato-rz.net>
 *
 * Date: 3/13/16
 * Time: 11:25 AM
 *
 * Contains SubscriptionHelper class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class Modules_AcronisBackup_subscriptions_SubscriptionHelper
 *
 * Helper providing functions to get infos about configured subscriptions and Plesk
 *
 * @category Helper
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class Modules_AcronisBackup_subscriptions_SubscriptionHelper
{

    /**
     * @var string Current Version of plesk
     */
    private static $pleskVersion;

    /**
     * getPleskVersion
     *
     * Returns the current plesk version
     *
     * @return string
     */
    public static function getPleskVersion()
    {
        if (self::$pleskVersion === null) {
            self::$pleskVersion = pm_ProductInfo::getVersion();
        }

        return self::$pleskVersion;
    }

    /**
     * getSubscriptions
     *
     * Returns all subscriptions the client knows
     *
     * @param null|pm_Client $client Client for which we want the subscriptions
     *
     * @return array
     */
    public static function getSubscriptions($client = null)
    {
        if ($client === null) {
            $login = pm_Session::getClient()->getProperty('login');
            if ('admin' != $login) {
                return [pm_Session::getCurrentDomain()->getName()];
            }
        }
        $request = "<webspace>
            <get>
                <filter/>
                <dataset>
                    <gen_info/>
                </dataset>
            </get>
        </webspace>";

        $response = pm_ApiRpc::getService()->call($request);
        $elements = reset($response->webspace->get);

        if ($elements instanceof SimpleXMLElement) {
            $elements = [$elements];
        }
        $subscriptions = [];

        foreach ($elements as $element) {
            $subscriptions[] = (string) $element->data->gen_info->name;
        }

        return $subscriptions;
    }

    /**
     * getEnabledSubscriptions
     *
     * Returns all subscriptions for which the use of the Acronis-Restore-Functionality was enabled by the administrator
     *
     * @return array
     */
    public static function getEnabledSubscriptions()
    {
        $enabledSubscriptions = pm_Settings::get('enabledSubscriptions');
        if ($enabledSubscriptions == null) {
            $enabledSubscriptions = [];
        } else {
            $enabledSubscriptions = json_decode($enabledSubscriptions, true);
        }

        return $enabledSubscriptions;
    }

    /**
     * setEnabledSubscriptions
     *
     * Sets the subscriptions for which the Acronis-Restore-functionality was enabled by the administrator
     *
     * @param array $enabledSubscriptions Array of subscription-names
     */
    public static function setEnabledSubscriptions($enabledSubscriptions)
    {
        $enabledSubscriptions = json_encode($enabledSubscriptions);
        pm_Settings::set('enabledSubscriptions', $enabledSubscriptions);
    }

    /**
     * getAuthorizationMode
     *
     * Gets the authorization mode configured for the Acronis-Restore-Functionality
     * simple: all subscriptions can use the functionality
     * extended: the administrator has to enable subscriptions manually
     * default: extended
     *
     * @return string
     */
    public static function getAuthorizationMode()
    {
        return pm_Settings::get('authorizationMode', 'extended');
    }

    /**
     * setAuthorizationMode
     *
     * Sets the authorization mode configured for the Acronis-Restore-Functionality
     * simple: all subscriptions can use the functionality
     * extended: the administrator has to enable subscriptions manually
     * default: extended
     *
     * @param string $mode Authorization-Mode
     */
    public static function setAuthorizationMode($mode)
    {
        $authorizationMode = ($mode == 'extended' | $mode == 'simple') ? $mode : 'extended';
        pm_Settings::set('authorizationMode', $authorizationMode);
    }
}