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
            if(file_exists($file->getFullPath())) {
                if ($field->getType() == 'file') {
                    $html .= '<nobr><a href="' . $file->getDownloadLink() . '">' . $file->getName() . '</a> <small>['.$file->getSizeText().']</small></nobr>' .
                        '<br><small>' . $this->__('Type') . ': ' . $file->getMimeType() . '</small>';

                }
                if ($field->getType() == 'image') {
                    $width = Mage::getStoreConfig('webforms/images/grid_thumbnail_width');
                    $height = Mage::getStoreConfig('webforms/images/grid_thumbnail_height');
                    if ($file->getThumbnail($width, $height))
                        $html .= '<a href="' . $file->getDownloadLink() . '"><img src="' . $file->getThumbnail($width, $height) . '"/></a><br>';
                    $html .= '<nobr><a href="' . $file->getDownloadLink() . '">' . $file->getName() . '</a> <small>[' . $file->getSizeText() . ']</small></nobr>';
                }
            }
            else {
                $html .= '<nobr>'. $file->getName() . ' <small>['.$file->getSizeText().']</small></nobr>' .
                         '<br><small>' . $this->__('Type') . ': ' . $file->getMimeType() . '</small>';
            }
        }

        $html_object = new Varien_Object(array('html' => $html));

        Mage::dispatchEvent('webforms_block_adminhtml_results_renderer_value_render', array('field' => $field, 'html_object' => $html_object, 'value' => $value));

        if ($html_object->getHtml())
            return $html_object->getHtml();
    }

}