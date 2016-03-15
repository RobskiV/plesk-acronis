<?php
/**
 * This File is a part of the plesk-acronis extension (https://github.com/StratoAG/plesk-acronis)
 *
 * Created by Vincent Fahrenholz <fahrenholz@strato-rz.de>
 *
 * Date: 11.03.16
 * Time: 16:25
 *
 * Contains the ConfigurationController class
 *
 * @licence http://www.apache.org/licenses/LICENSE-2.0 Apache Licence v. 2.0
 */

/**
 * Class ConfigurationController
 *
 * Controller holding all actions related to the saving of basic settings
 *
 * @category Controller
 * @author   Vincent Fahrenholz <fahrenholz@strato.de>
 * @version  Release: 1.0.0
 */
class ConfigurationController extends pm_Controller_Action
{

    /**
     * init
     *
     * Override to fix the page title once and for all for all actions
     */
    public function init()
    {
        parent::init();
        $this->view->pageTitle = pm_Locale::lmsg('configurationPageTitle');
    }


    /**
     * indexAction
     *
     * Main action of the controller. forwards to accountAction()
     */
    public function indexAction()
    {
        $this->_forward('account');
    }

    /**
     * accountAction
     *
     * Displays the account settings view and checks and saves the data the user puts into it
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
        $accountForm = $this->getAccountForm($settings);
        $this->treatAccountForm($accountForm, $settings);

        if ($settings['host'] != null) {
            $this->view->tabs = $this->getTabs();
        } else {
            $this->view->tabs = null;
        }

        $this->view->accountForm = $accountForm;
    }

    /**
     * backupAction
     *
     * Displays the backup settings view and checks and saves the data the user puts into it
     */
    public function backupAction()
    {
        $accountSettings = Modules_AcronisBackup_settings_SettingsHelper::getAccountSettings();

        if ($accountSettings['host'] == null) {
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'account')));
        }
        $settings = Modules_AcronisBackup_settings_SettingsHelper::getBackupSettings();
        $form = $this->getBackupForm($settings);
        $this->treatBackupForm($form);

        $this->view->backupForm = $form;
        $this->view->tabs = $this->getTabs();
    }

    /**
     * getAccountForm
     *
     * Generates the form used for the accountAction
     *
     * @param array $settings Account Settings already provided by the administrator
     *
     * @return pm_Form_Simple
     */
    private function getAccountForm($settings)
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

    /**
     * getBackupForm
     *
     * Generates the form used by the backupAction
     *
     * @param array $settings Backup-Settings already given by the user
     *
     * @return pm_Form_Simple
     * @throws Exception When the API-Call to Acronis produces some error
     */
    private function getBackupForm($settings)
    {
        $form = new pm_Form_Simple();

        $form->addElement('password', 'encryptionPassword', array(
            'label' => pm_Locale::lmsg('encryptionPasswordLabel'),
            'value' => $settings['encryptionPassword'],
            'validators' => array(
                array('StringLength', true, array(5, 255))
            ),
        ));

        $plans = [];
        $backupPlans = Modules_AcronisBackup_backups_BackupHelper::getBackupPlans();
        foreach ($backupPlans as $plan) {
            $plans[$plan] = $plan;
        }

        $form->addElement('select', 'backupPlan', array(
            'label' => pm_Locale::lmsg('backupPlanLabel'),
            'value' => $settings['backupPlan'],
            'multiOptions' => $plans,
            'validators' => array(
                array('NotEmpty', true),
            ),
        ));

        $form->addControlButtons(array(
            'cancelLink' => pm_Context::getActionUrl('admin', 'index'),
        ));

        return $form;
    }

    /**
     * treatAccountForm
     *
     * Validates and saves the data provided by the user via the Account-Form
     *
     * @param pm_Form_Simple $form     Account-Form
     * @param array          $settings Account-Settings already provided by the user
     */
    private function treatAccountForm($form, $settings)
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
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'account')));
        }
    }

    /**
     * treatBackupForm
     *
     * Validates and saves the data provided by the user via the backup-form
     *
     * @param pm_Form_Simple $form     Backup-Form
     * @param array          $settings Backup-settings already provided by the user
     */
    private function treatBackupForm($form, $settings)
    {
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            if ($form->getValue('encryptionPassword')) {
                $settings['encryptionPassword'] = $form->getValue('encryptionPassword');
            }
            $settings['backupPlan'] = $form->getValue('backupPlan');

            $settings = json_encode($settings);
            pm_Settings::set('backupSettings', $settings);

            $this->_status->addMessage('info', pm_Locale::lmsg('backupConfigSavedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getActionUrl('configuration', 'backup')));
        }
    }

    /**
     * getTabs
     *
     * Generate the tabs displayed in the form
     *
     * @return array
     */
    private function getTabs()
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