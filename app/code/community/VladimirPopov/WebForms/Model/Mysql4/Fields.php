<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Mysql4_Fields
    extends VladimirPopov_WebForms_Model_Mysql4_Abstract
{
    const ENTITY_TYPE = 'field';

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    public function _construct()
    {
        $this->_init('webforms/fields', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

        if (is_array($object->getValue())) $object->setValue(serialize($object->getValue()));

        Mage::dispatchEvent('webforms_field_save_before', array('field' => $object));

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {

        Mage::dispatchEvent('webforms_field_save_after', array('field' => $object));

        return parent::_afterSave($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        //delete values
        $this->_getReadAdapter()->delete($this->getTable('webforms/results_values'), 'field_id =' . $object->getId());
        $this->_getReadAdapter()->delete($this->getTable('webforms/logic'), 'field_id =' . $object->getId());

        //delete files
        $files = Mage::getModel('webforms/files')->getCollection()->addFilter('field_id', $object->getId());
        foreach ($files as $file) {
            $file->delete();
        }

        Mage::dispatchEvent('webforms_field_delete', array('field' => $object));

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
        parent::_afterLoad($object);

        if (!is_array($object->getValue())) {
            $unserialized_value = @unserialize($object->getValue());
            if ($unserialized_value) {
                $object->setValue($unserialized_value);
            } else {
                // support for old value format
                $value = $object->getValue();
                $stars_value = explode("\n", $value);
                if (empty($stars_value[1])) $stars_value[1] = false;
                $value_array = array(
                    'text' => $value,
                    'text_email' => $value,
                    'text_url' => $value,
                    'textarea' => $value,
                    'newsletter' => $value,
                    'stars_init' => $stars_value[1],
                    'stars_max' => $stars_value[0],
                    'options' => $value,
                    'options_radio' => $value,
                    'options_checkbox' => $value,
                    'options_contact' => $value,
                    'allowed_extensions' => $value,
                    'html' => $value,
                    'hidden' => $value,
                );
                $object->setValue($value_array);
            }
        }

        $value = $object->getValue();
        switch ($object->getType()) {
            case 'url':
                if (!empty($value["text_url"]))
                    $value["text"] = $value["text_url"];
                break;
            case 'email':
                if (!empty($value["text_email"]))
                    $value["text"] = $value["text_email"];
                break;
            case 'select/radio':
                if (!empty($value["options_radio"]))
                    $value["options"] = $value["options_radio"];
                break;
            case 'select/checkbox':
                if (!empty($value["options_checkbox"]))
                    $value["options"] = $value["options_checkbox"];
                break;
            case 'select/contact':
                if (!empty($value["options_contact"]))
                    $value["options"] = $value["options_contact"];
                break;
        }
        if (!empty($value["text"])) {
            $value["text_url"] = $value["text"];
            $value["text_email"] = $value["text"];
        }
        if (!empty($value["options"])) {
            $value["options_radio"] = $value["options"];
            $value["options_checkbox"] = $value["options"];
            $value["options_contact"] = $value["options"];
        }
        $object->setValue($value);

        Mage::dispatchEvent('webforms_field_load_after', array('field' => $object));

        $store_data = $object->getData('store_data');
        if (!empty($store_data['value']) && is_array($store_data['value'])) {
            foreach ($store_data['value'] as $key => $value) {
                $store_data['value_' . $key] = $value;
            }
        }
        $object->setStoreData($store_data);

        return $this;
    }

    public function getNextPosition($webformId)
    {
        $sql = new Zend_Db_Select($this->getReadConnection());
        $sql
            ->from($this->getMainTable(), 'position')
            ->where('webform_id = ?', $webformId)
            ->order('position DESC');

        $position = intval($this->getReadConnection()->fetchOne($sql));
        if (!$position) {
            $position = 0;
        }

        return $position + 10;
    }

}
