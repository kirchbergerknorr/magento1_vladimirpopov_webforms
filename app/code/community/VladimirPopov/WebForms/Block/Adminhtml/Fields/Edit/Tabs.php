<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fields_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('webforms_fields_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('webforms')->__('Field Information'));
    }

    protected function _beforeToHtml()
    {

        $this->addTab('form_information', array(
            'label' => Mage::helper('webforms')->__('Information'),
            'title' => Mage::helper('webforms')->__('Information'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_fields_edit_tab_information')->toHtml(),
        ));

        $this->addTab('form_design', array(
            'label' => Mage::helper('webforms')->__('Design'),
            'title' => Mage::helper('webforms')->__('Design'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_fields_edit_tab_design')->toHtml(),
        ));

        $this->addTab('form_additional', array(
            'label' => Mage::helper('webforms')->__('Validation'),
            'title' => Mage::helper('webforms')->__('Validation'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_fields_edit_tab_validation')->toHtml(),
        ));

        if (count(Mage::registry('field')->getLogic()))
            $this->addTab('logic', array(
                'label' => Mage::helper('webforms')->__('Logic'),
                'title' => Mage::helper('webforms')->__('Logic'),
                'content' => $this->getLayout()->createBlock('webforms/adminhtml_fields_edit_tab_logic')->toHtml(),
            ));

        if ($this->getRequest()->getParam('tab')) {
            $this->setActiveTab($this->getRequest()->getParam('tab'));
        }

        Mage::dispatchEvent('webforms_adminhtml_fields_edit_tabs_before_to_html', array('tabs' => $this));

        return parent::_beforeToHtml();
    }
}
