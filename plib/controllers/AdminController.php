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
        $list = $this->_getSubscriptionList();
        // List object for pm_View_Helper_RenderList
        $this->view->list = $list;
    }

    public function webspacelistDataAction()
    {
        $list = $this->_getSubscriptionList();
        // List object for pm_View_Helper_RenderList
        $this->_helper->json($list->fetchData());
    }

    private function _getSubscriptionList()
    {
        $enabledSubscriptions = $this->_getEnabledSubscriptions();
        $subscriptions = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getSubscriptions();
        $iconPath = pm_Context::getBaseUrl() . 'images/icon_64.png';
        $data = [];
        foreach ($subscriptions as $subscription) {
            $data[] = array(
                'column-1' => $subscription,
                'column-2' => isset($subscription, $enabledSubscriptions),
            );
        }

        $list = new pm_View_List_Simple($this->view, $this->_request);

        $list->setData($data);
        $list->setColumns(array(
            "column-1" => array(
                "title" => pm_Locale::lmsg('adminListSubscriptionTitle'),
                "noEscape" => true,
                "searchable" => true,
                "sortable" => true,
            ),
            "column-2" => array(
                "title" => pm_Locale::lmsg('adminListRestoreTitle'),
                "noEscape" => true,
                "searchable" => false,
                "sortable" => false,
            )
        ));

        $list->setDataUrl(array('action' => 'webspacelist-data'));

        return $list;
    }

    private function _getEnabledSubscriptions()
    {
        $enabledSubscriptions = pm_Settings::get('enabledSubscriptions');
        if ($enabledSubscriptions == null) {
            $enabledSubscriptions = [];
        } else {
            $enabledSubscriptions = json_decode($enabledSubscriptions);
        }

        return $enabledSubscriptions;
    }
}