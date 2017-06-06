<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Logic_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array(
                'id' => $this->getRequest()->getParam('id'),
                'webform_id' => $this->getRequest()->getParam('webform_id'),
                'store' => $this->getRequest()->getParam('store'))),
            'method' => 'post',
        ));

        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('logic');
        $form->setDataObject(Mage::registry('logic'));

        $fieldset = $form->addFieldset('fieldset_information', array(
            'legend' => Mage::helper('webforms')->__('Logic Rule')
        ));

        $fieldset->addField('logic_condition', 'select', array(
            'label' => Mage::helper('webforms')->__('Condition'),
            'name' => 'logic_condition',
            'options' => Mage::getModel('webforms/logic_condition')->getOptions(),
        ));

        $fieldset->addField('value', 'multiselect', array(
            'label' => Mage::helper('webforms')->__('Trigger value(s)'),
            'required' => true,
            'name' => 'value',
            'note' => Mage::helper('webforms')->__('Select one or multiple trigger values.<br>Please, configure for each locale <b>Store View</b>.'),
            'values' => Mage::registry('field')->getOptionsArray()
        ));

        $fieldset->addField('action', 'select', array(
            'label' => Mage::helper('webforms')->__('Action'),
            'name' => 'action',
            'options' => Mage::getModel('webforms/logic_action')->getOptions(),
            'note' => Mage::helper('webforms')->__('Action to perform with target elements'),
        ));

        $fieldset->addField('target', 'multiselect', array(
            'label' => Mage::helper('webforms')->__('Target element(s)'),
            'required' => true,
            'name' => 'target',
            'note' => Mage::helper('webforms')->__('Select one or multiple target elements'),
            'values' => Mage::registry('field')->getLogicTargetOptionsArray()
        ));

        if (Mage::registry('field')->getType() == 'select/checkbox') {
            $fieldset->addField('aggregation', 'select', array(
                'label' => Mage::helper('webforms')->__('Logic aggregation'),
                'name' => 'aggregation',
                'options' => Mage::getModel('webforms/logic_aggregation')->getOptions()
            ));
        }

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('webforms')->__('Status'),
            'title' => Mage::helper('webforms')->__('Status'),
            'name' => 'is_active',
            'options' => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
        ));

        $form->addField('field_id', 'hidden', array(
            'name' => 'field_id',
        ));

        $form->addField('saveandcontinue', 'hidden', array(
            'name' => 'saveandcontinue'
        ));

        if (Mage::getSingleton('adminhtml/session')->getWebFormsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif (Mage::registry('logic')) {
            $form->setValues(Mage::registry('logic')->getData());
        }

        $form->setUseContainer(true);

        Mage::dispatchEvent('webforms_adminhtml_logic_edit_form_prepare_layout', array('form' => $form));

        $this->setForm($form);

    }
}
