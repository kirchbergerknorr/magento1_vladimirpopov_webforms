<?php
class VladimirPopov_WebForms_Block_Adminhtml_License
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);

        if(Mage::helper('webforms')->isLocal())
            return $html.'<ul class="messages"><li class="success-msg"><ul><li><span>' . $this->__('Development environment detected. Serial number is not required.') . '</span></li></ul></li></ul>';

        if (Mage::helper('webforms')->isProduction()) {
            $html .= '<ul class="messages"><li class="success-msg"><ul><li><span>' . $this->__('License is active.') . '</span></li></ul></li></ul>';
        } else if (!Mage::getStoreConfig('webforms/license/serial')) {
            $html .= '<ul class="messages"><li class="error-msg"><ul><li><span>' . $this->__('Please, enter serial number.') . '</span></li></ul></li></ul>';
        } else {
            $html .= '<ul class="messages"><li class="error-msg"><ul><li><span>' . $this->__('Incorrect serial number.') . '</span></li></ul></li></ul>';
        }

        return $html;
    }

}