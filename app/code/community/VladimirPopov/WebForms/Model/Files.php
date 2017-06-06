<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Files extends Mage_Core_Model_Abstract
{

    const THUMBNAIL_DIR = 'webforms/thumbs';

    /** @var  VladimirPopov_WebForms_Model_Results */
    protected $_result;

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/files');
    }

    public function getResult()
    {
        if ($this->_result) return $this->_result;
        if ($this->getResultId()) {
            $this->_result = Mage::getModel('webforms/results')->load($this->getResultId());
            return $this->_result;
        }
        return false;
    }

    public function getWebform()
    {
        $result = $this->getResult();
        if ($result) return $result->getWebform();
        return false;
    }

    public function getFullPath()
    {
        return Mage::getBaseDir('media') . DS . $this->getPath();
    }

    public function getSizeText()
    {
        $size = $this->getSize();
        $sizes = array(" bytes", " kb", " mb", " gb", " tb", " pb", " eb", " zb", " yb");
        if ($size == 0) {
            return ('n/a');
        } else {
            return (round($size / pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[intval($i)]);
        }
    }

    public function getDownloadLink()
    {
        return Mage::app()->getStore($this->getResult()->getStoreId())->getUrl('webforms/files/download', array('hash' => $this->getLinkHash()));
    }

    public function loadByHash($hash)
    {
        if ($hash)
            return $this->getCollection()->addFilter('link_hash', $hash)->getFirstItem();
        return false;
    }

    protected function getThumbnailDir()
    {
        return Mage::getBaseDir('media') . DS . self::THUMBNAIL_DIR;
    }

    public function getThumbnail($width = false, $height = false)
    {
        if (!$width) $width = Mage::app()->getStore()->getConfig('webforms/images/grid_thumbnail_width');
        if (!$width) $width = 100;

        $imageUrl = $this->getFullPath();

        $file_info = @getimagesize($imageUrl);

        if (!$file_info)
            return false;

        if (strstr($file_info["mime"], "bmp"))
            return false;

        if (file_exists($imageUrl)) {
            if (!$height) {
                $height = round($file_info[1] * ($width / $file_info[0]));
            }
            $imageResized = $this->getThumbnailDir() . DS . $this->getId() . '_' . $width . 'x' . $height;
            if (!file_exists($imageResized) || Mage::getStoreConfig('webforms/images/cache') == 0) {

                $this->setMemoryForImage();
                $adapter = Varien_Image_Adapter::ADAPTER_GD2;
                if (Mage::getStoreConfig('design/watermark_adapter/adapter') && class_exists(Varien_Image_Adapter_Imagemagic))
                    $adapter = Mage::getStoreConfig('design/watermark_adapter/adapter');
                $imageObj = new Varien_Image($imageUrl, $adapter);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepTransparency(true);
                $imageObj->resize($width, $height);
                $imageObj->save($imageResized);
                unset($imageObj);
            }
        } else {
            return false;
        }

        $url = Mage::app()->getStore($this->getResult()->getStoreId())->getBaseUrl('media') . self::THUMBNAIL_DIR;
        $url .= '/' . $this->getId() . '_' . $width . 'x' . $height;
        return $url;
    }

    public function setMemoryForImage()
    {
        $filename = $this->getFullPath();
        $imageInfo = getimagesize($filename);
        $MB = 1048576;  // number of bytes in 1M
        $K64 = 65536;    // number of bytes in 64K
        $TWEAKFACTOR = 1.5;  // Or whatever works for you
        if (empty($imageInfo['bits']) || empty($imageInfo['channels'])) return false;
        $memoryNeeded = round(($imageInfo[0] * $imageInfo[1]
                * $imageInfo['bits']
                * $imageInfo['channels'] / 8
                + $K64
            ) * $TWEAKFACTOR
        );
        $defaultLimit = ini_get('memory_limit');
        $memoryLimit = $defaultLimit;
        if (preg_match('/^(\d+)(.)$/', $defaultLimit, $matches)) {
            if ($matches[2] == 'M') {
                $memoryLimit = intval($matches[1]) * 1024 * 1024; // nnnM -> nnn MB
            } else if ($matches[2] == 'K') {
                $memoryLimit = intval($matches[1]) * 1024; // nnnK -> nnn KB
            }
        }
        if (function_exists('memory_get_usage') &&
            memory_get_usage() + $memoryNeeded > $memoryLimit
        ) {
            $newLimit = $memoryLimit + ceil((memory_get_usage()
                        + $memoryNeeded
                        - $memoryLimit
                    ) / $MB
                );
            ini_set('memory_limit', $newLimit . 'M');
            return $defaultLimit;
        } else
            return false;
    }
}