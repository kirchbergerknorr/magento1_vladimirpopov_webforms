<?php

class VladimirPopov_WebForms_Block_Adminhtml_Element_Image extends VladimirPopov_WebForms_Block_Adminhtml_Element_File
{

    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getData('result_id')) {
            $result = Mage::getModel('webforms/results')->load($this->getData('result_id'));
            $field_id = $this->getData('field_id');
            $files = Mage::getModel('webforms/files')->getCollection()
                ->addFilter('result_id', $result->getId())
                ->addFilter('field_id', $field_id);
            /** @var VladimirPopov_WebForms_Model_Files $file */
            foreach ($files as $file) {
                if(file_exists($file->getFullPath())) {
                    $thumbnail = $file->getThumbnail(100);
                    if ($thumbnail) {
                        $html .= '<div><img src="' . $thumbnail . '"/></div>';
                    }
                    $html .= '<nobr><a href="' . $file->getDownloadLink() . '">' . $file->getName() . '</a> <small>[' . $file->getSizeText() . ']</small></nobr><br>';
                }
            }
        }
        return $html;

    }
}
