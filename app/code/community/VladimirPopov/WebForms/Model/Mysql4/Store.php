<?php
class VladimirPopov_WebForms_Model_Mysql4_Store
	extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('webforms/store','id');
	}
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{
		$object->setStoreData(
			unserialize($object->getStoreData())
		);
		
		parent::_afterLoad($object);
	}
}
