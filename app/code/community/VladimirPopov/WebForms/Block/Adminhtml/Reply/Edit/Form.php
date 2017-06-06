<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply_Edit_Form
	extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/saveMessage'),
			'method' => 'post',
		));
		
		$form->setFieldNameSuffix('reply');

        $form->addField('reply_results','note',array(
            'text' => $this->getLayout()->createBlock('webforms/adminhtml_reply_results','reply_results')->toHtml()
        ));

		$Ids = $this->getRequest()->getParam('id');
		
		if(!is_array($Ids)){
			$Ids = array($Ids);
		} 
		
		$form->addField('result_id','hidden',array(
			'name' => 'result_id',
			'value' => serialize($Ids),
		));
		
		$form->addField('webform_id','hidden',array(
			'name' => 'webform_id',
			'value' => $this->getRequest()->getParam('webform_id'),
		));
		
		$form->addField('email','hidden',array(
			'name' => 'email'
		));
		
		// message block
		$message = $form->addFieldset('reply_fieldset',array(
			'legend' => Mage::helper('webforms')->__('Reply')
		));
	
		$quickresponse_options = Mage::getModel('webforms/quickresponse')->toOptionArray();

		if(count($quickresponse_options))
			$message->addField('quick_response', 'select', array(
				'label'		=> Mage::helper('webforms')->__('Quick response'),
				'name'		=> 'quick_response',
				'style'		=> 'width:500px;',
				'class'		=> 'order-disabled',
				'values'	=> array_merge(array(array('label'=>'...','value'=>'')),$quickresponse_options),
				'after_element_html' => '<button class="scalable" id="quickresponse_button" type="button"><span>'.$this->__('Load').'</span></button>'
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
			$wysiwygConfig->setData("plugins",array());
			
			$editor_type='editor';
			$config = $wysiwygConfig;
		}
		
		$message->addField('message', $editor_type, array(
			'label'     => Mage::helper('webforms')->__('Message'),
			'title'     => Mage::helper('webforms')->__('Message'),
			'style'     => 'width:700px; height:300px;',
			'name'      => 'message',
			'required'  => true,
			'config'	=> $config
		));
		
		if(count($Ids) == 1){
			$history = Mage::getModel('webforms/message')->getCollection()->addFilter('result_id',$Ids[0])->load();
			if(count($history)){
				$form->addField('reply_history','note',array(
					"text" => ' <div class="entry-edit-head"><h4 class="icon-head head-edit-form fieldset-legend">'.
                                Mage::helper('webforms')->__('Messages History').
                                '</h4></div><div class="fieldset"><div class="hor-scroll">'.
                                $this->getLayout()->createBlock('webforms/adminhtml_reply_history')->toHtml().
                                '</div></div>'
                    )
				);
			}
		}
				
		$form->setUseContainer(true);

		$this->setForm($form);
		
	}	
}
