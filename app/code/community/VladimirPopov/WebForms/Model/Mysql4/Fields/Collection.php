<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Mysql4_Fields_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/fields');
    }

    protected function _afterLoad()
    {
        $store_id = $this->getResource()->getStoreId();
        if ($store_id) {
            foreach ($this as $item) {
                $store = Mage::getModel('webforms/store')->search($store_id, $this->getResource()->getEntityType(), $item->getId());
                $store_data = $store->getStoreData();
                if ($store_data) {
                    foreach ($store_data as $key => $val) {
                        $item->setData($key, $val);
                    }
                }
            }
        }

        foreach ($this as $item) {

            if (!is_array($item->getValue())) {
                $unserialized_value = @unserialize($item->getValue());
            }
            if (is_array($item->getValue())) {
                $unserialized_value = $item->getValue();
            }
            if (!empty($unserialized_value)) {
                switch ($item->getType()) {
                    case 'url':
                        if (!empty($unserialized_value["text_url"]))
                            $unserialized_value["text"] = $unserialized_value["text_url"];
                        break;
                    case 'email':
                        if (!empty($unserialized_value["text_email"]))
                            $unserialized_value["text"] = $unserialized_value["text_email"];
                        break;
                    case 'select/radio':
                        if (!empty($unserialized_value["options_radio"]))
                            $unserialized_value["options"] = $unserialized_value["options_radio"];
                        break;
                    case 'select/checkbox':
                        if (!empty($unserialized_value["options_checkbox"]))
                            $unserialized_value["options"] = $unserialized_value["options_checkbox"];
                        break;
                    case 'select/contact':
                        if (!empty($unserialized_value["options_contact"]))
                            $unserialized_value["options"] = $unserialized_value["options_contact"];
                        break;
                }
                if (!empty($unserialized_value["text"])) {
                    $unserialized_value["text_url"] = $unserialized_value["text"];
                    $unserialized_value["text_email"] = $unserialized_value["text"];
                }
                if (!empty($unserialized_value["options"])) {
                    $unserialized_value["options_radio"] = $unserialized_value["options"];
                    $unserialized_value["options_checkbox"] = $unserialized_value["options"];
                    $unserialized_value["options_contact"] = $unserialized_value["options"];
                }
                $item->setValue($unserialized_value);
            } else {
                // support for old value format
                $value = $item->getValue();
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
                $item->setValue($value_array);
            }
        }
        return parent::_afterLoad();
    }
}  
