<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Mysql4_Webforms
	extends VladimirPopov_WebForms_Model_Mysql4_Abstract
{
	const ENTITY_TYPE = 'form';

	public function getEntityType(){
		return self::ENTITY_TYPE;
	}
	
	public function _construct(){
		$this->_init('webforms/webforms','id');
	}

    protected function _beforeSave(Mage_Core_Model_Abstract $object){
		if($object->getData('access_groups')) $object->setData('access_groups_serialized', serialize($object->getData('access_groups')));
		if($object->getData('dashboard_groups')) $object->setData('dashboard_groups_serialized', serialize($object->getData('dashboard_groups')));

        Mage::dispatchEvent('webforms_before_save',array('webform'=>$object));

        return parent::_beforeSave($object);
    }

	protected function _afterSave(Mage_Core_Model_Abstract $object){
		
		Mage::dispatchEvent('webforms_after_save',array('webform'=>$object));
		
		return parent::_afterSave($object);
	}
	
	protected function _beforeDelete(Mage_Core_Model_Abstract $object){
		//delete fields
		$fields = Mage::getModel('webforms/fields')->getCollection()->addFilter('webform_id',$object->getId());
		foreach($fields as $field){
			$field->delete();
		}
		//delete fieldsets
		$fieldsets = Mage::getModel('webforms/fieldsets')->getCollection()->addFilter('webform_id',$object->getId());
		foreach($fieldsets as $fieldset){
			$fieldset->delete();
		}
		
		Mage::dispatchEvent('webforms_after_delete',array('webform'=>$object));

		return parent::_beforeDelete($object);
	}
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{
		$object->setData('access_groups', unserialize($object->getData('access_groups_serialized')));
		$object->setData('dashboard_groups', unserialize($object->getData('dashboard_groups_serialized')));

		Mage::dispatchEvent('webforms_after_load',array('webform' => $object));
				
		return parent::_afterLoad($object);
	}

}