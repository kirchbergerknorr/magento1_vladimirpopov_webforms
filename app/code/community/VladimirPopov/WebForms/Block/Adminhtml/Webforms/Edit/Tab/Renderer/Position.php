<?php
class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Renderer_Position
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return <<<HTML
{$value}<input type="text" name="{$this->getNameAttribute($row)}" value="{$value}" class="input-text"/>
HTML;

    }

    public function getNameAttribute(Varien_Object $row){
        if($this->getColumn()->getPrefix()){
            return $this->getColumn()->getPrefix().'['.$this->getColumn()->getIndex().']'.'['.$row->getId().']';
        }
        return $this->getColumn()->getIndex().'['.$row->getId().']';
    }
}