<?php
class VladimirPopov_WebForms_Model_Logic_Condition
    extends Mage_Core_Model_Abstract
{
    const CONDITION_EQUAL = 'equal';
    const CONDITION_NOTEQUAL = 'notequal';

    public function toOptionArray()
    {
        $options = array();

        $options[]=array('value' => self::CONDITION_EQUAL, 'label' => Mage::helper('webforms')->__('Equal'));
        $options[]=array('value' => self::CONDITION_NOTEQUAL, 'label' => Mage::helper('webforms')->__('NOT equal'));

        return $options;
    }

    public function getOptions()
    {
        $opt = $this->toOptionArray();
        $options = array();
        foreach($opt as $o){
            $options[$o['value']] = $o['label'];
        }

        return $options;
    }
}