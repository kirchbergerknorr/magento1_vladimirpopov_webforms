<?php
class VladimirPopov_WebForms_Block_Adminhtml_Logic_Renderer_Value
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        return implode('<br>',$value);
    }
}