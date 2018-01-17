<?php

class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Files
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $field = Mage::getModel('webforms/fields')->load($field_id);

        $files = Mage::getModel('webforms/files')->getCollection()
            ->addFilter('result_id', $row->getId())
            ->addFilter('field_id', $field_id);

        $html = '';

        /** @var VladimirPopov_WebForms_Model_Files $file */
        foreach ($files as $file) {
            $nameStart = '<div class="webforms-file-link-name">' . substr($file->getName(), 0, strlen($file->getName()) - 7) . '</div>';
            $nameEnd = '<div class="webforms-file-link-name-end">' . substr($file->getName(), -7) . '</div>';
            if (file_exists($file->getFullPath())) {
                if ($field->getType() == 'file') {
                    $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                }
                if ($field->getType() == 'image') {
                    $width = Mage::getStoreConfig('webforms/images/grid_thumbnail_width');
                    $height = Mage::getStoreConfig('webforms/images/grid_thumbnail_height');
                    if ($file->getThumbnail($width, $height)) {

                        $html .= '<a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink() . '">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '"/></p>
                                <figcaption>' . $file->getName() . ' <small>[' . $file->getSizeText() . ']</small></figcaption>
                            </figure>
                        </a>';
                    } else {
                        $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                    }
                }
            } else {
                $html .= '<nobr><a class="grid-button-action webforms-file-link" href="javascript:alert(\'' . Mage::helper('webforms')->__('File not found.') . '\')">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';

            }
        }

        $html_object = new Varien_Object(array('html' => $html));

        Mage::dispatchEvent('webforms_block_adminhtml_results_renderer_value_render', array('field' => $field, 'html_object' => $html_object, 'value' => $value));

        if ($html_object->getHtml())
            return $html_object->getHtml();
    }

}