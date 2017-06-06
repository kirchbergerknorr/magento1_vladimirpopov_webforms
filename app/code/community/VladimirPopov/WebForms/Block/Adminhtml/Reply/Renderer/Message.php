<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply_Renderer_Message
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$field_id = str_replace('field_','',$this->getColumn()->getIndex());
		$value =  $row->toHtml('admin',array('header'=>false));
		if(strlen(strip_tags($value))> 200 || substr_count($value, "\n")>11){
			$div_id = 'x_'.$field_id.'_'.$row->getId();
			$preview_div_id = 'preview_x_'.$field_id.'_'.$row->getId();
			$onclick = "$('{$preview_div_id}').hide(); Effect.toggle('$div_id', 'slide', { duration: 0.3 }); this.style.display='none';  return false;";
			$html = '<div style="min-width:400px" id="'.$preview_div_id.'">'.Mage::helper('webforms')->htmlCut($value,200).'</div>';
			$html.= '<div id="'.$div_id.'" style="display:none;min-width:400px">'.$value.'</div>';
			$html.= '<a onclick="'.$onclick.'" style="text-decoration:none;float:right">['.$this->__('Expand').']</a>';
			return $html;
		}
		return $value;
	}	
}
