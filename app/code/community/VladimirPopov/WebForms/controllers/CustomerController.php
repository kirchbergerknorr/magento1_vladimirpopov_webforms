<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_CustomerController
    extends Mage_Core_Controller_Front_Action
{
    public function _init()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->addError($this->__('Please login to view the form.'));
            Mage::getSingleton('customer/session')->authenticate($this);
        }
    }

    public function accountAction()
    {
        $this->_init();

        $webformId = $this->getRequest()->getParam('webform_id');
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $webform = Mage::getModel('webforms/webforms')->setStoreId(Mage::app()->getStore()->getId())->load($webformId);
        if (!$webform->getIsActive() || !$webform->getDashboardEnable() || !in_array($groupId, $webform->getDashboardGroups())) $this->_redirect('customer/account');
        Mage::register('webform', $webform);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($webform->getName());
        $this->getLayout()->getBlock('webforms_customer_account_form')->setData('webform_id', $webformId)->setData('scroll_to', 1);

        $this->renderLayout();
    }

    public function resultAction()
    {
        $this->_init();

        $resultId = Mage::app()->getRequest()->getParam('id');
        $result = Mage::getModel('webforms/results')->load($resultId);
        if ($result->getCustomerId() != Mage::getSingleton('customer/session')->getId()) $this->_redirect('customer/account');
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $webform = Mage::getModel('webforms/webforms')->setStoreId($result->getStoreId())->load($result->getWebformId());
        if (!$webform->getIsActive() || !$webform->getDashboardEnable() || !in_array($groupId, $webform->getDashboardGroups())) $this->_redirect('customer/account');
        Mage::register('result', $result);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($result->getEmailSubject());

        $this->renderLayout();
    }
}