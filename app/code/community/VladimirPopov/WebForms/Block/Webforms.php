<?php

/**
 * @author        Vladimir Popov
 * @copyright     Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Block_Webforms
    extends Mage_Core_Block_Template
{
    protected $_webform;

    protected $_uid;

    /**
     * @return VladimirPopov_WebForms_Model_Webforms
     */
    public function getWebform()
    {
        return $this->_webform;
    }

    public function setWebform($webform)
    {
        $this->_webform = $webform;
        return $this;
    }

    protected function _toHtml()
    {
        if (Mage::registry('webforms_preview')) {
            $this->setTemplate(Mage::getStoreConfig('webforms/general/preview_template'));
        }

        if (!Mage::registry('webforms_preview'))
            $this->initForm();

        if (!$this->getWebform()->canAccess())
            $this->setTemplate('webforms/access_denied.phtml');

        $html = parent::_toHtml();

        return $html;
    }

    protected function initForm()
    {
        $this->clearRegistry();
        $show_success = false;
        $data = $this->getFormData();

        //get form data
        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($data['webform_id']);
        $this->setWebform($webform);

        //delete form temporary data
        if ($this->isAjax()) {
            Mage::getSingleton('core/session')->setData('webform_result_tmp_' . $webform->getId(), false);
        }

        //proccess texts
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 || Mage::helper('webforms')->getMageEdition() == 'EE') {
            $webform->setDescription(Mage::helper('cms')->getPageTemplateProcessor()->filter($webform->getDescription()));
            $webform->setSuccessText(Mage::helper('cms')->getPageTemplateProcessor()->filter($webform->getSuccessText()));
        }

        if (!Mage::registry('webform'))
            Mage::register('webform', $webform);

        if (intval($this->getData('results')) == 1)
            $this->getResults();

        if ($webform->getSurvey()) {
            $collection = Mage::getModel('webforms/results')->getCollection()->addFilter('webform_id', $data['webform_id']);

            if (Mage::helper('customer')->isLoggedIn()) {
                $collection->addFilter('customer_id', Mage::getSingleton('customer/session')->getCustomerId());
            } else {
                $session_validator = Mage::getSingleton('customer/session')->getData('_session_validator_data');
                $collection->addFilter('customer_ip', ip2long($session_validator['remote_addr']));
            }
            $count = $collection->count();

            if ($count > 0) {
                $show_success = true;
            }
        }

        if (Mage::getSingleton('core/session')->getWebformsSuccess() == $data['webform_id'] || $show_success) {
            Mage::register('show_success', true);
            Mage::getSingleton('core/session')->setWebformsSuccess();
            if (Mage::getSingleton('core/session')->getData('webform_result_' . $webform->getId())) {

                // apply custom variables
                $filter = Mage::helper('cms')->getPageTemplateProcessor();
                $webformObject = new Varien_Object();
                $webformObject->setData($webform->getData());
                $resultObject = Mage::getModel('webforms/results')->load((Mage::getSingleton('core/session')->getData('webform_result_' . $webform->getId())));
                $subject = $resultObject->getEmailSubject('customer');
                $filter->setVariables(array(
                    'webform_result' => $resultObject->toHtml('customer'),
                    'result' => $resultObject->getTemplateResultVar(),
                    'webform' => $webformObject,
                    'webform_subject' => $subject
                ));

                $webform->setData('success_text', $filter->filter($webform->getSuccessText()));
                Mage::getSingleton('core/session')->setData('webform_result_' . $webform->getId());
            }
        }

        if ($webform->getAccessEnable() && !Mage::helper('customer')->isLoggedIn() && !$this->getData('results')) {
            Mage::getSingleton('core/session')->addError($this->__('Please login to view the form.'));
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());

            $login_url = Mage::helper('customer')->getLoginUrl();
            $status = 301;

            if (Mage::getStoreConfig('webforms/general/login_redirect')) {
                $login_url = $this->getUrl(Mage::getStoreConfig('webforms/general/login_redirect'));

                if (strstr(Mage::getStoreConfig('webforms/general/login_redirect'), '://'))
                    $login_url = Mage::getStoreConfig('webforms/general/login_redirect');
            }
            Mage::app()->getFrontController()->getResponse()->setRedirect($login_url, $status);
        }

        Mage::register('fields_to_fieldsets', $webform->getFieldsToFieldsets());

        //use captcha
        Mage::register('use_captcha', $webform->useCaptcha());

        //proccess the result
        if ($this->getRequest()->getParam('submitWebform_' . $data['webform_id']) && $webform->getIsActive()) {
            //validate
            $result = $webform->savePostResult();

            if ($result) {
                Mage::getSingleton('core/session')->setWebformsSuccess($data['webform_id']);
                Mage::getSingleton('core/session')->setData('webform_result_' . $webform->getId(), $result->getId());
            }

            //redirect after successful submission
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = strtok($url, '?');

            if (!$result && $this->getTemplate() != 'webforms/legacy.phtml')
                Mage::app()->getFrontController()->getResponse()->setRedirect($url);

            if ($webform->getRedirectUrl()) {
                Mage::getSingleton('core/session')->setWebformsSuccess(false);
                if (strstr($webform->getRedirectUrl(), '://'))
                    $url = $webform->getRedirectUrl();
                else
                    $url = $this->getUrl($webform->getRedirectUrl());
            }
            Mage::register('redirect_url', $url);

            if ($result)
                Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }

        return $this;
    }

    // check that form is available for direct access
    public function isDirectAvailable()
    {
        $available = new Varien_Object();
        $status = true;
        if (Mage::registry('webforms_preview') && !Mage::getStoreConfig('webforms/general/preview_enabled'))
            $status = false;
        $available->setData('status', $status);

        Mage::dispatchEvent('webforms_direct_available', array
        (
            'available' => $available,
            'form_data' => $this->getFormData()
        ));

        return $available->getData('status');
    }

    public function getNotAvailableMessage()
    {
        $message = $this->__('Web-form is not active.');

        if (Mage::registry('webform')->getIsActive() && !$this->isDirectAvailable())
            $message = $this->__('Web-form is locked by configuration and can not be accessed directly.');
        return $message;
    }

    public function getFormData()
    {
        $data = $this->getRequest()->getParams();

        if (isset($data['id'])) {
            $data['webform_id'] = $data['id'];
        }

        if ($this->getData('webform_id')) {
            $data['webform_id'] = $this->getData('webform_id');
        }

        if (empty($data['webform_id'])) {
            $data['webform_id'] = Mage::getStoreConfig('webforms/contacts/webform');
        }

        return $data;
    }

    protected function _prepareLayout()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) <= 1.4)
            error_reporting(E_ERROR);

        Mage::helper('webforms')->addAssets($this->getLayout());

        parent::_prepareLayout();

        if (Mage::registry('webforms_preview')) {

            $this->initForm();

            if ($this->getLayout()->getBlock('head'))
                $this->getLayout()->getBlock('head')->setTitle($this->getWebform()->getName());
        }
    }

    public function getCaptcha()
    {
        return Mage::helper('webforms')->getCaptcha();
    }

    public function getEnctype()
    {
        if (Mage::registry('fields_to_fieldsets')) {
            foreach (Mage::registry('fields_to_fieldsets') as $fieldset) {
                foreach ($fieldset['fields'] as $field) {
                    if ($field->getType() == 'file' || $field->getType() == 'image') {
                        return 'multipart/form-data';
                    }
                }
            }
        }
        return 'application/x-www-form-urlencoded';
    }

    public function getResults()
    {
        $prev_url = '';
        $next_url = '';

        $data = $this->getData();

        $webform = Mage::registry('webform');

        //get results
        $page_size = 10;
        if (!empty($data["page_size"])) $page_size = $data["page_size"];
        $current_page = (int)$this->getRequest()->getParam('p');

        if (!$current_page)
            $current_page = 1;
        $results = Mage::getModel('webforms/results')->getCollection()->addFilter('webform_id', $webform->getId())->addFilter('approved', 1)->setPageSize($page_size)->setCurPage($current_page);
        $results->getSelect()->order('created_time desc');

        $last_page = $results->getLastPageNumber();

        $page_url = $this->getUrl(Mage::getSingleton('cms/page')->getData('identifier'));

        if ($current_page < $last_page) {
            $prev_url = $page_url . "?p=" . ($current_page + 1);
        }

        if ($current_page > 1) {
            $next_url = $page_url . "?p=" . ($current_page - 1);
        }

        Mage::register('prev_url', $prev_url);
        Mage::register('next_url', $next_url);
        Mage::register('current_page', $current_page);
        Mage::register('results', $results);
    }

    protected function clearRegistry()
    {
        Mage::unregister('webform');
        Mage::unregister('fields_to_fieldsets');
        Mage::unregister('prev_url');
        Mage::unregister('next_url');
        Mage::unregister('current_page');
        Mage::unregister('results');
        Mage::unregister('redirect_url');
        Mage::unregister('use_captcha');
        Mage::unregister('captcha_invalid');
    }

    public function isAjax()
    {
        return Mage::getStoreConfig('webforms/general/ajax');
    }

    public function getFormAction()
    {
        if ($this->isAjax()) {
            $secure = strstr(Mage::helper('core/url')->getCurrentUrl(), 'https://') ? true : false;
            // avoid trailing slash and missing slash issue
            return rtrim($this->getUrl('webforms/index/iframe', array('_secure' => $secure)), '/');
        }
        return Mage::helper('core/url')->getCurrentUrl();
    }

    public function getUid()
    {
        if (!$this->_uid) {
            $this->_uid = strtolower(Mage::helper('webforms')->randomAlphaNum());
        }
        return $this->_uid;
    }

    public function getFieldUid($field_id)
    {
        return $this->getUid() . $field_id;
    }
}
