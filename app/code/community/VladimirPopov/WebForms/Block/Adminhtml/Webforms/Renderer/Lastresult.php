<?php
class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Lastresult
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
    protected function _getValue(Varien_Object $row)
    {
        $last_result = Mage::getModel('webforms/results')->getCollection()->addFilter('webform_id',$row->getId());
        $last_result->getSelect()->order('created_time desc')->limit(1);

        return $last_result->getFirstItem()->getData('created_time');
    }
}