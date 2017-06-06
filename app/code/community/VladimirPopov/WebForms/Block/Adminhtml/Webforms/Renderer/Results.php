<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Results extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
	public function render(Varien_Object $row)
	{
		$value =  Mage::getModel('webforms/results')->getCollection()->addFilter('webform_id',$row->getId())->getSize();
		return $value.' [ <a href="#" style="text-decoration:none" onclick="setLocation(\''.$this->getRowUrl($row).'\')">'.Mage::helper('webforms')->__('View').'</a> ]';
	 
	 }

	 public function getRowUrl(Varien_Object $row)
	 {
		 return $this->getUrl('*/webforms_results',array('webform_id'=>$row->getId()));
	 }

}
