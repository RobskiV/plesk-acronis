<?php

/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 13.03.16
 * Time: 11:51
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

class AdminController extends pm_Controller_Action
{
    /**
     * indexAction
     *
     * Main action of the controller. Forwards to webspaceListAction
     */
    public function indexAction()
    {
        $this->_forward('webspacelist');
    }

    /**
     * webspaceListAction
     *
     * Action to display a list of webspaces and to enable these webspaces to perform a restore of their webspace
     */
    public function webspacelistAction()
    {
        $this->view->subscriptions = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getSubscriptions();
        $this->view->version = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getPleskVersion();
    }
}