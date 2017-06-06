<?php
class VladimirPopov_WebForms_Block_Adminhtml_Quickresponse_Edit
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE')
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
			{
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
				$this->_formScripts[] = "
					function toggleEditor() {
						if (tinyMCE.getInstanceById('page_content') == null) {
							tinyMCE.execCommand('mceAddControl', false, 'content');
						} else {
							tinyMCE.execCommand('mceRemoveControl', false, 'content');
						}
					}";
			}
	}
		
	public function __construct()
	{
		$quickresponse = Mage::getModel('webforms/quickresponse');
		$id = $this->getRequest()->getParam('id');
		if($id){
			$quickresponse->load($id);
		}
		Mage::register('quickresponse',$quickresponse);
		
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'webforms';
		$this->_controller = 'adminhtml_quickresponse';

		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => "$('saveandcontinue').value = true; editForm.submit()",
			'class'     => 'save',
		), -100);
	}
	
	public function getSaveUrl()
	{
		return $this->getUrl('*/webforms_quickresponse/save');
	}
	
	public function getBackUrl(){
		return $this->getUrl('*/webforms_quickresponse/index');
	}

	public function getHeaderText()
	{
		if(!is_null(Mage::registry('quickresponse')->getId())) {
			return Mage::helper('webforms')->__("Edit Quick Response '%s'", $this->htmlEscape(Mage::registry('quickresponse')->getTitle()));
		} else {
			return Mage::helper('webforms')->__('New Quick Response');
		}
	}	
}
