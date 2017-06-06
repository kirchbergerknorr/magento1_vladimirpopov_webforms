<?php
class VladimirPopov_WebForms_Block_Adminhtml_Quickresponse_Edit_Form
	extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
		));
		
		$form->setFieldNameSuffix('quickresponse');
		
		$id = $this->getRequest()->getParam('id');
			
		
		$fieldset = $form->addFieldset('quickresponse_fieldset',array(
			'legend' => Mage::helper('webforms')->__('Quick Response')
		));
		
		$fieldset->addField('title','text',array(
			'label' 	=> Mage::helper('webforms')->__('Title'),
			'class' 	=> 'required-entry',
			'required' 	=> true,
			'style' 	=> 'width:700px;',
			'name' 		=> 'title'
		));
	
		$editor_type = 'textarea';
		$config = '';
		if((float)substr(Mage::getVersion(),0,3) > 1.3 && substr(Mage::getVersion(),0,5)!= '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE'){
			
			$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
				array('tab_id' => $this->getTabId())
			);

			$wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
			$wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
			$wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');

			$wysiwygConfig["add_widgets"] = false;
			$wysiwygConfig["add_variables"] = false;
			$wysiwygConfig["widget_plugin_src"] = false;
			$plugins = $wysiwygConfig->setData("plugins",array());
			
			$editor_type='editor';
			$config = $wysiwygConfig;
		}
		
		$fieldset->addField('message', $editor_type, array(
			'label'     => Mage::helper('webforms')->__('Message'),
			'title'     => Mage::helper('webforms')->__('Message'),
			'style'     => 'width:700px; height:300px;',
			'name'      => 'message',
			'required'  => true,
			'config'	=> $config
		));
		
		if(Mage::getSingleton('adminhtml/session')->getFormData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
			Mage::getSingleton('adminhtml/session')->setFormData(false);
		} elseif(Mage::registry('quickresponse')->getId()){
			$form->setValues(Mage::registry('quickresponse')->getData());
		} 
				
		$form->addField('quickresponse_id','hidden',array(
				'name' => 'quickresponse_id',
				'value' => $id,
		));
		
		$form->addField('saveandcontinue','hidden',array(
				'name' => 'saveandcontinue',
		));

		$form->setUseContainer(true);

		$this->setForm($form);
		
	}	
}
