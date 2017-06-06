<?php
class VladimirPopov_WebForms_Block_Adminhtml_Element_Checkboxes
    extends Varien_Data_Form_Element_Checkboxes
{
    public function getElementHtml()
    {
        if($this->getRequired()){
            $html = "<script>$$('#{$this->getHtmlId()}_container input').last().setAttribute('class','validate-one-required-by-name')</script>";
            $this->setAfterElementHtml($html);
        }

        return parent::getElementHtml();
    }

    // override default option id
    protected function _optionToHtml($option)
    {
        $id = $this->getHtmlId().'_'.Mage::helper('webforms')->randomAlphaNum();

        $html = '<li><input id="'.$id.'"';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $html .= ' '.$attribute.'="'.$value.'"';
            }
        }
        $html .= ' value="'.$option['value'].'" />'
            . ' <label for="'.$id.'">' . $option['label'] . '</label></li>'
            . "\n";
        return $html;
    }
}