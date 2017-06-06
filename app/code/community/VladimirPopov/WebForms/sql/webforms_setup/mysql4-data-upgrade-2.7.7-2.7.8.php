<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$connection = $installer->getConnection();

// register existing files in new table

$select = $connection->select()
    ->from(array('v' => $installer->getTable('webforms/results_values')), array('v.result_id', 'v.value', 'v.key', 'v.field_id'))
    ->join(array('f' => $installer->getTable('webforms/fields')), 'f.id = v.field_id', array())
    ->join(array('r' => $installer->getTable('webforms/results')), 'r.id = v.result_id', array('r.webform_id'))
    ->where('f.type = "file" or f.type = "image"')
    ->where('v.value <> ""');

$query = $select->query();

while ($file = $query->fetch()) {
    $rel_path = 'webforms' . DS .
        $file['result_id'] . DS .
        $file['field_id'] . DS .
        $file['key'] . DS .
        Varien_File_Uploader::getCorrectFileName($file['value']);
    $full_path = Mage::getBaseDir('media') . DS . $rel_path;
    if (file_exists($full_path)) {

        // check if file has not been imported already

        $collection = Mage::getModel('webforms/files')->getCollection()
            ->addFilter('field_id', $file['field_id'])
            ->addFilter('result_id', $file['result_id']);
        if (!$collection->getSize()) {
            $link_hash = Mage::helper('webforms')->randomAlphaNum(40);
            $size = filesize($full_path);
            $mime = mime_content_type($full_path);

            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($full_path);
            }

            $model = Mage::getModel('webforms/files');
            $model->setData('result_id', $file['result_id'])
                ->setData('field_id', $file['field_id'])
                ->setData('name', $file['value'])
                ->setData('size', $size)
                ->setData('mime_type', $mime)
                ->setData('path', $rel_path)
                ->setData('link_hash', $link_hash);
            $model->save();
        }
    }
}

// delete old script js/webforms/upload

$old_upload_folder = Mage::getBaseDir() . DS . 'js' . DS . 'webforms' . DS . 'upload';
if (is_dir($old_upload_folder)) {
    try {
        Mage::helper('webforms')->rrmdir($old_upload_folder);
    } catch (Exception $e) {

    }
}