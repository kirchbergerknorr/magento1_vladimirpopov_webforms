<?php
/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Mysql4_Results
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('webforms/results', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

        if (!$object->getId() && $object->getCreatedTime() == "") {
            $object->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        if (count($object->getData('field')) > 0) {
            foreach ($object->getData('field') as $field_id => $value) {
                $field = Mage::getModel('webforms/fields')->load($field_id);

                // assign customer ID if email found
                if ($field->getType() == 'email' && $field->getValue('assign_customer_id_by_email') && !$object->getCustomerId()) {
                    $customer = Mage::getModel('customer/customer');
                    $customer->setWebsiteId(Mage::app()->getStore($object->getStoreId())->getWebsite())->loadByEmail($value);
                    if ($customer->getId()) {
                        $object->setCustomerId($customer->getId());
                    }
                }
            }
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        //insert field values
        if (count($object->getData('field')) > 0) {
            foreach ($object->getData('field') as $field_id => $value) {
                if (is_array($value)) {
                    $value = implode("\n", $value);
                }
                $field = Mage::getModel('webforms/fields')->load($field_id);
                if (strstr($field->getType(), 'date') && strlen($value) > 0) {
                    $date = new Zend_Date();
                    $date->setDate($value, $field->getDateFormat(), Mage::app()->getLocale()->getLocaleCode());
                    if ($field->getType() == 'datetime')
                        $date->setTime($value, $field->getDateFormat(), Mage::app()->getLocale()->getLocaleCode());
                    $value = date($field->getDbDateFormat(), $date->getTimestamp());
                }
                if ($field->getType() == 'select/contact' && is_numeric($value)) {
                    $value = $field->getContactValueById($value);
                }

                if($value == $field->getHint()){
                    $value = '';
                }

                // create key
                $key = "";
                if ($field->getType() == 'file' || $field->getType() == 'image') {
                    $key = Mage::helper('webforms')->randomAlphaNum(6);
                    if ($object->getData('key_' . $field_id))
                        $key = $object->getData('key_' . $field_id);
                }
                $object->setData('key_' . $field_id, $key);

                $select = $this->_getReadAdapter()->select()
                    ->from($this->getTable('webforms/results_values'))
                    ->where('result_id = ?', $object->getId())
                    ->where('field_id = ?', $field_id);

                $result_value = $this->_getReadAdapter()->fetchAll($select);

                if (!empty($result_value[0])) {
                    $this->_getWriteAdapter()->update($this->getTable('webforms/results_values'), array(
                            "value" => $value,
                            "key" => $key
                        ),
                        "id = " . $result_value[0]['id']
                    );

                } else {
                    $this->_getWriteAdapter()->insert($this->getTable('webforms/results_values'), array(
                        "result_id" => $object->getId(),
                        "field_id" => $field_id,
                        "value" => $value,
                        "key" => $key
                    ));
                }

                // update object
                $object->setData('field_'.$field_id, $value);
                $object->setData('key_'.$field_id, $key);


            }
        }

        Mage::dispatchEvent('webforms_result_save', array('result' => $object));

        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $webform = Mage::getModel('webforms/webforms')->load($object->getData('webform_id'));

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('webforms/results_values'))
            ->where('result_id = ?', $object->getId());
        $values = $this->_getReadAdapter()->fetchAll($select);

        foreach ($values as $val) {
            $object->setData('field_' . $val['field_id'], $val['value']);
            $object->setData('key_' . $val['field_id'], $val['key']);
        }

        $object->setData('ip', long2ip($object->getCustomerIp()));

        Mage::dispatchEvent('webforms_result_load', array('webform' => $webform, 'result' => $object));

        return parent::_afterLoad($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        //delete values
        $this->_getReadAdapter()->delete($this->getTable('webforms/results_values'),
            'result_id = ' . $object->getId()
        );

        //delete files
        $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $object->getId());
        foreach ($files as $file){
            $file->delete();
        }

        //clear messages
        $messages = Mage::getModel('webforms/message')->getCollection()->addFilter('result_id', $object->getId());
        foreach ($messages as $message) $message->delete();

        Mage::dispatchEvent('webforms_result_delete', array('result' => $object));

        return parent::_beforeDelete($object);
    }

    // this function helps delete folder recursively
    /** @deprecated since 2.7.8 */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") $this->rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function getSummaryRatings($webform_id, $store_id)
    {
        $adapter = $this->_getReadAdapter();

        $sumColumn = new Zend_Db_Expr("SUM(results_values.value)");
        $countColumn = new Zend_Db_Expr("COUNT(*)");

        $select = $adapter->select()
            ->from(array('results_values' => $this->getTable('webforms/results_values')),
                array(
                    'sum' => $sumColumn,
                    'count' => $countColumn,
                    'field_id'
                ))
            ->join(array('fields' => $this->getTable('webforms/fields')),
                'results_values.field_id = fields.id',
                array())
            ->join(array('results' => $this->getTable('webforms/results')),
                'results_values.result_id = results.id',
                array())
            ->where('fields.type = "stars"')
            ->where('results.webform_id = ' . $webform_id)
            ->where('results.store_id = ' . $store_id)
            ->where('results.approved = 1')
            ->group('results_values.field_id');
        return $adapter->fetchAll($select);
    }

}
