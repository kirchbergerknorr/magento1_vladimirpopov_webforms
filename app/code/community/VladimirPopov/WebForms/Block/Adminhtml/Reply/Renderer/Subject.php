<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply_Renderer_Subject
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$field_id = str_replace('field_','',$this->getColumn()->getIndex());
		$value =  $row->getEmailSubject();
		return $value;
	}	
}
