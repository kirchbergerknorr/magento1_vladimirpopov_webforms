<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Logic_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'webforms';
        $this->_controller = 'adminhtml_logic';

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => "$('saveandcontinue').value = true; editForm.submit()",
            'class' => 'save',
        ), -100);
    }

    public function getSaveUrl()
    {
        $params = array(
            'field_id' => Mage::registry('field')->getId(),
            'webform_id' => $this->getRequest()->getParam('webform_id'),
            'store' => $this->getRequest()->getParam('store')
        );
        return $this->getUrl('*/*/save', $params);
    }

    public function getBackUrl()
    {
        $store = $this->getRequest()->getParam('store');
        if ($this->getRequest()->getParam('webform_id')) {
            return $this->getUrl('*/webforms_webforms/edit', array(
                'id' => $this->getRequest()->getParam('webform_id'),
                'tab' => 'logic',
                'store' => $store));
        }
        return $this->getUrl('*/webforms_fields/edit', array(
            'id' => Mage::registry('field')->getId(),
            'tab' => 'logic',
            'store' => $store));
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array(
            $this->_objectId => $this->getRequest()->getParam($this->_objectId),
            'webform_id' => $this->getRequest()->getParam('webform_id'),
        ));
    }

    public function getHeaderText()
    {
        if (!is_null(Mage::registry('logic')->getId())) {
            return Mage::helper('webforms')->__("%s: Edit Logic", $this->htmlEscape(Mage::registry('field')->getName()));
        } else {
            return Mage::helper('webforms')->__('%s: New Logic', $this->htmlEscape(Mage::registry('field')->getName()));
        }
    }

    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        // add store switcher
        if (!Mage::app()->isSingleStoreMode() && $this->getRequest()->getParam('id')) {
            $store_switcher = $this->getLayout()->createBlock('adminhtml/store_switcher', 'store_switcher');
            $store_switcher->setDefaultStoreName($this->__('Default Values'));

            $html = $store_switcher->toHtml() . $html;

        }
        return $html;
    }
}
