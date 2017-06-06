<?php
class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Id extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		$messages = Mage::getModel('webforms/message')->getCollection()->addFilter('result_id',$row->getId())->count();
		if($messages) $html = '<div class="result-replied">'.$value.'</div>';
		else $html = '<div class="result-not-replied">'.$value.'</div>';
		return $html;
	}

}
