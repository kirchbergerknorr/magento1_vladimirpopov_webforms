<?php
class VladimirPopov_WebForms_Block_Adminhtml_Element_Field
	extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	
	protected function _construct()
	{
		$this->setTemplate('webforms/form/renderer/fieldset/element.phtml');
	}
	
	public function getDataObject()
	{
		return $this->getElement()->getForm()->getDataObject();
	}

	public function usedDefault()
	{
		if(Mage::app()->getRequest()->getParam('store')){
			$data = $this->getDataObject();
			if($data){
				$store_data = $data->getStoreData();
				$id =$this->getElement()->getId();
				if(is_array($store_data) && array_key_exists($id,$store_data))
					return false;
			}
			return true;
		}
		return false;
	}

	public function canDisplayUseDefault(){
		if(Mage::app()->getRequest()->getParam('store')){
			return true;
		}
		return false;
	}
	
	public function checkFieldDisable()
	{
		if ($this->canDisplayUseDefault() && $this->usedDefault()) {
			$this->getElement()->setDisabled(true);
		}
		return $this;
	}
	
	public function getScopeLabel()
	{
		if(!Mage::app()->isSingleStoreMode())
			return '[STORE VIEW]';
	}
	
	/**
	 * Retrieve element label html
	 *
	 * @return string
	 */
	public function getElementLabelHtml()
	{
		return $this->getElement()->getLabelHtml();
	}

	/**
	 * Retrieve element html
	 *
	 * @return string
	 */
	public function getElementHtml()
	{
		return $this->getElement()->getElementHtml();
	}
}
