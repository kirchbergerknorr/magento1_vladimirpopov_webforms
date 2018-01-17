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
            $width = Mage::getStoreConfig('webforms/images/grid_thumbnail_width');
            $height = Mage::getStoreConfig('webforms/images/grid_thumbnail_height');

            if (count($files)) {
                $html .= '<div class="webforms-file-pool">';
                $html .= $this->_getSelectAllHtml();
                foreach ($files as $file) {
                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $nameStart = '<div class="webforms-file-link-name">' . substr($file->getName(), 0, strlen($file->getName()) - 7) . '</div>';
                        $nameEnd = '<div class="webforms-file-link-name-end">' . substr($file->getName(), -7) . '</div>';

                        $thumbnail = $file->getThumbnail(100);
                        if ($thumbnail) {
                            $html .= '<a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '"/></p>
                                <figcaption>' . $file->getName() . ' <small>[' . $file->getSizeText() . ']</small></figcaption>
                            </figure>
                        </a>';
                        } else {
                            $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                        }
                    }
                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }

        }
        return $html;

    }
}
