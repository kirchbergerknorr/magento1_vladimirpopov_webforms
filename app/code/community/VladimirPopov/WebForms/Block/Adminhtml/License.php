<?php

class VladimirPopov_WebForms_Block_Adminhtml_License
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    final protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);

        $html .= '<ul id="webforms_license_messages" class="messages">';

        if (VladimirPopov_WebForms_Helper_Data::DEV_CHECK)
            if (Mage::helper('webforms')->isLocal())
                return $html . '<li class="success-msg"><ul><li><span>' . Mage::helper('webforms')->__('Development environment detected. Serial number is not required.') . '</span></li></ul></li></ul>';

        if (!Mage::getStoreConfig('webforms/license/serial')) {
            $html .= '<li class="error-msg"><ul><li><span>' . Mage::helper('webforms')->__('Please, enter serial number.') . '</span></li></ul></li>';
        } else {
            $url = $this->getUrl('adminhtml/webforms_license/verify');
            $html .= '
            <script>
                    var xhr = new XMLHttpRequest();
                    xhr.responseType = \'json\';
                    xhr.open(\'GET\', \'' . $url . '\', true);
                    xhr.onload  = function() {
                       var messages = document.getElementById(\'webforms_license_messages\');   
                       if (this.status === 200) {   
                           var data = xhr.response;
                           if(data.verified){
                                messages.innerHTML = \'<li class="success-msg"><ul><li><span>' . Mage::helper('webforms')->__('License is active.') . '</span></li></ul></li>\';
                           } else {
                                messages.innerHTML = \'\';
                           }
                           if(data.errors){
                                for(var i=0; i< data.errors.length; i++){
                                     messages.innerHTML += \'<li class="error-msg"><ul><li><span>\' + data.errors[i] + \'</span></li></ul></li>\';
                                }
                           }
                           if(data.warnings){
                                for(var i=0; i< data.warnings.length; i++){
                                     messages.innerHTML += \'<li class="warning-msg"><ul><li><span>\' + data.warnings[i] + \'</span></li></ul></li>\';
                                }
                           }  
                       } else {
                           messages.innerHTML = \'<li class="error-msg"><ul><li><span>' . Mage::helper('webforms')->__('Unknown error(s) occurred.') . '</span></li></ul></li>\';
                       }
                    };
                    xhr.send();
            </script>
            ';
            $html .= '<li class="success-msg"><ul><li><span>' . Mage::helper('webforms')->__('Connecting to license server...') . '</span></li></ul></li>';

        }
        $html .= '</ul>';
        return $html;
    }

}