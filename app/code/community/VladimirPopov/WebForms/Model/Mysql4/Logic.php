<?php
class VladimirPopov_WebForms_Model_Mysql4_Logic
    extends VladimirPopov_WebForms_Model_Mysql4_Abstract
{
    const ENTITY_TYPE = 'logic';

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    public function _construct()
    {
        $this->_init('webforms/logic', 'id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('value', unserialize($object->getData('value_serialized')));
        $object->setData('target', unserialize($object->getData('target_serialized')));

        Mage::dispatchEvent('webforms_logic_after_load', array('logic' => $object));

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getData('value'))) $object->setData('value_serialized', serialize($object->getData('value')));
        if (is_array($object->getData('target'))) {
            $targets = $object->getData('target');
            foreach($targets as $i => $t){
                if(strstr($t, 'field_')){
                    $field_id = str_replace('field_','',$t);
                    $field = Mage::getModel('webforms/fields')->load($field_id);
                    if(!$field->getId()){
                        unset($targets[$i]);
                    }
                }

                if(strstr($t, 'fieldset_')){
                    $fieldset_id = str_replace('fieldset_','',$t);
                    $fieldset = Mage::getModel('webforms/fieldsets')->load($fieldset_id);
                    if(!$fieldset->getId()){
                        unset($targets[$i]);
                    }
                }
            }
            $object->setData('target_serialized', serialize($targets));
        }

        Mage::dispatchEvent('webforms_logic_before_save', array('logic' => $object));

        return parent::_beforeSave($object);
    }

}