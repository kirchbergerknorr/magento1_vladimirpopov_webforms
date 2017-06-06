<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Fieldsets_Display extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/fieldsets_display');
	}
	
	public function toOptionArray($default = false){
		$options = array(
			array('value' => 'on' , 'label' => Mage::helper('webforms')->__('On')),
			array('value' => 'off' , 'label' => Mage::helper('webforms')->__('Off')),
		);
		return $options;
	}
	
}
