<?php
class VladimirPopov_WebForms_Block_Adminhtml_Quickresponse
	extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_quickresponse';
		$this->_blockGroup = 'webforms';
		$this->_headerText = Mage::helper('webforms')->__('Manage Quick Responses');
		parent::__construct();
	}
	
}
