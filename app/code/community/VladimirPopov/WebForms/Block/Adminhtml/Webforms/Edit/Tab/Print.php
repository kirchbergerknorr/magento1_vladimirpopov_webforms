<?php
require_once(Mage::getBaseDir('lib').'/Webforms/mpdf.php');

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Print
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('form');
        $form->setDataObject(Mage::registry('webforms_data'));

        $this->setForm($form);

        if (!@class_exists('mPDF')) {
            $fieldset = $form->addFieldset('print_warning', array(
                'legend' => Mage::helper('webforms')->__('Warning'),
                'class' => 'error'
            ));
            $fieldset->setHtmlContent($this->__('Printing is disabled. Please install mPDF library. <a href=\'http://mageme.com/downloads/mpdf.zip\'>Click here to download</a>'));
        }

        $fieldset = $form->addFieldset('webforms_print', array(
            'legend' => Mage::helper('webforms')->__('Admin Print Settings')
        ));

        $fieldset->addField('print_template_id', 'select', array(
            'label' => Mage::helper('webforms')->__('Admin print template'),
            'name' => 'print_template_id',
            'note' => Mage::helper('webforms')->__('Select template for printable version of submission results for admin'),
            'values' => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset->addField('print_attach_to_email', 'select', array(
            'label' => Mage::helper('webforms')->__('Attach PDF to admin email'),
            'name' => 'print_attach_to_email',
            'note' => Mage::helper('webforms')->__('Attach printable version of the result to email'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset = $form->addFieldset('webforms_print_customer', array(
            'legend' => Mage::helper('webforms')->__('Customer Print Settings')
        ));

        $fieldset->addField('customer_print_template_id', 'select', array(
            'label' => Mage::helper('webforms')->__('Customer print template'),
            'name' => 'customer_print_template_id',
            'note' => Mage::helper('webforms')->__('Select template for printable version of submission results for customer'),
            'values' => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset->addField('customer_print_attach_to_email', 'select', array(
            'label' => Mage::helper('webforms')->__('Attach PDF to customer email'),
            'name' => 'customer_print_attach_to_email',
            'note' => Mage::helper('webforms')->__('Attach printable version of the result to customer email'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset = $form->addFieldset('webforms_print_approval', array(
            'legend' => Mage::helper('webforms')->__('Approval Print Settings')
        ));

        $fieldset->addField('approved_print_template_id', 'select', array(
            'label' => Mage::helper('webforms')->__('Approved result print template'),
            'name' => 'approved_print_template_id',
            'note' => Mage::helper('webforms')->__('Select template for printable version of submission results for approved result'),
            'values' => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset->addField('approved_print_attach_to_email', 'select', array(
            'label' => Mage::helper('webforms')->__('Attach PDF to approved result email'),
            'name' => 'approved_print_attach_to_email',
            'note' => Mage::helper('webforms')->__('Attach printable version of the result to customer approved result email'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('completed_print_template_id', 'select', array(
            'label' => Mage::helper('webforms')->__('Completed result print template'),
            'name' => 'completed_print_template_id',
            'note' => Mage::helper('webforms')->__('Select template for printable version of submission results for completed result'),
            'values' => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset->addField('completed_print_attach_to_email', 'select', array(
            'label' => Mage::helper('webforms')->__('Attach PDF to completed result email'),
            'name' => 'completed_print_attach_to_email',
            'note' => Mage::helper('webforms')->__('Attach printable version of the result to customer completed result email'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        if (Mage::getSingleton('adminhtml/session')->getWebFormsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif (Mage::registry('webforms_data')) {
            $form->setValues(Mage::registry('webforms_data')->getData());
        }

        return parent::_prepareForm();
    }
}