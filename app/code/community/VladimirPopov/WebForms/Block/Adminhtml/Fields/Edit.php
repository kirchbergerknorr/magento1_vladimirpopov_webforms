<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fields_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // add store switcher
        if (!Mage::app()->isSingleStoreMode() && $this->getRequest()->getParam('id')) {
            $store_switcher = $this->getLayout()->createBlock('adminhtml/store_switcher', 'store_switcher');
            $store_switcher->setDefaultStoreName($this->__('Default Values'));

            $this->getLayout()->getBlock("left")->append(
                $store_switcher
            );

        }
        // add scripts
        $js = $this->getLayout()->createBlock('core/template', 'webforms_js', array(
            'template' => 'webforms/js.phtml',
            'tabs_block' => 'webforms_fields_tabs'
        ));

        $this->getLayout()->getBlock('content')->append(
            $js
        );

        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE')
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
            {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
                $this->_formScripts[] = "
					function toggleEditor() {
						if (tinyMCE.getInstanceById('page_content') == null) {
							tinyMCE.execCommand('mceAddControl', false, 'content');
						} else {
							tinyMCE.execCommand('mceRemoveControl', false, 'content');
						}
					}";
            }
    }

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'webforms';
        $this->_controller = 'adminhtml_fields';

        if (strstr(Mage::registry('field')->getType(), 'select')) {
            $this->_addButton('logic', array(
                'label' => Mage::helper('webforms')->__('Add Logic'),
                'class' => 'add',
                'onclick' => 'setLocation(\'' . $this->getAddLogicUrl() . '\')',
            ));
        }

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->getSaveAndContinueUrl() . '\')',
            'class' => 'save',
        ), -100);

    }

    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true,
            'back' => 'edit',
            'active_tab' => '{{tab_id}}'
        ));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/webforms_webforms/save', array('webform_id' => Mage::registry('webforms_data')->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/webforms_webforms/edit', array('id' => Mage::registry('webforms_data')->getId()));
    }

    public function getAddLogicUrl()
    {
        return $this->getUrl('*/webforms_logic/new', array('field_id' => Mage::registry('field')->getId()));
    }

    public function getHeaderText()
    {
        if (Mage::registry('field') && Mage::registry('field')->getId()) {
            return Mage::helper('webforms')->__("Edit '%s' Field - %s", $this->htmlEscape(Mage::registry('field')->getName()), $this->htmlEscape($this->htmlEscape(Mage::registry('webforms_data')->getName())));
        } else {
            return Mage::helper('webforms')->__('Add Field - %s', $this->htmlEscape(Mage::registry('webforms_data')->getName()));
        }
    }

}