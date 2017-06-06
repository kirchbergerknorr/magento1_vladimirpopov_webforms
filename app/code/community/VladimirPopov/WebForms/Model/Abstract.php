<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Abstract
    extends Mage_Core_Model_Abstract
{
    public function getEntityType()
    {
        return $this->getResource()->getEntityType();
    }

    public function setStoreId($store_id)
    {
        $this->getResource()->setStoreId($store_id);

        return $this;
    }

    public function getStoreId()
    {
        return $this->getResource()->getStoreId();
    }

    public function saveStoreData($store_id, $data)
    {

        $object = Mage::getModel('webforms/store')
            ->search($store_id, $this->getEntityType(), $this->getId())
            ->setStoreId($store_id)
            ->setEntityType($this->getEntityType())
            ->setEntityId($this->getId())
            ->setStoreData(serialize($data))
            ->save();

        $this->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())->save();

        return $this;
    }

    public function updateStoreData($store_id, $data)
    {
        $object = Mage::getModel('webforms/store')->search($store_id, $this->getEntityType(), $this->getId());
        $store_data = $data;

        if ($object->getId()) {
            $store_data = $object->getStoreData();
            foreach ($data as $key => $val) {
                $store_data[$key] = $val;
            }
        }

        return $this->saveStoreData($store_id, $store_data);
    }
}
