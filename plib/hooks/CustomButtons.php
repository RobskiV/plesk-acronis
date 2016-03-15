<?php

/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 13.03.16
 * Time: 11:21
 *
 * Contains the Modules_AcronisBackup_CustomButtons class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class Modules_AcronisBackup_CustomButtons
 *
 * Hook providing custom buttons for the acronis-backup-extension
 *
 * @category Hook
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class Modules_AcronisBackup_CustomButtons extends pm_Hook_CustomButtons
{
    /**
     * getButtons
     *
     * Generats two custom buttons, one under Tools & Settings, one in the Webspace-Panel. The Webspace-Panel-Button
     * only is generated if the current user is admin or if the current subscription has the restore functionality
     * enabled
     *
     * @return array
     */
    public function getButtons()
    {
        $customButtons=[[
            'place' => self::PLACE_ADMIN_TOOLS_AND_SETTINGS,
            'title' => pm_Locale::lmsg('adminToolsButtonTitle'),
            'description' => pm_Locale::lmsg('adminToolsButtonDescription'),
            'icon' => pm_Context::getBaseUrl() . 'images/icon_64.png',
            'link' => pm_Context::getBaseUrl() . 'index.php/admin/webspacelist',
        ]];
        $domain = pm_Session::getCurrentDomain();
        $client = pm_Session::getClient();
        $authorizationMode = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getAuthorizationMode();
        $enabledSubscriptions = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getEnabledSubscriptions();

        if ($client->isAdmin() || $authorizationMode == 'simple' || (isset($enabledSubscriptions[$domain->getName()]) && $enabledSubscriptions[$domain->getName()])) {
            $customButtons[] = [
                'place' => self::PLACE_DOMAIN_PROPERTIES,
                'title' => pm_Locale::lmsg('domainPropertiesButtonTitle'),
                'description' => pm_Locale::lmsg('domainPropertiesButtonDescription'),
                'icon' => pm_Context::getBaseUrl() . 'images/icon_64.png',
                'link' => pm_Context::getBaseUrl() . 'index.php/customer/list',
            ];
        }

        return $customButtons;
    }
}