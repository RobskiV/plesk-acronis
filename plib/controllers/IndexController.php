<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 11.03.16
 * Time: 16:25
 *
 * Contains the IndexController class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class IndexController
 *
 * Main Controller of the extension
 *
 * @category Controller
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class IndexController extends pm_Controller_Action
{
    /**
     * indexAction
     *
     * Redefines the default action
     */
    public function indexAction()
    {
        $this->_forward('index', 'Admin');
    }
}