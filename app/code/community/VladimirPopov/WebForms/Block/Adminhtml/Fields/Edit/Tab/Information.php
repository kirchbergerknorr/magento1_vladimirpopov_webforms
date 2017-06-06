<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fields_Edit_Tab_Information
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout(){
        
        parent::_prepareLayout();
    }   
    
    protected function _prepareForm()
    {
        $model = Mage::getModel('webforms/fields');
        $form = new Varien_Data_Form();
        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('field');
        $form->setDataObject(Mage::registry('field'));

        $this->setForm($form);
        
        $fieldset = $form->addFieldset('webforms_form',array(
            'legend' => Mage::helper('webforms')->__('Information')
        ));
        
        $fieldset->addField('name','text',array(
            'label' => Mage::helper('webforms')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name'
        ));

        $type = $fieldset->addField('type', 'select', array(
            'label'     => Mage::helper('webforms')->__('Type'),
            'title'     => Mage::helper('webforms')->__('Type'),
            'name'      => 'type',
            'required'  => false,
            'options'   => $model->getFieldTypes(),
        ));

        $fieldset->addField('code','text',array(
            'label' => Mage::helper('webforms')->__('Code'),
            'name' => 'code',
            'note' => Mage::helper('webforms')->__('Code is used to help identify this field in scripts'),
        ));

        $result_label = $fieldset->addField('result_label','text',array(
            'label' => Mage::helper('webforms')->__('Result label'),
            'required' => false,
            'name' => 'result_label',
            'note' => Mage::helper('webforms')->__('Result label will be used on results page. Use it to shorten long question fields')
        ));

        $hint = $fieldset->addField('hint','text',array(
            'label' => Mage::helper('webforms')->__('Hint'),
            'required' => false,
            'name' => 'hint',
            'note' => Mage::helper('webforms')->__('Hint message will appear in the input and disappear on the focus'),
        ));

        $hint_email = $fieldset->addField('hint_email','text',array(
            'label' => Mage::helper('webforms')->__('Hint'),
            'required' => false,
            'name' => 'hint_email',
            'note' => Mage::helper('webforms')->__('Hint message will appear in the input and disappear on the focus'),
        ));

        $hint_url = $fieldset->addField('hint_url','text',array(
            'label' => Mage::helper('webforms')->__('Hint'),
            'required' => false,
            'name' => 'hint_url',
            'note' => Mage::helper('webforms')->__('Hint message will appear in the input and disappear on the focus'),
        ));

        $hint_textarea = $fieldset->addField('hint_textarea','text',array(
            'label' => Mage::helper('webforms')->__('Hint'),
            'required' => false,
            'name' => 'hint_textarea',
            'note' => Mage::helper('webforms')->__('Hint message will appear in the input and disappear on the focus'),
        ));

        $comment = $fieldset->addField('comment','textarea',array(
            'label' => Mage::helper('webforms')->__('Comment'),
            'required' => false,
            'name' => 'comment',
            'style' => 'height:10em;',
            'note' => Mage::helper('webforms')->__('This text will appear under the input field.<br>Use <i>{{tooltip}}text{{/tooltip}}</i> to add tooltip to field name.<br>Use <i>{{tooltip val=&quot;Option&quot;}}text{{/tooltip}}</i> to add tooltip to checkbox or radio label.'),
            'wysiwyg' => true,
        ));

        $fieldsetsOptions  = Mage::registry('webforms_data')->getFieldsetsOptionsArray();
        if(count($fieldsetsOptions)>1){
            $fieldset->addField('fieldset_id', 'select', array(
                'label'     => Mage::helper('webforms')->__('Field set'),
                'title'     => Mage::helper('webforms')->__('Field set'),
                'name'      => 'fieldset_id',
                'required'  => false,
                'options'   => $fieldsetsOptions,
            ));
        }

        $autocomplete_choices = $fieldset->addField('value_autocomplete_choices','textarea',array(
            'label' => Mage::helper('webforms')->__('Auto-complete choices'),
            'required' => false,
            'name' => 'value[autocomplete_choices]',
            'note' => Mage::helper('webforms')->__('Drop-down list of auto-complete choices. Values should be separated with new line'),
        ));

        $options = $fieldset->addField('value_options','textarea',array(
            'label' => Mage::helper('webforms')->__('Options'),
            'required' => false,
            'name' => 'value[options]',
            'note' => Mage::helper('webforms')->__('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{null}}</i> to create option without value</i><br>Use <i>Option Text {{val VALUE}}</i> to set different option value<br>Use <i>Option Text {{disabled}}</i> to create disabled option'),
        ));

        $options_radio = $fieldset->addField('value_options_radio','textarea',array(
            'label' => Mage::helper('webforms')->__('Options'),
            'required' => false,
            'name' => 'value[options_radio]',
            'note' => Mage::helper('webforms')->__('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{null}}</i> to create option without value</i><br>Use <i>Option Text {{val VALUE}}</i> to set different option value'),
        ));

        $options_checkbox = $fieldset->addField('value_options_checkbox','textarea',array(
            'label' => Mage::helper('webforms')->__('Options'),
            'required' => false,
            'name' => 'value[options_checkbox]',
            'note' => Mage::helper('webforms')->__('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{null}}</i> to create option without value</i><br>Use <i>Option Text {{val VALUE}}</i> to set different option value'),
        ));

        $options_contact = $fieldset->addField('value_options_contact','textarea',array(
            'label' => Mage::helper('webforms')->__('Options'),
            'required' => false,
            'name' => 'value[options_contact]',
            'note' => Mage::helper('webforms')->__('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Options format:<br><i>Site Admin &lt;admin@mysite.com&gt;<br>Sales &lt;sales@mysite.com&gt;</i>'),
        ));

        $text_value = $fieldset->addField('value_text','text',array(
            'label' => Mage::helper('webforms')->__('Field value'),
            'name' => 'value[text]',
            'note' => Mage::helper('webforms')->__('Following codes pre-fill data for registered customer:<br>{{email}} - customer e-mail address<br>{{firstname}} - first name of the customer<br>{{lastname}} - last name of the customer<br>{{company}} - billing profile company<br>{{city}} - billing profile city<br>{{street}} - billing profile street<br>{{country_id}} - billing profile country 2 symbol code<br>{{region}} - billing profile region<br>{{postcode}} - billing profile postcode<br>{{telephone}} - billing profile telephone<br>{{fax}} - billing profile fax')
        ));

        $text_value_email = $fieldset->addField('value_text_email','text',array(
            'label' => Mage::helper('webforms')->__('Field value'),
            'name' => 'value[text_email]',
            'note' => Mage::helper('webforms')->__('Following codes pre-fill data for registered customer:<br>{{email}} - customer e-mail address<br>{{firstname}} - first name of the customer<br>{{lastname}} - last name of the customer<br>{{company}} - billing profile company<br>{{city}} - billing profile city<br>{{street}} - billing profile street<br>{{country_id}} - billing profile country 2 symbol code<br>{{region}} - billing profile region<br>{{postcode}} - billing profile postcode<br>{{telephone}} - billing profile telephone<br>{{fax}} - billing profile fax')
        ));

        $assign_customer_id_by_email = $fieldset->addField('value_assign_customer_id_by_email','select',array(
            'label' => Mage::helper('webforms')->__('Assign Customer ID automatically'),
            'name' => 'value[assign_customer_id_by_email]',
            'note' => Mage::helper('webforms')->__('Assign Customer ID automatically if e-mail address matches customer account in the database'),
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $text_value_url = $fieldset->addField('value_text_url','text',array(
            'label' => Mage::helper('webforms')->__('Field value'),
            'name' => 'value[text_url]',
            'note' => Mage::helper('webforms')->__('Following codes pre-fill data for registered customer:<br>{{email}} - customer e-mail address<br>{{firstname}} - first name of the customer<br>{{lastname}} - last name of the customer<br>{{company}} - billing profile company<br>{{city}} - billing profile city<br>{{street}} - billing profile street<br>{{country_id}} - billing profile country 2 symbol code<br>{{region}} - billing profile region<br>{{postcode}} - billing profile postcode<br>{{telephone}} - billing profile telephone<br>{{fax}} - billing profile fax')
        ));

        $textarea_value = $fieldset->addField('value_textarea','textarea',array(
            'label' => Mage::helper('webforms')->__('Field value'),
            'name' => 'value[textarea]',
            'note' => Mage::helper('webforms')->__('Following codes pre-fill data for registered customer:<br>{{email}} - customer e-mail address<br>{{firstname}} - first name of the customer<br>{{lastname}} - last name of the customer<br>{{company}} - billing profile company<br>{{city}} - billing profile city<br>{{street}} - billing profile street<br>{{country_id}} - billing profile country 2 symbol code<br>{{region}} - billing profile region<br>{{postcode}} - billing profile postcode<br>{{telephone}} - billing profile telephone<br>{{fax}} - billing profile fax')
        ));

        $number_min = $fieldset->addField('value_number_min','text',array(
            'label' => Mage::helper('webforms')->__('Minimum value'),
            'name' => 'value[number_min]',
            'note' => Mage::helper('webforms')->__('Minimum integer value that can be entered'),
            'class' => 'validate-number'
        ));

        $number_max = $fieldset->addField('value_number_max','text',array(
            'label' => Mage::helper('webforms')->__('Maximum value'),
            'name' => 'value[number_max]',
            'note' => Mage::helper('webforms')->__('Maximum integer value that can be entered'),
            'class' => 'validate-number'
        ));

        $stars_init = $fieldset->addField('value_stars_init','text',array(
            'label' => Mage::helper('webforms')->__('Number of stars selected by default'),
            'note' => Mage::helper('webforms')->__('3 stars are selected by default'),
            'name' => 'value[stars_init]',
            'class' => 'validate-number'
        ));

        $stars_max = $fieldset->addField('value_stars_max','text',array(
            'label' => Mage::helper('webforms')->__('Total amount of stars'),
            'name' => 'value[stars_max]',
            'note' => Mage::helper('webforms')->__('5 stars are available by default'),
            'class' => 'validate-number'
        ));

        $newsletter_label = $fieldset->addField('value_newsletter_label','text',array(
            'label' => Mage::helper('webforms')->__('Newsletter subscription checkbox label'),
            'name' => 'value[newsletter_label]',
            'note' => Mage::helper('webforms')->__('Overwrite default text &quot;Sign Up for Newsletter&quot;<br>Use <i>^Sign Up for Newsletter</i> to check by default'),
        ));

        $allowed_extensions = $fieldset->addField('value_allowed_extensions','textarea',array(
            'label' => Mage::helper('webforms')->__('Allowed file extensions'),
            'name' => 'value[allowed_extensions]',
            'note' => Mage::helper('webforms')->__('Specify allowed file extensions separated by newline. Example:<br><i>doc<br>txt<br>pdf</i>')
        ));

        $editor_type = 'textarea';
        $config = '';
        if ((float) substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE') {


            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
            );


            $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
            $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
            $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
            $wysiwygConfig["widget_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index');


            $plugins = $wysiwygConfig->getPlugins();
            for ($i = 0; $i < count($plugins); $i++) {
                if ($plugins[$i]["name"] == "magentovariable") {
                    $plugins[$i]["options"]["url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
                    $plugins[$i]["options"]["onclick"]["subject"] = 'MagentovariablePlugin.loadChooser(\'' . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin') . '\', \'{{html_id}}\');';
                }
            }
            $wysiwygConfig->setPlugins($plugins);


            $editor_type = 'editor';
            $config = $wysiwygConfig;
        }

        $html_content = $fieldset->addField('value_html',$editor_type,array(
            'label' => Mage::helper('webforms')->__('HTML content'),
            'name' => 'value[html]',
            'config' => $config
        ));

        $hidden_value = $fieldset->addField('value_hidden','textarea',array(
            'label' => Mage::helper('webforms')->__('Hidden field value'),
            'name' => 'value[hidden]',
            'note' => Mage::helper('webforms')->__("You can use variables to store dynamic information. Example:<br><i>{{var product.sku}}<br>{{var category.name}}<br>{{var customer.email}}<br>{{var url}}</i>")
        ));

        $image_resize = $fieldset->addField('value_image_resize','select',array(
            'label' => Mage::helper('webforms')->__('Resize uploaded image'),
            'name' => 'value[image_resize]',
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $image_resize_width = $fieldset->addField('value_image_resize_width','text',array(
            'label' => Mage::helper('webforms')->__('Maximum width'),
            'name' => 'value[image_resize_width]',
            'class' => 'validate-number'
        ));

        $image_resize_height = $fieldset->addField('value_image_resize_height','text',array(
            'label' => Mage::helper('webforms')->__('Maximum height'),
            'name' => 'value[image_resize_height]',
            'class' => 'validate-number'
        ));

        $fieldset->addField('email_subject', 'select', array(
            'label'     => Mage::helper('webforms')->__('Use field value as e-mail subject'),
            'title'     => Mage::helper('webforms')->__('Use field value as e-mail subject'),
            'name'      => 'email_subject',
            'note'      => Mage::helper('webforms')->__('This field value will be used as a subject in notification e-mail'),
            'required'  => false,
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        
        $required = $fieldset->addField('required', 'select', array(
            'label'     => Mage::helper('webforms')->__('Required'),
            'title'     => Mage::helper('webforms')->__('Required'),
            'name'      => 'required',
            'required'  => false,
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $validation_advice = $fieldset->addField('validation_advice', 'text',array(
            'label'     => Mage::helper('webforms')->__('Custom validation advice'),
            'name'      => 'validation_advice',
            'note'      => Mage::helper('webforms')->__('Set custom text for the validation error message. If empty <b>&quot;This is a required field.&quot;</b> will be used'),
        ));

        $fieldset->addField('position','text',array(
            'label' => Mage::helper('webforms')->__('Position'),
            'required' => true,
            'name' => 'position',
            'note' => Mage::helper('webforms')->__('Field position in the form relative to field set'),
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('webforms')->__('Status'),
            'title'     => Mage::helper('webforms')->__('Status'),
            'name'      => 'is_active',
            'note'      => Mage::helper('webforms')->__('If assigned field set is not active the field won`t be displayed'),
            'required'  => false,
            'options'   => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
        ));
        
        $form->addField('webform_id', 'hidden', array(
            'name'      => 'webform_id',
            'value'   => 1,
        ));
        
        $form->addField('saveandcontinue','hidden',array(
            'name' => 'saveandcontinue'
        ));

        Mage::dispatchEvent('webforms_adminhtml_fields_edit_tab_information_prepare_form', array('form' => $form, 'fieldset' => $fieldset));
        
        if (!$model->getId()) {
            $model->setData('is_active', '0');
        }
        
        if(Mage::getSingleton('adminhtml/session')->getWebFormsData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif(Mage::registry('field')){
            $form->setValues(Mage::registry('field')->getData());
        } 

        $webformId = $this->getRequest()->getParam('webform_id');
        // set default field values
        if(!Mage::registry('field')->getId()){
            $form->setValues(array(
                'webform_id' => $webformId,
                'position' => $model->getResource()->getNextPosition($webformId)
            ));
        }

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence','fields_information_dependence')
            ->addFieldMap($type->getHtmlId(), $type->getName())
            ->addFieldMap($required->getHtmlId(), $required->getName())
            ->addFieldMap($number_min->getHtmlId(), $number_min->getName())
            ->addFieldMap($number_max->getHtmlId(), $number_max->getName())
            ->addFieldMap($validation_advice->getHtmlId(), $validation_advice->getName())
            ->addFieldMap($text_value->getHtmlId(), $text_value->getName())
            ->addFieldMap($text_value_email->getHtmlId(), $text_value_email->getName())
            ->addFieldMap($text_value_url->getHtmlId(), $text_value_url->getName())
            ->addFieldMap($options->getHtmlId(), $options->getName())
            ->addFieldMap($options_radio->getHtmlId(), $options_radio->getName())
            ->addFieldMap($options_checkbox->getHtmlId(), $options_checkbox->getName())
            ->addFieldMap($options_contact->getHtmlId(), $options_contact->getName())
            ->addFieldMap($textarea_value->getHtmlId(), $textarea_value->getName())
            ->addFieldMap($newsletter_label->getHtmlId(), $newsletter_label->getName())
            ->addFieldMap($stars_init->getHtmlId(), $stars_init->getName())
            ->addFieldMap($stars_max->getHtmlId(), $stars_max->getName())
            ->addFieldMap($hint->getHtmlId(), $hint->getName())
            ->addFieldMap($hint_email->getHtmlId(), $hint_email->getName())
            ->addFieldMap($hint_url->getHtmlId(), $hint_url->getName())
            ->addFieldMap($hint_textarea->getHtmlId(), $hint_textarea->getName())
            ->addFieldMap($allowed_extensions->getHtmlId(), $allowed_extensions->getName())
            ->addFieldMap($html_content->getHtmlId(), $html_content->getName())
            ->addFieldMap($hidden_value->getHtmlId(), $hidden_value->getName())
            ->addFieldMap($image_resize->getHtmlId(), $image_resize->getName())
            ->addFieldMap($image_resize_width->getHtmlId(), $image_resize_width->getName())
            ->addFieldMap($image_resize_height->getHtmlId(), $image_resize_height->getName())
            ->addFieldMap($assign_customer_id_by_email->getHtmlId(), $assign_customer_id_by_email->getName())
            ->addFieldMap($autocomplete_choices->getHtmlId(), $autocomplete_choices->getName())
            ->addFieldDependence(
                $hint->getName(),
                $type->getName(),
                'text'
            )
            ->addFieldDependence(
                $hint_email->getName(),
                $type->getName(),
                'email'
            )
            ->addFieldDependence(
                $assign_customer_id_by_email->getName(),
                $type->getName(),
                'email'
            )
            ->addFieldDependence(
                $hint_url->getName(),
                $type->getName(),
                'url'
            )
            ->addFieldDependence(
                $hint_textarea->getName(),
                $type->getName(),
                'textarea'
            )
            ->addFieldDependence(
                $number_min->getName(),
                $type->getName(),
                'number'
            )
            ->addFieldDependence(
                $number_max->getName(),
                $type->getName(),
                'number'
            )
            ->addFieldDependence(
                $text_value->getName(),
                $type->getName(),
                'text'
            )
            ->addFieldDependence(
                $text_value_email->getName(),
                $type->getName(),
                'email'
            )
            ->addFieldDependence(
                $text_value_url->getName(),
                $type->getName(),
                'url'
            )
            ->addFieldDependence(
                $textarea_value->getName(),
                $type->getName(),
                'textarea'
            )
            ->addFieldDependence(
                $newsletter_label->getName(),
                $type->getName(),
                'subscribe'
            )
            ->addFieldDependence(
                $options->getName(),
                $type->getName(),
                'select'
            )
            ->addFieldDependence(
                $options_radio->getName(),
                $type->getName(),
                'select/radio'
            )
            ->addFieldDependence(
                $options_checkbox->getName(),
                $type->getName(),
                'select/checkbox'
            )
            ->addFieldDependence(
                $options_contact->getName(),
                $type->getName(),
                'select/contact'
            )
            ->addFieldDependence(
                $stars_init->getName(),
                $type->getName(),
                'stars'
            )
            ->addFieldDependence(
                $stars_max->getName(),
                $type->getName(),
                'stars'
            )
            ->addFieldDependence(
                $allowed_extensions->getName(),
                $type->getName(),
                'file'
            )
            ->addFieldDependence(
                $html_content->getName(),
                $type->getName(),
                'html'
            )
            ->addFieldDependence(
                $hidden_value->getName(),
                $type->getName(),
                'hidden'
            )
            ->addFieldDependence(
                $image_resize->getName(),
                $type->getName(),
                'image'
            )
            ->addFieldDependence(
                $image_resize_width->getName(),
                $type->getName(),
                'image'
            )
            ->addFieldDependence(
                $image_resize_height->getName(),
                $type->getName(),
                'image'
            )
            ->addFieldDependence(
                $validation_advice->getName(),
                $required->getName(),
                '1'
            )
            ->addFieldDependence(
                $autocomplete_choices->getName(),
                $type->getName(),
                'autocomplete'
            )

        );
    
        return parent::_prepareForm();
    }
}  
