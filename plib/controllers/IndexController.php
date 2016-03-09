<?php


class IndexController extends pm_Controller_Action
{
    public function init() {
        parent::init();

        $this->view->pageTitle = 'Acronis Backup Extension';
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $this->view->url = $this->_helper->url('index', 'configuration');
    }
}