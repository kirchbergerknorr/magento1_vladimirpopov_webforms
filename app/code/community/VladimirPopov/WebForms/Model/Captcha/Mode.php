<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Captcha_Mode extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/captcha_mode');
	}
	
	public function toOptionArray($default = false){
		$options = array(
			array('value' => 'auto' , 'label' => Mage::helper('webforms')->__('Auto (hidden for logged in customers)')),
			array('value' => 'always' , 'label' => Mage::helper('webforms')->__('Always on')),
			array('value' => 'off' , 'label' => Mage::helper('webforms')->__('Off')),
		);
		if($default){
			$options = array_merge(array(
				array('value' => 'default' , 'label' => Mage::helper('webforms')->__('Default')),
			),$options);
		}
		return $options;
	}
	
}
