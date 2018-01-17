<?php

class VladimirPopov_WebForms_Block_Adminhtml_Results_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $result = Mage::registry('result');
        /** @var VladimirPopov_WebForms_Model_Webforms $webform */
        $webform = Mage::registry('webform');

        $form = new Varien_Data_Form(array
        (
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'method' => 'post',
        ));
        $form->setFieldNameSuffix('result');

        if ($result->getId())
            $fieldset = $form->addFieldset('result_info', array('legend' => Mage::helper('webforms')->__('Result # %s', $result->getId())));
        else
            $fieldset = $form->addFieldset('result_info', array('legend' => Mage::helper('webforms')->__('New Result')));

        $customer_id = $result->getCustomerId();
        $customer_ip = long2ip($result->getData('customer_ip'));

        $result->addData(array(
            'info_customer_ip' => $customer_ip,
            'info_created_time' => Mage::helper('core')->formatDate($result->getCreatedTime(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true),
            'info_webform_name' => $webform->getName(),
        ));


        $fieldset->addField('info_webform_name', 'link', array(
            'id' => 'info_webform_name',
            'style' => 'font-weight:bold',
            'href' => $this->getUrl('*/webforms_webforms/edit', array('id' => $webform->getId())),
            'label' => Mage::helper('webforms')->__('Web-form'),
        ));

        if ($result->getId())
            $fieldset->addField('info_created_time', 'label', array(
                'id' => 'info_created_time',
                'bold' => true,
                'label' => Mage::helper('webforms')->__('Result Date'),
            ));

        $fieldset->addType('customer', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_customer'));

        $fieldset->addField(
            'customer', 'customer',
            array(
                'label' => $this->__('Customer'),
                'name' => 'customer',
                'value' => $customer_id
            )
        );

        $fieldset->addField(
            'store_id', 'select',
            array(
                'name' => 'store_id',
                'label' => $this->__('Store View'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                'required' => true,
            )
        );

        if ($result->getId())
            $fieldset->addField('info_customer_ip', 'label', array(
                'id' => 'info_customer_ip',
                'bold' => true,
                'label' => Mage::helper('webforms')->__('Sent from IP'),
            ));

        $editor_type = 'textarea';
        $editor_config = '';
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE') {

            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
            );

            $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
            $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
            $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');

            $wysiwygConfig["add_widgets"] = false;
            $wysiwygConfig["add_variables"] = false;
            $wysiwygConfig["widget_plugin_src"] = false;
            $wysiwygConfig->setData("plugins", array());

            $editor_type = 'editor';
            $editor_config = $wysiwygConfig;
        }

        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true, $result);

        foreach ($fields_to_fieldsets as $fs_id => $fs_data) {
            $legend = "";
            if (!empty($fs_data['name'])) $legend = $fs_data['name'];

            // check logic visibility
            $fieldset = $form->addFieldset('fs_' . $fs_id, array(
                'legend' => $legend,
                'fieldset_container_id' => 'fieldset_' . $fs_id . '_container'
            ));

            foreach ($fs_data['fields'] as $field) {
                /** @var VladimirPopov_WebForms_Model_Fields $type */
                $type = 'text';
                $config = array
                (
                    'name' => 'field[' . $field->getId() . ']',
                    'label' => $field->getName(),
                    'container_id' => 'field_' . $field->getId() . '_container',
                    'required' => $field->getRequired(),
                    'container_style' => 'display:none'
                );

                $dateFormatIso = Mage::app()->getLocale()->getDateFormat($field->getDateType());
                $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat($field->getDateType());

                switch ($field->getType()) {
                    case 'textarea':
                        $type = 'textarea';
                        break;
                    case 'hidden':
                        $type = 'text';
                        break;
                    case 'wysiwyg':
                        $type = $editor_type;
                        $config['config'] = $editor_config;
                        break;

                    case 'date':
                    case 'date/dob':
                        $type = 'date';
                        $config['format'] = $dateFormatIso;
                        $config['locale'] = Mage::app()->getLocale()->getLocaleCode();
                        $config['image'] = $this->getSkinUrl('images/grid-cal.gif');
                        break;

                    case 'datetime':
                        $type = 'date';
                        $config['time'] = true;
                        $config['format'] = $dateTimeFormatIso;
                        $config['image'] = $this->getSkinUrl('images/grid-cal.gif');
                        break;

                    case 'select/radio':
                        $type = 'select';
                        $config['required'] = false;
                        $config['values'] = $field->getOptionsArray();
                        break;

                    case 'select/checkbox':
                        $type = 'checkboxes';
                        $value = explode("\n", $result->getData('field_' . $field->getId()));
                        $result->setData('field_' . $field->getId(), $value);
                        $config['options'] = $field->getSelectOptions();
                        $config['name'] = 'field[' . $field->getId() . '][]';
                        break;

                    case 'select':
                        $type = 'select';

                        $config['options'] = $field->getSelectOptions();

                        if($field->getValue('multiselect')) {
                            $type = 'multiselect';
                            $value = explode("\n", $result->getData('field_' . $field->getId()));
                            $result->setData('field_' . $field->getId(), $value);
                            $config['values'] = $field->getOptionsArray();
                        }
                        break;

                    case 'subscribe':
                        $type = 'select';
                        $config['options'] = Mage::getModel('adminhtml/system_config_source_yesno')->toArray();
                        break;

                    case 'select/contact':
                        $type = 'select';
                        $config['options'] = $field->getSelectOptions(false);
                        break;

                    case 'stars':
                        $type = 'select';
                        $config['options'] = $field->getStarsOptions();
                        break;

                    case 'file':
                        $type = 'file';
                        $config['field_id'] = $field->getId();
                        $config['result_id'] = $result->getId();
                        $config['url'] = $result->getFilePath($field->getId());
                        $config['dropzone_name'] = $config['name'];
                        $config['name'] = 'file_' . $field->getId();

                        $config['dropzone'] = $field->getValue('dropzone');
                        $config['dropzone_text'] = $field->getValue('dropzone_text');
                        $config['dropzone_maxfiles'] = $field->getValue('dropzone_maxfiles');
                        $config['allowed_size'] = $webform->getUploadLimit($field->getType());
                        $config['allowed_extensions'] = $field->getAllowedExtensions();
                        $config['restricted_extensions'] = $field->getRestrictedExtensions();
                        break;

                    case 'image':
                        $type = 'image';
                        $config['field_id'] = $field->getId();
                        $config['result_id'] = $result->getId();
                        $config['url'] = $result->getFilePath($field->getId());
                        $config['name'] = 'file_' . $field->getId();
                        $fieldset->addType('image', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_image'));

                        $config['dropzone'] = $field->getValue('dropzone');
                        $config['dropzone_text'] = $field->getValue('dropzone_text');
                        $config['dropzone_maxfiles'] = $field->getValue('dropzone_maxfiles');
                        $config['allowed_size'] = $webform->getUploadLimit($field->getType());
                        $config['allowed_extensions'] = $field->getAllowedExtensions();
                        $config['restricted_extensions'] = $field->getRestrictedExtensions();
                        break;

                    case 'html':
                        $type = 'label';
                        $config['label'] = false;
                        $config['after_element_html'] = $field->getValue('html');
                        break;

                    case 'country':
                        $type = 'select';
                        $config['values'] = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
                        break;
                }
                $config['type'] = $type;
                $config = new Varien_Object($config);
                $fieldset->addType('checkboxes', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_checkboxes'));
                $fieldset->addType('file', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_file'));

                Mage::dispatchEvent('webforms_block_adminhtml_results_edit_form_prepare_layout_field', array('form' => $form, 'fieldset' => $fieldset, 'field' => $field, 'config' => $config));
                $fieldset->addField('field_' . $field->getId(), $config->getData('type'), $config->getData());
            }
        }

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getFormData());
            Mage::getSingleton('adminhtml/session')->setFormData(false);
        } elseif (Mage::registry('result')) {
            $form->addValues(Mage::registry('result')->getData());
        }

        $form->addField('result_id', 'hidden', array
        (
            'name' => 'result_id',
            'value' => $result->getId(),
        ));

        $form->addField('webform_id', 'hidden', array
        (
            'name' => 'webform_id',
            'value' => $webform->getId(),
        ));

        $form->addField('saveandcontinue', 'hidden', array('name' => 'saveandcontinue',));

        $form->setUseContainer(true);

        $this->setForm($form);

        $this->getForm()->setData('enctype', 'multipart/form-data');

        return $this;
    }

    public function getCustomerUrl($customer_id)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $customer_id, '_current' => false));
    }

    public function getStoreName()
    {
        $storeId = Mage::registry('result')->getStoreId();
        if (is_null($storeId)) {
            $deleted = Mage::helper('adminhtml')->__('[deleted]');
            return $deleted;
        }
        $store = Mage::app()->getStore($storeId);
        $name = array(
            $store->getWebsite()->getName(),
            $store->getGroup()->getName(),
            $store->getName()
        );
        return implode('<br>', $name);
    }
}
