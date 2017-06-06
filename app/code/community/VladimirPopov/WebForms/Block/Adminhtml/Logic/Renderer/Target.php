<?php
class VladimirPopov_WebForms_Block_Adminhtml_Logic_Renderer_Target
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $field_id = $row->getFieldId();
        $field = Mage::getModel('webforms/fields')->load($field_id);
        $value =  $row->getData($this->getColumn()->getIndex());

        $options = array();
        $webform = Mage::getModel('webforms/webforms')->setStoreId($row->getStoreId())->load($field->getWebformId());
        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true);

        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $field_options = array();
            foreach ($fieldset['fields'] as $field) {
                if (in_array('field_'.$field->getId(),$value))
                    $field_options[] = $field->getName();
            }

            if($fieldset_id){
                if(in_array('fieldset_'.$fieldset_id,$value))
                    $options[]= $fieldset['name'].' ['.Mage::helper('webforms')->__('Field Set').']';
                if(count($field_options)){
                    $options[]= '<b>'.$fieldset['name'].'</b><br>&nbsp;&nbsp;&nbsp;&nbsp;'.implode('<br>&nbsp;&nbsp;&nbsp;&nbsp;',$field_options);
                }
            } else {
                foreach($field_options as $opt){
                    $options[]= $opt;
                }
            }
        }

        return implode('<br>',$options);
    }
}