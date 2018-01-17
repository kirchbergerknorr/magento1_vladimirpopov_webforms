<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_FilesController extends Mage_Core_Controller_Front_Action
{

    public function downloadAction()
    {
        $hash = $this->getRequest()->getParam('hash');

        if ($hash) {
            /** @var VladimirPopov_WebForms_Model_Files $file */
            $file = Mage::getModel('webforms/files')->loadByHash($hash);

            $result = $file->getResult();
            if($result) {
                $webform = $result->getWebform();
                if($webform && $webform->getData('frontend_download')) {
                    if (file_exists($file->getFullPath())) {
                        $this->_prepareDownloadResponse($file->getName(),
                            array('value' => $file->getFullPath(), 'type' => 'filename'),
                            $file->getMimeType());
                    }
                }
            }
        }
        $this->norouteAction();
    }

    public function dropzoneAction()
    {
        $result = array();
        $result['hash'] = '';
        $result['error'] = '';

        $uploaded_files = array();
        $file_id = $this->getRequest()->getParam('file_id');
        $field_id = str_replace('file_', '', $file_id);
        /** @var VladimirPopov_WebForms_Model_Fields $field */
        $field = Mage::getModel('webforms/fields')->setStoreId(Mage::app()->getStore()->getId())->load($field_id);
        $uploader = new Zend_Validate_File_Upload;
        $valid = $uploader->isValid($file_id);
        if ($valid) {
            $file = $uploader->getFiles($file_id);
            $uploaded_files[$file_id] = $file[$file_id];
            $result['error'] = $field->validate($file[$file_id]);
        }
        if (!$result['error']) {
            $hash = Mage::getModel('webforms/dropzone')->upload($uploaded_files);
            $result['hash'] = $hash;
        }
        $this->getResponse()->setBody(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
    }
}