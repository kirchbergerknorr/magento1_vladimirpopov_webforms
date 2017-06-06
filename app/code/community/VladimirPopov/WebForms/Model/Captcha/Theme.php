<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Captcha_Theme extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/captcha_theme');
	}
	
	public function toOptionArray(){
		return array(
			array('value' => 'standard' , 'label' => Mage::helper('webforms')->__('Standard')),
			array('value' => 'dark' , 'label' => Mage::helper('webforms')->__('Dark')),
		);
	}
	
}
