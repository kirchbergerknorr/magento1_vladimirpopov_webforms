<?php

/**
 * Class VladimirPopov_WebForms_Block_Rating
 */
class VladimirPopov_WebForms_Block_Rating
    extends Mage_Core_Block_Template
{
    /**
     * @return array|bool
     */
    public function getSummaryRatings()
    {
        $webform_id = $this->getData('webform_id');
        $store_id = Mage::app()->getStore()->getId();
        if (!$webform_id) return false;

        $summary_ratings = Mage::getModel('webforms/results')->getResource()->getSummaryRatings($webform_id, $store_id);

        return $summary_ratings;
    }
}