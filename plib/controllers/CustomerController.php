<?php

/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 13.03.16
 * Time: 17:57
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */
class CustomerController extends pm_Controller_Action
{
    private $domain = null;

    public function init()
    {
        parent::init();
        $this->domain = pm_Session::getCurrentDomain();
    }

    public function indexAction()
    {
        $this->_forward('listAction');
    }

    public function listAction()
    {
        $this->view->pageTitle = pm_Locale::lmsg('customerListHeading') . $this->domain->getName();

        $data = $this->_getRestorepoints();

        $list = new pm_View_List_Simple($this->view, $this->_request);

        $list->setData($data);
        $list->setColumns(array(
            "column-1" => array(
                "title" => pm_Locale::lmsg('customerListDatetimeTitle'),
                "searchable" => true,
                "sortable" => true,
            ),
            "column-2" => array(
                "title" => pm_Locale::lmsg('customerListDetailTitle'),
                "noEscape" => true,
                "searchable" => false,
                "sortable" => false,
                "noWrap" => true,
            )
        ));

        $list->setDataUrl(array('action' => 'list'));

        $this->view->list = $list;
    }

    public function itemAction()
    {
        $this->view->pageTitle = pm_Locale::lmsg('customerItemHeading') . $this->domain->getName();

        // @todo fetch the backup details
        $domainId = $this->domain->getId();
        $item = new stdClass();
        $item->id = 123123;

        $this->view->item = $item;
    }

    public function restoreAction()
    {
        $this->view->pageTitle = pm_Locale::lmsg('customerRestoreHeading') . $this->domain->getName();

        // @todo start acutal restore
        $domainId = $this->domain->getId();
    }

    private function _getRestorepoints()
    {
        // @todo fetch restorepoints from API
        $domainId = $this->domain->getId();
        $restorepoints = [];

        $data = [];
        foreach ($restorepoints as $restorepoint) {
            $data[] = array(
                'column-1' => date("M d, Y G:H", $restorepoint->timestamp),
                'column-2' => '<a href="'.pm_Context::getActionUrl('customer', 'item').'/' . $restorepoint->id . '" ><i class="icon"><img src="'.pm_Context::getBaseUrl().'/images/ui-icons/right_32.png'.'"/></i></a>',
            );
        }

        return $data;
    }
}