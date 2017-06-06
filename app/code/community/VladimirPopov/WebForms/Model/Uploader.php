<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Uploader
{
    const UPLOAD_DIR = 'webforms/upload';

    protected $_result;

    public function setResult(VladimirPopov_WebForms_Model_Results $result)
    {
        $this->_result = $result;
    }
    /**
     * @return VladimirPopov_WebForms_Model_Results
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return VladimirPopov_WebForms_Model_Webforms
     */
    public function getWebform()
    {
        return $this->getResult()->getWebform();
    }

    public static function getUploadDir()
    {
        return Mage::getBaseDir('media') . DS . self::getPath();
    }

    public static function getPath()
    {
        return self::UPLOAD_DIR;
    }

    public function upload()
    {
        if ($this->getResult()) {
            $uploaded_files = $this->getWebform()->getUploadedFiles();

            foreach ($uploaded_files as $field_id => $file) {
                $file_id = 'file_' . $field_id;
                $uploader = new Varien_File_Uploader($file_id);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $tmp_name = Mage::helper('webforms')->randomAlphaNum(20);
                $link_hash = Mage::helper('webforms')->randomAlphaNum(40);
                $size = filesize($file['tmp_name']);
                $mime = self::getMimeType($file['tmp_name']);

                $success = $uploader->save($this->getUploadDir(), $tmp_name);

                if ($success) {
                    /** @var VladimirPopov_WebForms_Model_Files $model */
                    $model = Mage::getModel('webforms/files');

                    // remove previously uploaded file
                    $collection = $model->getCollection()
                        ->addFilter('result_id', $this->getResult()->getId())
                        ->addFilter('field_id', $field_id);
                    /** @var VladimirPopov_WebForms_Model_Files $old_file */
                    foreach ($collection as $old_file) $old_file->delete();

                    // save new file
                    $model->setData('result_id', $this->getResult()->getId())
                        ->setData('field_id', $field_id)
                        ->setData('name', $file['name'])
                        ->setData('size', $size)
                        ->setData('mime_type', $mime)
                        ->setData('path', $this->getPath(). DS . $tmp_name)
                        ->setData('link_hash', $link_hash);
                    $model->save();
                }
            }
        }
    }

    public static function getMimeType($path){
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = $finfo->file($path);
            return $type;
        }
        return mime_content_type($path);
    }
}