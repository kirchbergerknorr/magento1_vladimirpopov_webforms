<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Settings
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('form');
        $form->setDataObject(Mage::registry('webforms_data'));

        $this->setForm($form);

        $fieldset = $form->addFieldset('webforms_general', array(
            'legend' => Mage::helper('webforms')->__('General Settings')
        ));

        $fieldset->addField('accept_url_parameters', 'select', array(
            'label'    => Mage::helper('webforms')->__('Accept URL parameters'),
            'title'    => Mage::helper('webforms')->__('Accept URL parameters'),
            'name'     => 'accept_url_parameters',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('Accept URL parameters to set field values. Use field Code value as parameter name'),
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('survey', 'select', array(
            'label'    => Mage::helper('webforms')->__('Survey mode'),
            'title'    => Mage::helper('webforms')->__('Survey mode'),
            'name'     => 'survey',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('Survey mode allows filling up the form only one time'),
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('redirect_url', 'text', array(
            'label' => Mage::helper('webforms')->__('Redirect URL'),
            'title' => Mage::helper('webforms')->__('Redirect URL'),
            'name'  => 'redirect_url',
            'note'  => Mage::helper('webforms')->__('Redirect to specified url after successful submission'),
        ));

        $fieldset =  $form->addFieldset('webforms_approval', array(
            'legend' => Mage::helper('webforms')->__('Result Approval Settings')
        ));

        $approve = $fieldset->addField('approve', 'select', array(
            'label'    => Mage::helper('webforms')->__('Enable result approval controls'),
            'title'    => Mage::helper('webforms')->__('Enable result approval controls'),
            'name'     => 'approve',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('You can switch submission result status: pending, approved or not approved'),
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $email_result_approval = $fieldset->addField('email_result_approval', 'select', array(
            'label'    => Mage::helper('webforms')->__('Enable approval status notification'),
            'title'    => Mage::helper('webforms')->__('Enable approval status notification'),
            'name'     => 'email_result_approval',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('Send customer notification email on submission result status change'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $bcc_approval_email = $fieldset->addField('bcc_approval_email', 'text', array(
            'label' => Mage::helper('webforms')->__('Bcc e-mail address'),
            'note'  => Mage::helper('webforms')->__('Send blind carbon copy of notification to specified address. You can set multiple addresses comma-separated'),
            'name'  => 'bcc_approval_email'
        ));

        $email_result_notapproved_template = $fieldset->addField('email_result_notapproved_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Result NOT approved notification email template'),
            'title'    => Mage::helper('webforms')->__('Result NOT approved notification email template'),
            'name'     => 'email_result_notapproved_template_id',
            'required' => false,
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $email_result_approved_template = $fieldset->addField('email_result_approved_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Result approved notification email template'),
            'title'    => Mage::helper('webforms')->__('Result approved notification email template'),
            'name'     => 'email_result_approved_template_id',
            'required' => false,
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $email_result_completed_template = $fieldset->addField('email_result_completed_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Result completed notification email template'),
            'title'    => Mage::helper('webforms')->__('Result completed notification email template'),
            'name'     => 'email_result_completed_template_id',
            'required' => false,
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset = $form->addFieldset('webforms_captcha', array(
            'legend' => Mage::helper('webforms')->__('reCaptcha Settings')
        ));

        $fieldset->addField('captcha_mode', 'select', array(
            'label'    => Mage::helper('webforms')->__('Captcha mode'),
            'title'    => Mage::helper('webforms')->__('Captcha mode'),
            'name'     => 'captcha_mode',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('Default value is set in Forms Settings'),
            'values'   => Mage::getModel('webforms/captcha_mode')->toOptionArray(true),
        ));

        $fieldset = $form->addFieldset('webforms_files', array(
            'legend' => Mage::helper('webforms')->__('Files Settings')
        ));

        $fieldset->addField('files_upload_limit', 'text', array(
            'label' => Mage::helper('webforms')->__('Files upload limit'),
            'title' => Mage::helper('webforms')->__('Files upload limit'),
            'name'  => 'files_upload_limit',
            'class' => 'validate-number',
            'note'  => Mage::helper('webforms')->__('Maximum upload file size in kB'),
        ));

        $fieldset = $form->addFieldset('webforms_images', array(
            'legend' => Mage::helper('webforms')->__('Images Settings')
        ));

        $fieldset->addField('images_upload_limit', 'text', array(
            'label' => Mage::helper('webforms')->__('Images upload limit'),
            'title' => Mage::helper('webforms')->__('Images upload limit'),
            'class' => 'validate-number',
            'name'  => 'images_upload_limit',
            'note'  => Mage::helper('webforms')->__('Maximum upload image size in kB'),
        ));

//        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence','form_settings_dependence')
//            ->addFieldMap($approve->getHtmlId(), $approve->getName())
//            ->addFieldMap($email_result_approval->getHtmlId(), $email_result_approval->getName())
//            ->addFieldMap($email_result_approved_template->getHtmlId(),$email_result_approved_template->getName())
//            ->addFieldMap($email_result_notapproved_template->getHtmlId(), $email_result_notapproved_template->getName())
//            ->addFieldMap($email_result_completed_template->getHtmlId(), $email_result_completed_template->getName())
//            ->addFieldDependence(
//                $email_result_approval->getName(),
//                $approve->getName(),
//                1
//            )
//            ->addFieldDependence(
//                $email_result_approved_template->getName(),
//                $email_result_approval->getName(),
//                1
//            )
//            ->addFieldDependence(
//                $email_result_notapproved_template->getName(),
//                $email_result_approval->getName(),
//                1
//            )
//            ->addFieldDependence(
//                $email_result_completed_template->getName(),
//                $email_result_approval->getName(),
//                1
//            )
//        );

        Mage::dispatchEvent('webforms_adminhtml_webforms_edit_tab_settings_prepare_form', array('form' => $form, 'fieldset' => $fieldset));

        if (Mage::registry('webforms_data')->getData('files_upload_limit') == 0) {
            Mage::registry('webforms_data')->setData('files_upload_limit', '');
        }

        if (Mage::registry('webforms_data')->getData('images_upload_limit') == 0) {
            Mage::registry('webforms_data')->setData('images_upload_limit', '');
        }

        if (Mage::getSingleton('adminhtml/session')->getWebFormsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif (Mage::registry('webforms_data')) {
            $form->setValues(Mage::registry('webforms_data')->getData());
        }

        return parent::_prepareForm();
    }
}