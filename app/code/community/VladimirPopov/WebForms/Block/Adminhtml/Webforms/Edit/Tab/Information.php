<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Information
	extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout(){

		parent::_prepareLayout();
	}
	
	protected function _prepareForm()
	{
		$model = Mage::getModel('webforms/webforms');
		$form = new Varien_Data_Form();
		$renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
		$form->setFieldsetElementRenderer($renderer);
		$form->setFieldNameSuffix('form');
		$form->setDataObject(Mage::registry('webforms_data'));
		
		$this->setForm($form);
		$fieldset = $form->addFieldset('webforms_form',array(
			'legend' => Mage::helper('webforms')->__('Form Information')
		));
		
		$fieldset->addField('name','text',array(
			'label' => Mage::helper('webforms')->__('Name'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'name'
		));
		
		$fieldset->addField('code','text',array(
			'label' => Mage::helper('webforms')->__('Code'),
			'name' => 'code',
			'note' => Mage::helper('webforms')->__('Code is used to help identify this web-form in scripts'),
		));
		
		$editor_type = 'textarea';
		$style= '';
		$config = '';
		if((float)substr(Mage::getVersion(),0,3) > 1.3 && substr(Mage::getVersion(),0,5)!= '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE'){
			
			$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
				array('tab_id' => $this->getTabId())
			);

			$wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
			$wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
			$wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
            $wysiwygConfig["widget_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index');

            $plugins = $wysiwygConfig->getPlugins();
            for($i=0;$i<count($plugins); $i++){
                if($plugins[$i]["name"] == "magentovariable"){
                    $plugins[$i]["options"]["url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
                    $plugins[$i]["options"]["onclick"]["subject"] = 'MagentovariablePlugin.loadChooser(\''.Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin').'\', \'{{html_id}}\');';
                }
            }
            $wysiwygConfig->setPlugins($plugins);

			$editor_type='editor';
			$style = 'height:20em; width:50em;';
			$config = $wysiwygConfig;
		}
		
		$descField = $fieldset->addField('description',$editor_type,array(
			'label' => Mage::helper('webforms')->__('Description'),
			'required' => false,
			'name' => 'description',
			'style' => $style,
			'note' => Mage::helper('webforms')->__('This text will appear under the form name'),
			'wysiwyg' => true,
			'config' => $config,
		));
		
		$succField = $fieldset->addField('success_text',$editor_type,array(
			'label' => Mage::helper('webforms')->__('Success text'),
			'required' => false,
			'name' => 'success_text',
			'style' => $style,
			'note' => Mage::helper('webforms')->__('This text will be displayed after the form completion'),
			'wysiwyg' => true,
			'config' => $config,
		));
		if(isset($renderer)){
			$descField->setRenderer($renderer);
			$succField->setRenderer($renderer);
		}

        $fieldset->addField('submit_button_text','text',array(
            'label' => Mage::helper('webforms')->__('Submit button text'),
            'name' => 'submit_button_text',
            'note' => Mage::helper('webforms')->__('Set text for the submit button of the form. If empty the default value &quot;Submit&quot; will be used'),
        ));
		
		$fieldset->addField('menu', 'select', array(
			'label'     => Mage::helper('webforms')->__('Display form in admin menu'),
			'title'     => Mage::helper('webforms')->__('Display form in admin menu'),
			'name'      => 'menu',
			'note' => Mage::helper('webforms')->__('Show web-form in admin backend menu under Web-forms section'),
			'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
		));
		
		$fieldset->addField('is_active', 'select', array(
			'label'     => Mage::helper('webforms')->__('Status'),
			'title'     => Mage::helper('webforms')->__('Status'),
			'name'      => 'is_active',
			'required'  => false,
			'options'   => $model->getAvailableStatuses(),
		));
		
		Mage::dispatchEvent('webforms_adminhtml_webforms_edit_tab_information_prepare_form', array('form' => $form, 'fieldset' => $fieldset));
		
		if (!Mage::registry('webforms_data')->getId()) {
			$model->setData('is_active', '0');
		}
		
		if(Mage::getSingleton('adminhtml/session')->getWebFormsData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
			Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
		} elseif(Mage::registry('webforms_data')){
			$form->setValues(Mage::registry('webforms_data')->getData());
		}
		
		
		return parent::_prepareForm();
	}
}  
