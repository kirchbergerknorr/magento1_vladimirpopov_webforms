<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fieldsets_Edit
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
	
	protected function _prepareLayout(){

		parent::_prepareLayout();

	}
	
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'webforms';
		$this->_controller = 'adminhtml_fieldsets';

		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => "$('saveandcontinue').value = true; editForm.submit()",
			'class'     => 'save',
		), -100);
	}
	
	public function getSaveUrl()
	{
		return $this->getUrl('*/webforms_webforms/save',array('webform_id'=>Mage::registry('webforms_data')->getId()));
	}
	
	public function getBackUrl(){
		return $this->getUrl('*/webforms_webforms/edit',array('id'=>Mage::registry('webforms_data')->getId()));
	}

	public function getHeaderText()
	{
		if(!is_null(Mage::registry('fieldsets_data')->getId())) {
			return Mage::helper('webforms')->__("Edit Field Set '%s'", $this->htmlEscape(Mage::registry('fieldsets_data')->getName()));
		} else {
			return Mage::helper('webforms')->__('New Field Set');
		}
	}

	public function getFormHtml()
	{
		$html = parent::getFormHtml();
		// add store switcher
		if (!Mage::app()->isSingleStoreMode() && $this->getRequest()->getParam('id')) {
			$store_switcher = $this->getLayout()->createBlock('adminhtml/store_switcher','store_switcher');
			$store_switcher->setDefaultStoreName($this->__('Default Values'));
			
			$html = $store_switcher->toHtml().$html;
			
		}
		return $html;
	}
}  
