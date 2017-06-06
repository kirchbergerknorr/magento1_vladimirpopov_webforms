<?php

class VladimirPopov_WebForms_Block_Adminhtml_Element_Customer extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $config = array(
            'value' => $this->getValue(),
            'template' => 'webforms/result/customer.phtml'
        );
        $html = Mage::app()->getLayout()->createBlock('core/template', $this->getName(), $config)->toHtml();
        $html .= $this->getAfterElementHtml();

        return $html;
    }

}