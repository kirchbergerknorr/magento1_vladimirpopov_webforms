<?php
class VladimirPopov_WebForms_Block_Adminhtml_Results_Popup
    extends Mage_Adminhtml_Block_Widget_Container{

    protected function _toHtml()
    {
        if($this->getResult()) {
            $this->addButton('print', array
            (
                'label' => Mage::helper('webforms')->__('Print'),
                'class' => 'save',
                'onclick' => 'setLocation(\'' . $this->getUrl('*/webforms_print_result/print', array('_current' => true)) . '\')',
            ));
            $this->_addButton('edit', array
            (
                'label' => Mage::helper('webforms')->__('Edit Result'),
                'onclick' => 'window.top.location.href = \'' . $this->getUrl('*/*/edit', array('_current' => true)) . '\'',
            ));
            $this->addButton('reply', array
            (
                'label' => Mage::helper('webforms')->__('Reply'),
                'onclick' => 'window.top.location.href = \'' . $this->getUrl('*/*/reply',array('_current'=>true)) . '\'',
            ));

        }

        return parent::_toHtml();
    }
}