<?php
class VladimirPopov_WebForms_Model_Mysql4_Abstract
	extends Mage_Core_Model_Mysql4_Abstract
{
	protected $_store_id;
	
	public function _construct(){
		$this->_init('webforms/abstract','id');
	}

	public function getEntityType(){}

	public function setStoreId($store_id){
		$this->_store_id = $store_id;
	}
	
	public function getStoreId(){
		return $this->_store_id;
	}
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{			
		if($this->getStoreId()){
			$store = Mage::getModel('webforms/store')->search($this->getStoreId(),$this->getEntityType(),$object->getId());
			
			$object->setStoreData($store->getStoreData());
			
			if($store->getStoreData())
				foreach($store->getStoreData() as $key=>$val){
					$object->setData($key,$val);
				}
		}
				
		return parent::_afterLoad($object);
	}
	
	protected function _beforeDelete(Mage_Core_Model_Abstract $object)
	{
		Mage::getModel('webforms/store')->deleteAllStoreData($this->getEntityType(),$object->getId());
        return parent::_beforeDelete($object);
	}
}
