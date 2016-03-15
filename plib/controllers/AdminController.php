<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 13.03.16
 * Time: 11:51
 *
 * Contains the AdminController class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class AdminController
 *
 * Controller for all actions relevant to Admin functionalities other than basic settings (@see ConfigurationController)
 *
 * @category Controller
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class AdminController extends pm_Controller_Action
{
    /**
     * indexAction
     *
     * Main action of the controller. Forwards to listAction
     */
    public function indexAction()
    {
        $this->_forward('list');
    }

    /**
     * listAction
     *
     * Manages the availability of the restore functionality for the subscriptions
     */
    public function listAction()
    {
        Modules_AcronisBackup_settings_SettingsHelper::getIpAddresses();
        $this->view->pageTitle = pm_Locale::lmsg('adminViewSubscriptionTitle');
        $this->view->authorizationMode = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getAuthorizationMode();
        $this->view->authorizationModeUrl = pm_Context::getActionUrl('admin', 'toggleauthorizationmode');
        $this->view->toolbar = $this->getToolbar();
        if ($this->view->authorizationMode == 'extended') {
            $list = $this->getSubscriptionList();
            // List object for pm_View_Helper_RenderList
            $this->view->list = $list;
        }
    }


    /**
     * togglesubscriptionAction
     *
     * Enables or disables the given subscriptions sestore functionality
     */
    public function togglesubscriptionAction()
    {
        $subscriptionId = $this->_request->getParam('id');
        $oldStatus = (bool) $this->_request->getParam('oldStatus');
        $newStatus = !$oldStatus;

        $enabledSubscriptions = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getEnabledSubscriptions();
        $enabledSubscriptions[$subscriptionId] = $newStatus;
        Modules_AcronisBackup_subscriptions_SubscriptionHelper::setEnabledSubscriptions($enabledSubscriptions);

        $this->_helper->json(array('newStatus'=>$newStatus));
    }

    /**
     * toggleauthorizationmodeAction
     *
     * Toggles the Mode used to determine the presence of the subscriptions backup possibility between simple
     * (all subscriptions enabled) and extended (The administrator has to specify the enabled subscriptions)
     */
    public function toggleauthorizationmodeAction()
    {
        $value = $this->_request->getParam('value');
        Modules_AcronisBackup_subscriptions_SubscriptionHelper::setAuthorizationMode($value);

        $this->_helper->json(array("value"=>$value));
    }

    /**
     * listDataAction
     *
     * Sends all informations needed to refresh the list displayed during the listAction
     */
    public function listDataAction()
    {
        $this->view->authorizationMode = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getAuthorizationMode();

        if ($this->view->authorizationMode == 'extended') {
            $list = $this->getSubscriptionList();
            // List object for pm_View_Helper_RenderList
            $this->_helper->json($list->fetchData());
        }
    }

    /**
     * getSubscriptionList
     *
     * Generates a Plesk List
     *
     * @return pm_View_List_Simple
     */
    private function getSubscriptionList()
    {
        $data = $this->getSubscriptionData();

        $list = new pm_View_List_Simple($this->view, $this->_request);

        $list->setData($data);
        $columns = [
            "column-1" => [
                "title" => pm_Locale::lmsg('adminListSubscriptionTitle'),
                "searchable" => true,
                "sortable" => true,
            ],
            "column-2" => [
                "title" => pm_Locale::lmsg('adminListRestoreTitle'),
                "noEscape" => true,
                "searchable" => false,
                "sortable" => false,
                "noWrap" => true,
            ]
        ];

        $list->setColumns($columns);

        $list->setDataUrl(array('action' => 'list-data'));

        return $list;
    }

    /**
     * getSubscriptionData
     *
     * Returns the data displayed in the subscription list, already organized in columns as needed by pm_View_List_Simple
     *
     * @return array
     */
    private function getSubscriptionData()
    {
        $enabledSubscriptions = Modules_AcronisBackup_subscriptions_SubscriptionHelper::getEnabledSubscriptions();
        $subscriptions = Modules_AcronisBackup_Subscriptions_SubscriptionHelper::getSubscriptions();
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

    /**
     * getToolbar
     *
     * Generates a toolbar which is renderable in the view
     *
     * @return array
     */
    private function getToolbar()
    {
        return array(
            array(
                'icon' => pm_Context::getBaseUrl() . '/images/ui-icons/gear_32.png',
                'title' => pm_Locale::lmsg('adminViewConfigurationTitle'),
                'description' => pm_Locale::lmsg('adminViewConfigurationDesc'),
                'link' => pm_Context::getActionUrl('configuration', 'account'),
            ),
        );
    }
}