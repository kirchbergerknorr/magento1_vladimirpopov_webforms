<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_CustomerController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _init(){
        $customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
        Mage::register('current_customer',$customer);
    }

    public function resultsAction(){
        $this->_init();


        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_customer_tab_results')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms');
    }
}