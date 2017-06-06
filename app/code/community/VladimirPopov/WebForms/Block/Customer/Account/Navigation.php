<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Customer_Account_Navigation
    extends Mage_Core_Block_Template
{
    protected $_links = array();
    protected $_path = 'webforms/customer/account';

    protected function _construct()
    {
        parent::_construct();

        if (!Mage::getSingleton('customer/session')->getCustomerId()) return;

        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $storeId = Mage::app()->getStore()->getId();

        $collection = Mage::getModel('webforms/webforms')->setStoreId($storeId)->getCollection();
        $links = array();
        foreach ($collection as $form) {
            if (
            (($form->getAccessEnable() && in_array($groupId, $form->getAccessGroups()) || !$form->getAccessEnable())
                && $form->getIsActive()
                && $form->getDashboardEnable()
                && in_array($groupId, $form->getDashboardGroups()))
            ) {
                $active = false;
                if(Mage::app()->getRequest()->getParam('webform_id') == $form->getId()) $active =true;
                $links[] = new Varien_Object(array('label' => $form->getName(), 'url' => $this->getFormUrl($form), 'active' => $active));
            }
        }
        $this->_links = $links;
    }

    public function getLinks()
    {
        return $this->_links;
    }

    public function getFormUrl($form)
    {
        return Mage::getUrl($this->_path, array('webform_id' => $form->getId()));
    }

}