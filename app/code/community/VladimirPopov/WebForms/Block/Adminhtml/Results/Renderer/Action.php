<?php

class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $class = "grid-button-action";
        $edit_url = $this->getUrl('*/*/edit', array('_current' => false, 'id' => $row->getId()));
        $reply_url = $this->getUrl('*/*/reply', array('_current' => true, 'id' => $row->getId()));
        $print_url = $this->getUrl('*/webforms_print_result/print', array('result_id' => $row->getId()));

        $button_print = '<a href="' . $print_url . '" class="' . $class . '"><span>' . $this->__('Print') . '</span></a>';
        $button_edit = '<a href="' . $edit_url . '" class="' . $class . '"><span>' . $this->__('Edit') . '</span></a>';
        $button_reply = '<a href="' . $reply_url . '" class="' . $class . '"><span>' . $this->__('Reply') . '</span></a>';


        return $button_print . $button_edit . $button_reply;
    }
}