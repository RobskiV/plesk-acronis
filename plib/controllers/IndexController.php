<?php

/**
 * Class IndexController
 *
 * Controller for all general stuff that is neither configuration nor backup
 *
 * @category Controller
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
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