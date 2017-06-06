<?php
class VladimirPopov_WebForms_Model_Logic_Action
    extends Mage_Core_Model_Abstract
{

    const ACTION_SHOW = 'show';
    const ACTION_HIDE = 'hide';

    public function toOptionArray()
    {
        $options = array();

        $options[]=array('value' => self::ACTION_SHOW, 'label' => Mage::helper('webforms')->__('Show'));
        $options[]=array('value' => self::ACTION_HIDE, 'label' => Mage::helper('webforms')->__('Hide'));

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