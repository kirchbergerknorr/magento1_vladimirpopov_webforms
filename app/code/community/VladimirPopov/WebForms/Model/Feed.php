<?php

class VladimirPopov_WebForms_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://'
                . 'mageme.com/feeds/webforms/m1.rss';
        }
        return $this->_feedUrl;
    }

    public function observe() {
        $model  = Mage::getModel('webforms/feed');
        $model->checkUpdate();
    }
}