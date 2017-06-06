<?php
class VladimirPopov_WebForms_Model_Mysql4_Files
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('webforms/files','id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {

        if (!$object->getId() && $object->getCreatedTime() == "") {
            $object->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
        }
        return $this;
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {

        unlink($object->getFullPath());

        Mage::dispatchEvent('webforms_files_delete', array('file' => $object));

        return parent::_beforeDelete($object);
    }
}
