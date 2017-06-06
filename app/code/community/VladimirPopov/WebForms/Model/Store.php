<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Store
	extends Mage_Core_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('webforms/store');
	}	
	
	public function search($store_id, $entity_type, $entity_id){
		
		$read = $this->getResource()->getReadConnection();
		
		$select = $read->select()
			->from($this->getResource()->getMainTable(),array('id'))
			->where('store_id=?',$store_id)
			->where('entity_type=?',$entity_type)
			->where('entity_id=?',$entity_id);
			
		$data = $read->fetchRow($select);
				
		if($data['id']){
			$this->load($data['id']);
		}
		return $this;
	}

	public function getAllStores($entity_type, $entity_id){
        $read = $this->getResource()->getReadConnection();

        $select = $read->select()
            ->from($this->getResource()->getMainTable(),array('id'))
            ->where('entity_type=?',$entity_type)
            ->where('entity_id=?',$entity_id);

        $data = $read->fetchRow($select);

        if($data['id']){
            $this->load($data['id']);
        }
        return $this;
    }
	
	public function deleteAllStoreData($entity_type, $entity_id)
	{
		$read = $this->getResource()->getReadConnection();
		
		$select = $read->select()
			->from($this->getResource()->getMainTable(),array('id'))
			->where('entity_type=?',$entity_type)
			->where('entity_id=?',$entity_id);
			
		while($data = $read->fetchRow($select)){
			Mage::getModel('webforms/store')->setId($data['id'])->delete();
		};
		
	}
}
