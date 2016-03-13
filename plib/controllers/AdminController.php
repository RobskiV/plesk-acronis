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
        $this->view->pageTitle = pm_Locale::lmsg('adminViewSubscriptionTitle');
        $this->view->toolbar = $this->_getToolbar();
        $list = $this->_getSubscriptionList();
        // List object for pm_View_Helper_RenderList
        $this->view->list = $list;
    }


    public function togglesubscriptionAction()
    {
        $id = $this->_request->getParam('id');
        $oldStatus = (bool) $this->_request->getParam('oldStatus');
        $newStatus = !$oldStatus;

        $enabledSubscriptions = $this->_getEnabledSubscriptions();
        $enabledSubscriptions[$id] = $newStatus;
        $this->_setEnabledSubscriptions($enabledSubscriptions);

        $this->_helper->json(array('newStatus'=>$newStatus, 'enabledSubscriptions' => $enabledSubscriptions));
    }

    public function webspacelistDataAction()
    {
        $list = $this->_getSubscriptionList();
        // List object for pm_View_Helper_RenderList
        $this->_helper->json($list->fetchData());
    }

    private function _getSubscriptionList()
    {
        $data = $this->_getSubscriptionData();

        $list = new pm_View_List_Simple($this->view, $this->_request);

        $list->setData($data);
        $list->setColumns(array(
            "column-1" => array(
                "title" => pm_Locale::lmsg('adminListSubscriptionTitle'),
                "searchable" => true,
                "sortable" => true,
            ),
            "column-2" => array(
                "title" => pm_Locale::lmsg('adminListRestoreTitle'),
                "noEscape" => true,
                "searchable" => false,
                "sortable" => false,
                "noWrap" => true,
            )
        ));

        $list->setDataUrl(array('action' => 'webspacelist-data'));

        return $list;
    }

    private function _getSubscriptionData()
    {
        $enabledSubscriptions = $this->_getEnabledSubscriptions();
        $subscriptions = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getSubscriptions();
        $iconPath = pm_Context::getBaseUrl() . 'images/icon_64.png';
        $data = [];
        foreach ($subscriptions as $subscription) {
            if (isset($enabledSubscriptions[$subscription]) && $enabledSubscriptions[$subscription]) {
                $column2 = '<a class="toggle-restore-link" onclick="toggleRestoreSettings(event, this);" href="'.pm_Context::getActionUrl('admin', 'togglesubscription').'" data-id="'.$subscription.'" data-status="1"><i class="icon"><img src="'.pm_Context::getBaseUrl().'/images/ui-icons/on.png'.'"/></i></a> '.pm_Locale::lmsg('restoreEnabled');
            } else {
                $column2 = '<a class="toggle-restore-link" onclick="toggleRestoreSettings(event, this);" href="'.pm_Context::getActionUrl('admin', 'togglesubscription').'" data-id="'.$subscription.'" data-status="0"><i class="icon"><img src="'.pm_Context::getBaseUrl().'/images/ui-icons/off.png'.'"/></i></a> '.pm_Locale::lmsg('restoreDisabled');
            }

            $data[] = array(
                'column-1' => $subscription,
                'column-2' => $column2,
            );
        }

        return $data;
    }

    private function _getEnabledSubscriptions()
    {
        $enabledSubscriptions = pm_Settings::get('enabledSubscriptions');
        if ($enabledSubscriptions == null) {
            $enabledSubscriptions = [];
        } else {
            $enabledSubscriptions = json_decode($enabledSubscriptions, true);
        }

        return $enabledSubscriptions;
    }

    private function _setEnabledSubscriptions($enabledSubscriptions)
    {
        $enabledSubscriptions = json_encode($enabledSubscriptions);
        pm_Settings::set('enabledSubscriptions', $enabledSubscriptions);
    }

    private function _getToolbar()
    {
        return array(
            array(
                'icon' => pm_Context::getBaseUrl() . '/images/ui-icons/gear_32.png',
                'title' => pm_Locale::lmsg('adminViewConfigurationTitle'),
                'description' => pm_Locale::lmsg('adminViewConfigurationDesc'),
                'link' => pm_Context::getActionUrl('configuration', 'adminform'),
            ),
        );
    }
}