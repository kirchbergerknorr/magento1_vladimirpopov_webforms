<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Quickresponse
	extends Mage_Core_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('webforms/quickresponse');
	}	

	public function toOptionArray(){
		$collection = $this->getCollection()->addOrder('title','asc');
		$option_array = array();
		foreach($collection as $element)
			$option_array[]= array('value'=>$element->getId(), 'label' => $element->getTitle());
		return $option_array;
	}
	
}
