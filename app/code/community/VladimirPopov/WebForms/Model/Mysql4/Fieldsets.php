<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Mysql4_Fieldsets
	extends VladimirPopov_WebForms_Model_Mysql4_Abstract
{
	const ENTITY_TYPE = 'fieldset';
	
	public function getEntityType(){
		return self::ENTITY_TYPE;
	}

	public function _construct(){
		$this->_init('webforms/fieldsets','id');
	}
	
	protected function _afterSave(Mage_Core_Model_Abstract $object){
		
		Mage::dispatchEvent('webforms_fieldset_save',array('fieldset'=>$object));

		return parent::_afterSave($object);
	}
	
	protected function _beforeDelete(Mage_Core_Model_Abstract $object){
		//set fields fieldset_id to null
		$fields = Mage::getModel('webforms/fields')->getCollection()->addFilter('fieldset_id',$object->getId());
		foreach($fields as $field){
			$field->setFieldsetId(0)->save();
		}

		Mage::dispatchEvent('webforms_fieldset_delete',array('fieldset'=>$object));

		return parent::_beforeDelete($object);
	}

    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        //update logic rules
        $webform = Mage::getModel('webforms/webforms')->load($object->getData('webform_id'));
        $logic_collection = $webform->getLogic();
        foreach ($logic_collection as $logic_rule){
            $logic_rule->save();
        }

        return parent::_beforeDelete($object);
    }

	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{		
		Mage::dispatchEvent('webforms_fieldset_after_load',array('fieldset' => $object));
				
		return parent::_afterLoad($object);
	}
}  
