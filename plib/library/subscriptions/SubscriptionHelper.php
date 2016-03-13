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
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

class Modules_AcronisBackup_Subscriptions_SubscriptionHelper
{

    private static $pleskVersion;

    /**
     * getPleskVersion
     *
     * Description
     *
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
     * _getSubscriptions
     *
     * Description
     *
     *
     * @return array\
     */
    public static function getSubscriptions()
    {
        $login = pm_Session::getClient()->getProperty('login');
        if ('admin' != $login) {
            return [pm_Session::getCurrentDomain()->getName()];
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
        $responseSubscriptions = reset($response->webspace->get);
        if ($responseSubscriptions instanceof SimpleXMLElement) {
            $responseSubscriptions = [$responseSubscriptions];
        }
        $subscriptions = [];
        foreach ($responseSubscriptions as $subscription) {
            $subscriptions[] = (string)$subscription->data->gen_info->name;
        }
        return $subscriptions;
    }
}