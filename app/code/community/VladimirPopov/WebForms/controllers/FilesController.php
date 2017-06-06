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
            if (file_exists($file->getFullPath())) {
                $this->_prepareDownloadResponse($file->getName(),
                    array('value' => $file->getFullPath(), 'type' => 'filename'),
                    $file->getMimeType());
            }
        }
        $this->norouteAction();
    }
}