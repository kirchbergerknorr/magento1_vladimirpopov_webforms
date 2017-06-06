<?php
class VladimirPopov_WebForms_Model_Logic_Aggregation
    extends Mage_Core_Model_Abstract
{
    const AGGREGATION_ANY = 'any';
    const AGGREGATION_ALL = 'all';

    public function toOptionArray()
    {
        $options = array();

        $options[]=array('value' => self::AGGREGATION_ANY, 'label' => Mage::helper('webforms')->__('Any value can be checked'));
        $options[]=array('value' => self::AGGREGATION_ALL, 'label' => Mage::helper('webforms')->__('All values should be checked'));

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