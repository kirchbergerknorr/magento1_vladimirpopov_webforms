<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_WebForms_Edit_Tabs
	extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('webforms_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('webforms')->__('Form Information'));
	}
	
	protected function _beforeToHtml()
	{
		
		$this->addTab('form_information',array(
			'label' => Mage::helper('webforms')->__('Information'),
			'title' => Mage::helper('webforms')->__('Information'),
			'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_information')->toHtml(),
		));
		
		$this->addTab('form_settings',array(
			'label' => Mage::helper('webforms')->__('General Settings'),
			'title' => Mage::helper('webforms')->__('General Settings'),
			'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_settings')->toHtml(),
		));

        $this->addTab('form_email',array(
            'label' => Mage::helper('webforms')->__('E-mail Settings'),
            'title' => Mage::helper('webforms')->__('E-mail Settings'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_email')->toHtml(),
        ));

        $this->addTab('form_access',array(
            'label' => Mage::helper('webforms')->__('Access Settings'),
            'title' => Mage::helper('webforms')->__('Access Settings'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_access')->toHtml(),
        ));

        $this->addTab('form_print',array(
            'label' => Mage::helper('webforms')->__('Print Settings'),
            'title' => Mage::helper('webforms')->__('Print Settings'),
            'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_print')->toHtml(),
        ));

        if(Mage::registry('webforms_data') && Mage::registry('webforms_data')->getId() ){
			$this->addTab('form_fieldsets',array(
				'label' => Mage::helper('webforms')->__('Field Sets'),
				'title' => Mage::helper('webforms')->__('Field Sets'),
				'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_fieldsets')->toHtml(),
			));

			$this->addTab('form_fields',array(
				'label' => Mage::helper('webforms')->__('Fields'),
				'title' => Mage::helper('webforms')->__('Fields'),
				'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_fields')->toHtml(),
			));

            if(count(Mage::registry('webforms_data')->getLogic()))
                $this->addTab('logic', array(
                    'label' => Mage::helper('webforms')->__('Logic'),
                    'title' => Mage::helper('webforms')->__('Logic'),
                    'content' => $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_logic')->toHtml(),
                ));
		}
		
		if($this->getRequest()->getParam('tab')){
			$this->setActiveTab($this->getRequest()->getParam('tab'));
		}
		
		Mage::dispatchEvent('webforms_adminhtml_webforms_edit_tabs_before_to_html',array('tabs'=>$this));

		return parent::_beforeToHtml();
	}
}
