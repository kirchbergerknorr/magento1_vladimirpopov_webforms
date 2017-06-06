<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fields_Edit_Tab_Validation
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
		$form->setFieldNameSuffix('field');
		$form->setDataObject(Mage::registry('field'));
		$this->setForm($form);
		$fieldset = $form->addFieldset('webforms_form',array(
			'legend' => Mage::helper('webforms')->__('Validation')
		));
		
		
		$fieldset->addField('validate_length_min','text',array(
			'label' => Mage::helper('webforms')->__('Minimum length'),
			'class' => 'validate-number',
			'name' => 'validate_length_min',
		));

		$fieldset->addField('validate_length_max','text',array(
			'label' => Mage::helper('webforms')->__('Maximum length'),
			'class' => 'validate-number',
			'name' => 'validate_length_max',
		));
		
		$fieldset->addField('validate_regex','text',array(
			'label' => Mage::helper('webforms')->__('Validation RegEx'),
			'name' => 'validate_regex',
			'note' => Mage::helper('webforms')->__('Validate with custom regular expression')
		));
		
		$fieldset->addField('validate_message','textarea',array(
			'label' => Mage::helper('webforms')->__('Validation error message'),
			'name' => 'validate_message',
			'note' => Mage::helper('webforms')->__('Displayed error message text if regex validation fails')			
		));
		
		Mage::dispatchEvent('webforms_adminhtml_fields_edit_tab_design_prepare_form', array('form' => $form, 'fieldset' => $fieldset));
		
		if(Mage::registry('field')->getData('validate_length_min') == 0){
			Mage::registry('field')->setData('validate_length_min','');
		}
		
		if(Mage::registry('field')->getData('validate_length_max') == 0){
			Mage::registry('field')->setData('validate_length_max','');
		}
		
		if(Mage::getSingleton('adminhtml/session')->getWebFormsData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
			Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
		} elseif(Mage::registry('field')){
			$form->setValues(Mage::registry('field')->getData());
		}

		return parent::_prepareForm();
	}
}  
