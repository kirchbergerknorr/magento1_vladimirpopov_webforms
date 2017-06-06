<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Contacts extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/contacts');
	}
	
	public function toOptionArray(){
        $options = array();
		$forms = Mage::getModel('webforms/webforms')->getCollection();
		$forms->getSelect()->order('name asc');
		foreach($forms as $form){
			$options[]= array(
				'label' => $form->getName(),
				'value' => $form->getId(),
			);
		}
		return $options;
	}
}
