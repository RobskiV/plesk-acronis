<?php

class ConfigurationController extends pm_Controller_Action
{
    public function init() {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('form');
    }

    public function formAction() {
        $form = new pm_Form_Simple();

        $form->addElement('text', 'acronisHost', array(
            'label' => pm_Locale::lmsg('acronisHostLabel'),
            'value' => pm_Settings::get('acronisHost'),
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

        $form->addElement('text', 'acronisLogin', array(
            'label' => pm_Locale::lmsg('acronisLoginLabel'),
            'value' => pm_Settings::get('acronisLogin'),
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
            ),
        ));
        $form->addElement('password', 'acronisPassword', array(
            'label' => pm_Locale::lmsg('acronisPasswordLabel'),
            'value' => '',
            'description' => 'Password: ' . pm_Settings::get('acronisPassword'),
            'validators' => array(
                array('StringLength', true, array(5, 255)),
            ),
        ));

        $form->addControlButtons(array(
            'cancelLink' => pm_Context::getModulesListUrl(),
        ));

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            pm_Settings::set('acronisHost', $form->getValue('acronisHost'));
            pm_Settings::set('acronisLogin', $form->getValue('acronisLogin'));
            if ($form->getValue('acronisPassword')) {
                pm_Settings::set('acronisPassword', $form->getValue('acronisPassword'));
            }

            //TODO: check configuration via Acronis Interface and treat the result

            $this->_status->addMessage('info', pm_Locale::lmsg('configSavedAlert'));
            $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
        }

        $this->view->form = $form;
    }
}