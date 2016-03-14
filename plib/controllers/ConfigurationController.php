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


class ConfigurationController extends pm_Controller_Action
{
    /**
     * init
     *
     * Description
     *
     *
     */
    public function init()
    {
        parent::init();
        $this->view->pageTitle = pm_Locale::lmsg('configurationPageTitle');
    }

    /**
     * indexAction
     *
     * Description
     */
    public function indexAction()
    {
        $this->_forward('account');
    }

    /**
     * formAction
     *
     * Description
     *
     *
     */
    public function accountAction()
    {
        try {
            $domain = pm_Session::getCurrentDomain();
        } catch (pm_Exception $e) {
            $this->_status->addMessage('error', pm_Locale::lmsg('errorNoClient'));
            $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
        }

        $settings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();


        $this->view->domainName = $domain->getName();
        $accountForm = $this->_getAccountForm($settings);
        $this->_treatAccountForm($accountForm, $settings);

        if ($settings['host'] != null) {
            $this->view->tabs = $this->_getTabs();
        } else {
            $this->view->tabs = null;
        }

        $this->view->accountForm = $accountForm;
    }

    public function backupAction()
    {
        $accountSettings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();

        if ($accountSettings['host'] == null) {
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'account')));
        }
        $settings = Modules_AcronisBackup_settings_SettingsHelper::getBackupSettings();
        $form = $this->_getBackupForm($settings);
        $this->_treatBackupForm($form);

        $this->view->backupForm = $form;
        $this->view->tabs = $this->_getTabs();
    }

    private function _getAccountForm($settings)
    {
        $form = new pm_Form_Simple();

        $form->addElement('text', 'host', array(
            'label' => pm_Locale::lmsg('hostLabel'),
            'value' => $settings['host'],
            'required' => true,
            'validators' => array(
                array(
                    'NotEmpty',
                    array(
                        'Callback',
                        true,
                        array(
                            'callback' => function($value) {
                                return Zend_Uri::check($value);
                            }
                        ),
                        'messages' => array(
                            Zend_Validate_Callback::INVALID_VALUE => pm_Locale::lmsg('invalidUrlAlert'),
                        ),
                    ),
                ),
            )
        ));

        $form->addElement('text', 'username', array(
            'label' => pm_Locale::lmsg('usernameLabel'),
            'value' => $settings['username'],
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
            ),
        ));

        $form->addElement('password', 'password', array(
            'label' => pm_Locale::lmsg('passwordLabel'),
            'value' => $settings['password'],
            'validators' => array(
                array('StringLength', true, array(5, 255)),
            ),
        ));

        $form->addControlButtons(array(
            'cancelLink' => pm_Context::getActionUrl('admin', 'index'),
        ));

        return $form;
    }

    private function _getBackupForm($settings)
    {
        $form = new pm_Form_Simple();

        $form->addElement('password', 'encryptionPassword', array(
            'label' => pm_Locale::lmsg('encryptionPasswordLabel'),
            'value' => $settings['encryptionPassword'],
            'validators' => array(
                array('StringLength', true, array(5, 255))
            ),
        ));

        $form->addElement('select', 'backupPlan', array(
            'label' => pm_Locale::lmsg('backupPlanLabel'),
            'value' => $settings['backupPlan'],
            'multiOptions' => array(
                'id1' => 'backup plan 1',
                'id2' => 'backup plan 3',
                'id3' => 'backup plan 3',
                'id4' => 'backup plan 4',
            ),
            'validators' => array(
                array('NotEmpty', true),
            ),
        ));

        $form->addControlButtons(array(
            'cancelLink' => pm_Context::getActionUrl('admin', 'index'),
        ));

        return $form;
    }

    private function _treatAccountForm($form, $settings)
    {
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $settings['host'] = $form->getValue('host');
            $settings['username'] = $form->getValue('username');
            if ($form->getValue('password')) {
                $settings['password'] = $form->getValue('password');
            }

            // use credentials to simulate a connection and verify them
            try {
                $request = new Modules_AcronisBackup_webapi_Request($settings['host'], $settings['username'], $settings['password']);
                $request->request('GET', '/api/ams/session');
            } catch (RuntimeException $e) {
                $this->_status->addMessage('error', pm_Locale::lmsg('configFailedAlert'));
                $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'account')));
            }

            Modules_AcronisBackup_settings_SettingsHelper::setAccountSettings($settings);

            $this->_status->addMessage('info', pm_Locale::lmsg('configSavedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'backup')));
        }
    }

    private function _treatBackupForm($form, $backupSettings)
    {
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            if ($form->getValue('encryptionPassword')) {
                $backupSettings['encryptionPassword'] = $form->getValue('encryptionPassword');
            }
            $backupSettings['backupPlan'] = $form->getValue('backupPlan');

            $backupSettings = json_encode($backupSettings);
            pm_Settings::set('backupSettings', $backupSettings);

            $this->_status->addMessage('info', pm_Locale::lmsg('backupConfigSavedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'backup')));
        }
    }

    private function _getTabs()
    {
        $tabs = array(
            array(
                'title' => pm_Locale::lmsg('accountSettingsTabTitle'),
                'action' => 'account'
            ),
            array(
                'title' => pm_Locale::lmsg('backupSettingsTabTitle'),
                'action' => 'backup'
            ),
        );

        return $tabs;
    }
}