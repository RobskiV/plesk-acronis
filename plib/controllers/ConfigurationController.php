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
    public function init() {
        parent::init();
        $this->view->pageTitle = "Acronis Backup: Settings";

    }

    /**
     * indexAction
     *
     * Description
     *
     *
     */
    public function indexAction()
    {
        $this->_forward('form');
    }

    /**
     * formAction
     *
     * Description
     *
     *
     */
    public function formAction() {

        try {
            $domain = pm_Session::getCurrentDomain();
        } catch (pm_Exception $e) {
            $this->_status->addMessage('error', pm_Locale::lmsg('errorNoClient'));
            $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
            exit;
        }

        $settings = pm_Settings::get('settings_'.$domain->getId(), null);

        if ($settings == null) {
            $settings = array(
                'host' => null,
                'username' => null,
                'password' => null,
            );
        } else {
            $settings = json_decode($settings, true);
        }


        $this->view->domainName = $domain->getName();

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
        )));

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
            'cancelLink' => pm_Context::getBaseUrl(),
        ));

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $settings['host'] = $form->getValue('host');
            $settings['username'] = $form->getValue('username');
            if ($form->getValue('password')) {
                $settings['password'] = $form->getValue('password');
            }

            $settings = json_encode($settings);

            pm_Settings::set('settings_'.$domain->getId(), $settings);

            //TODO: check configuration via Acronis Interface and treat the result

            $this->_status->addMessage('info', pm_Locale::lmsg('configSavedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
        }

        $this->view->form = $form;
    }
}