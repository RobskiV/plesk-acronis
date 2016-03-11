<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 11.03.16
 * Time: 16:25
 *
 * Short Info
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

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
        $this->_forward('dashboard');
    }

    public function dashboardAction()
    {
        $this->view->tools = $this->_getToolbar();
    }

    private  function _getToolbar()
    {
        return array(
            array(
                'icon' => pm_Context::getBaseUrl() . '/images/ui-icons/clock_32.png',
                'title' => pm_Locale::lmsg('dashboardNav'),
                'description' => pm_Locale::lmsg('dashboardDescription'),
                'link' => pm_Context::getActionUrl('index', 'index'),
            ),
            array(
                'icon' => pm_Context::getBaseUrl() . '/images/ui-icons/gear_32.png',
                'title' => pm_Locale::lmsg('configurationNav'),
                'description' => pm_Locale::lmsg('configurationDescription'),
                'link' => pm_Context::getActionUrl('configuration', 'form'),
            ),
        );
    }
}