<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Fieldsets
	extends VladimirPopov_WebForms_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/fieldsets');
	}
	
	public function duplicate(){
		// duplicate fieldset
		$fieldset = Mage::getModel('webforms/fieldsets')
			->setData($this->getData())
			->setId(null)
			->setName($this->getName().' '.Mage::helper('webforms')->__('(new copy)'))
			->setIsActive(false)
			->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
			->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
			->save();
		
		// duplicate store data
		$stores = Mage::getModel('webforms/store')
			->getCollection()
			->addFilter('entity_id',$this->getId())
			->addFilter('entity_type',$this->getEntityType());
		
		foreach($stores as $store){
			$duplicate = Mage::getModel('webforms/store')
				->setData($store->getData())
				->setId(null)
				->setEntityId($fieldset->getId())
				->save();
		}
		
		// duplicate fields
		$fields = Mage::getModel('webforms/fields')->getCollection()->addFilter('fieldset_id',$this->getId());
		foreach($fields as $field){
			$field->duplicate()
				->setFieldsetId($fieldset->getId())
				->save();
		}
		
		return $fieldset;
	}

	public function getName(){
		if(Mage::getStoreConfig('webforms/general/use_translation')){
			return Mage::helper('webforms')->__($this->getData('name'));
		}
		
		return $this->getData('name');
	}

}
