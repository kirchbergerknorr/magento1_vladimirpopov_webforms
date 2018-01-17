<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Dropzone extends Mage_Core_Model_Abstract
{
    const UPLOAD_DIR = 'webforms/dropzone';

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/dropzone');
    }

    public static function getUploadDir()
    {
        return Mage::getBaseDir('media') . DS . self::UPLOAD_DIR;
    }

    public function getFullPath()
    {
        return Mage::getBaseDir('media') . DS . $this->getPath();
    }

    public function upload($uploaded_files)
    {
        foreach ($uploaded_files as $file_id => $file) {

            $uploader = new Varien_File_Uploader($file_id);
            $field_id = str_replace('file_', '', $file_id);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $tmp_name = Mage::helper('webforms')->randomAlphaNum(20);
            $hash = Mage::helper('webforms')->randomAlphaNum(40);
            $size = filesize($file['tmp_name']);
            $mime = VladimirPopov_WebForms_Model_Uploader::getMimeType($file['tmp_name']);

            $success = $uploader->save($this->getUploadDir(), $tmp_name);

            if ($success) {
                // save new file
                $this->setData('name', $file['name'])
                    ->setData('field_id', $field_id)
                    ->setData('size', $size)
                    ->setData('mime_type', $mime)
                    ->setData('path', self::UPLOAD_DIR . DS . $tmp_name)
                    ->setData('hash', $hash);
                $this->save();

                if ($this->getId()) {
                    return $hash;
                }
            }
        }

        return false;
    }

    public function toFile($result_id)
    {
        $tmp_name = Mage::helper('webforms')->randomAlphaNum(20);
        $link_hash = Mage::helper('webforms')->randomAlphaNum(40);

        /** @var VladimirPopov_WebForms_Model_Files $model */
        $model = Mage::getModel('webforms/files');

        $file_path = VladimirPopov_WebForms_Model_Uploader::getUploadDir() . DS . $tmp_name;

        // save new file
        $model->setData('result_id', $result_id)
            ->setData('field_id', $this->getFieldId())
            ->setData('name', $this->getName())
            ->setData('size', $this->getSize())
            ->setData('mime_type', $this->getMimeType())
            ->setData('path', VladimirPopov_WebForms_Model_Uploader::getPath() . DS . $tmp_name)
            ->setData('link_hash', $link_hash);
        $model->save();

        if (!is_dir(dirname($file_path)))
            mkdir(dirname($file_path), 0755, true);

        copy($this->getFullPath(), $file_path);

        return $model;
    }

    public function cleanup()
    {
        $collection = $this->getCollection()->addFieldToFilter('created_time', array('lt' => date("Y-m-d H:i:s", strtotime('-1 hour'))));
        foreach ($collection as $item)
            $item->delete();
    }

    public function loadByHash($hash)
    {
        $collection = Mage::getModel('webforms/dropzone')->getCollection()->addFilter('hash', $hash);
        return $collection->getFirstItem();
    }
}