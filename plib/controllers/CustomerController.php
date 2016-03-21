<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 13.03.16
 * Time: 17:57
 *
 * Contains the CustomerController class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class CustomerController
 *
 * Controller holding all actions relevant to interfaces located under the subscription-panel and not doing any restore
 * (@see RestoreController)
 *
 * @category Controller
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class CustomerController extends pm_Controller_Action
{
    /**
     * @var null|pm_Domain Current Domain of the user
     */
    private $domain = null;

    /**
     * init
     *
     * Override to initialize the current user domain once and for all
     */
    public function init()
    {
        parent::init();
        $this->domain = pm_Session::getCurrentDomain();
    }

    /**
     * indexAction
     *
     * Main action of the controller. Forwards to listAction
     */
    public function indexAction()
    {
        $this->_forward('listAction');
    }

    /**
     * listAction
     *
     * Action used to provide a list of restore-points to the customer
     */
    public function listAction()
    {
        $this->view->pageTitle = pm_Locale::lmsg('customerListHeading') . $this->domain->getName();

        $list = $this->getRecoveryPointList();
        $list->setDataUrl(array('action' => 'listdata'));

        $this->view->list = $list;
    }

    /**
     * listDataAction
     *
     * Action to retrieve only the list data
     *
     * @return array
     */
    public function listdataAction()
    {
        $list = $this->getRecoveryPointList();

        $this->_helper->json($list->fetchData());
    }

    /**
     * getRecoveryPointList
     *
     * Generates the list used for listAction()
     *
     * @return pm_View_List_Simple
     */
    private function getRecoveryPointList()
    {
        $data = $this->getRestorepoints();

        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns($this->getColumns());

        return $list;
    }

    /**
     * getColumns
     *
     * Generates the columns needed for listAction()
     *
     * @return array
     */
    private function getColumns()
    {
        $columns = [
            "column-1" => [
                "title" => pm_Locale::lmsg('customerListDatetimeTitle'),
                "searchable" => true,
                "sortable" => true,
                "noEscape" => true,
            ],
            "column-2" => [
                "title" => pm_Locale::lmsg('customerListActionTitle'),
                "noEscape" => true,
                "searchable" => false,
                "sortable" => false,
                "noWrap" => true,
            ]
        ];

        return $columns;
    }

    /**
     * itemAction
     *
     * Action used to display details and browse one webspace-recovery-point
     */
    public function itemAction()
    {
        $this->view->pageTitle = pm_Locale::lmsg('customerItemHeading') . $this->domain->getName();

        // @todo fetch the backup details
        $item = new stdClass();
        $item->id = 123123;

        $this->view->item = $item;
    }

    /**
     * getRestorepoints
     *
     * Gets all recovery points the user can browse
     *
     * @return array
     * @throws Exception
     */
    private function getRestorepoints()
    {
        $recoveryPoints = Modules_AcronisBackup_backups_BackupHelper::getRecoveryPoints();

        $data = [];
        foreach ($recoveryPoints as $recoveryPoint) {
            $column2 = '<a class="btn" href="'.pm_Context::getActionUrl('customer', 'item').'/id/' . $recoveryPoint['ItemSliceName'] . '" >'.pm_Locale::lmsg('recoveryPointDetailsButton').'</a>'
                .'<a onclick="pleaseConfirm(event,\''.pm_Locale::lmsg('confirmDialog').'\')" class="btn" href="'.pm_Context::getActionUrl('restore', 'webspace').'/id/' . $recoveryPoint['ItemSliceName'].'/resource/'.base64_encode($recoveryPoint['ItemSliceFile']).'">'.pm_Locale::lmsg('restoreWebspaceAction').'</a>';
            $date = new DateTime($recoveryPoint['ItemSliceTime']);
            $data[] = array(
                'column-1' => date("M d, Y G:H", $date->format('U')),
                'column-2' => $column2,
            );
        }

        return $data;
    }
}