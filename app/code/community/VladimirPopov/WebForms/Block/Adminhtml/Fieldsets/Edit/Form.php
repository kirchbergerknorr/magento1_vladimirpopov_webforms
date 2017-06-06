<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Fieldsets_Edit_Form
	extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$model = Mage::getModel('webforms/fieldsets');
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'),'store'=>$this->getRequest()->getParam('store'))),
			'method' => 'post',
		));
		
		$renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
		$form->setFieldsetElementRenderer($renderer);
		$form->setFieldNameSuffix('fieldset');
		$form->setDataObject(Mage::registry('fieldsets_data'));
		
		$fieldset = $form->addFieldset('fieldset_information',array(
			'legend' => Mage::helper('webforms')->__('Information')
		));
		
		$fieldset->addField('name','text',array(
			'label' => Mage::helper('webforms')->__('Name'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'name'
		));
		
		$fieldset->addField('position','text',array(
			'label' => Mage::helper('webforms')->__('Position'),
			'required' => true,
			'name' => 'position',
			'note' => Mage::helper('webforms')->__('Fieldset position in the form'),
		));

        $fieldset->addField('css_class','text',array(
            'label' => Mage::helper('webforms')->__('Custom CSS classes'),
            'name' => 'css_class',
            'note' => Mage::helper('webforms')->__('Add custom CSS classes to the fieldset container'),
        ));
		
		$fieldset->addField('is_active', 'select', array(
			'label'     => Mage::helper('webforms')->__('Status'),
			'title'     => Mage::helper('webforms')->__('Status'),
			'name'      => 'is_active',
			'required'  => true,
			'options'   => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
		));
		
		$form->addField('webform_id', 'hidden', array(
			'name'      => 'webform_id',
			'value'   => 1,
		));
		
		$form->addField('saveandcontinue','hidden',array(
			'name' => 'saveandcontinue'
		));
		
		$fieldset = $form->addFieldset('fieldset_result',array(
			'legend' => Mage::helper('webforms')->__('Results / Notifications Settings')
		));

		$fieldset->addField('result_display', 'select', array(
			'label'     => Mage::helper('webforms')->__('Display fieldset name in results overview and notifications'),
			'title'     => Mage::helper('webforms')->__('Display fieldset name in results overview and notifications'),
			'name'      => 'result_display',
			'note' => Mage::helper('webforms')->__('Display fieldset name in result / notification messages'),
			'values'   => Mage::getModel('webforms/fieldsets_display')->toOptionArray(),
		));
		
		if (!$model->getId()) {
			$model->setData('is_active', '0' );
		}
		
		if(Mage::getSingleton('adminhtml/session')->getWebFormsData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
			Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
		} elseif(Mage::registry('fieldsets_data')){
			$form->setValues(Mage::registry('fieldsets_data')->getData());
		} 
		
		// set default field values
		if(!Mage::registry('fieldsets_data')->getId()){
			$form->setValues(array(
				'webform_id' => $this->getRequest()->getParam('webform_id'),
				'position' => 10
			));
		}
		$form->setUseContainer(true);

		Mage::dispatchEvent('webforms_adminhtml_fieldsets_edit_form_prepare_layout', array('form' => $form, 'fieldset' => $fieldset));

		$this->setForm($form);
		
	}
}  
