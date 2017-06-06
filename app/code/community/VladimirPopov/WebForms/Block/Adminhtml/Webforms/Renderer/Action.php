<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $store = Mage::app()->getStore(Mage::getStoreConfig('webforms/general/preview_store'));
        if (!$store->getId()) {
            $store = Mage::app()->getStore();
        }
        $class = "grid-button-action inline-action";

        $export_url = $this->getUrl('*/*/export', array('_current' => false, 'id' => $row->getId()));
        $button_export = '<a href="' . $export_url . '" class="' . $class . '"><span>' . $this->__('Export') . '</span></a>';

        $preview_url = $store->getUrl('webforms', array('_current' => false, 'id' => $row->getId()));
        $button_preview = '<a href="' . $preview_url . '" target="_blank" class="' . $class . '"><span>' . $this->__('Preview') . '</span></a>';

        return '<div class="inline-buttons">' . $button_export . $button_preview . '</div>';
    }
}
