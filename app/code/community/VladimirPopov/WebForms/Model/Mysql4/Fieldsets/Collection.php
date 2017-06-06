<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Mysql4_Fieldsets_Collection
	extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	
	public function _construct(){
		parent::_construct();
		$this->_init('webforms/fieldsets');
	}

	protected function _afterLoad()
	{
		$store_id = $this->getResource()->getStoreId();
		if($store_id){
			foreach($this as $item){
				$store = Mage::getModel('webforms/store')->search($store_id, $this->getResource()->getEntityType(), $item->getId());
				$store_data = $store->getStoreData();
				if($store_data){
					foreach($store_data as $key=>$val){
						$item->setData($key,$val);					
					}
				}
			}
		}
		return parent::_afterLoad();
	}
}  
