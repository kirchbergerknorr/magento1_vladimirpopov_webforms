<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($value) {
            $customer = Mage::getModel('customer/customer')->load($value);
            if ($customer->getId()) {
                $output = "<a href='" . $this->getCustomerUrl($row) . "' target='_blank'>" . $customer->getName() . "</a>";
                if($this->getColumn()->getExport()){
                    $output = $customer->getName();
                }
            }
            else
                $output = Mage::helper('webforms')->__('Guest');
        } else {
            $output = Mage::helper('webforms')->__('Guest');
        }
        return $output;
    }

    public function getCustomerUrl(Varien_Object $row)
    {

        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getCustomerId(), '_current' => false));
    }

}
