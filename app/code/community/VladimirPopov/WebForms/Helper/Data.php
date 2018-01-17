<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Helper_Data
    extends Mage_Core_Helper_Abstract
{

    const DKEY = 'WF1DM';
    const SKEY = 'WFSRV';
    const DEV_CHECK = true;

    public function getRealIp()
    {
        return Mage::helper('core/http')->getRemoteAddr();
    }

    public function captchaAvailable()
    {
        if (Mage::getStoreConfig('webforms/captcha/public_key') && Mage::getStoreConfig('webforms/captcha/private_key'))
            return true;
        return false;
    }

    public function getCaptcha()
    {
        $pubKey = Mage::getStoreConfig('webforms/captcha/public_key');
        $privKey = Mage::getStoreConfig('webforms/captcha/private_key');

        $recaptcha = false;

        if ($pubKey && $privKey) {
            $recaptcha = Mage::getModel('webforms/captcha');
            $recaptcha->setPublicKey($pubKey);
            $recaptcha->setPrivateKey($privKey);
            $recaptcha->setTheme(Mage::getStoreConfig('webforms/captcha/theme'));
        }
        return $recaptcha;
    }

    final public function getMageEdition()
    {
        if (method_exists('Mage', 'getEdition')) {
            switch (Mage::getEdition()) {
                case Mage::EDITION_COMMUNITY:
                    return 'CE';
                case Mage::EDITION_ENTERPRISE:
                    return 'EE';
                case Mage::EDITION_GO:
                    return 'GO';
                case Mage::EDITION_PROFESSIONAL:
                    return 'PRO';
            }
        }

        $version = explode('.', Mage::getVersion());

        if ($version[1] >= 9)
            return 'EE';

        return 'CE';
    }

    public function getMageSubversion()
    {
        $version = explode('.', Mage::getVersion());
        if (!empty($version[1])) return $version[1];
        return false;
    }

    public function htmlCut($text, $max_length)
    {
        $tags = array();
        $result = "";

        $is_open = false;
        $grab_open = false;
        $is_close = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";

        $i = 0;
        $stripped = 0;

        $stripped_text = strip_tags($text);

        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length) {
            $symbol = $text{$i};
            $result .= $symbol;

            switch ($symbol) {
                case '<':
                    $is_open = true;
                    $grab_open = true;
                    break;

                case '"':
                    if ($in_double_quotes)
                        $in_double_quotes = false;
                    else
                        $in_double_quotes = true;

                    break;

                case "'":
                    if ($in_single_quotes)
                        $in_single_quotes = false;
                    else
                        $in_single_quotes = true;

                    break;

                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes) {
                        $is_close = true;
                        $is_open = false;
                        $grab_open = false;
                    }

                    break;

                case ' ':
                    if ($is_open)
                        $grab_open = false;
                    else
                        $stripped++;

                    break;

                case '>':
                    if ($is_open) {
                        $is_open = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    } else if ($is_close) {
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }

                    break;

                default:
                    if ($grab_open || $is_close)
                        $tag .= $symbol;

                    if (!$is_open && !$is_close)
                        $stripped++;
            }

            $i++;
        }

        while ($tags)
            $result .= "</" . array_pop($tags) . ">";

        return $result;
    }

    public function addAssets(Mage_Core_Model_Layout $layout)
    {
        $head = $layout->getBlock('head');
        $content = $layout->getBlock('content');

        if ($head && $content) {

            $head->addCss('webforms/form.css', 'webforms');
            $head->addJs('prototype/window.js', 'webforms');
            $head->addItem('js_css', 'prototype/windows/themes/default.css', 'webforms');
            $head->addItem('js_css', 'prototype/windows/themes/alphacube.css', 'webforms');

            // logic
            $head->addJs('webforms/logic.js', 'webforms');

            // dropzone
            $head->addJs('webforms/dropzone.js', 'webforms');

            // multistep
            $head->addJs('webforms/multistep.js', 'webforms');

            // stars
            $head->addJs('webforms/stars.js', 'webforms');
            $head->addCss('webforms/stars.css', 'webforms');

            // auto-complete
            $head->addJs('webforms/auto-complete/auto-complete.min.js', 'webforms');
            $head->addCss('webforms/auto-complete.css', 'webforms');

            // tooltips
            $head->addJs('webforms/opentip/opentip-native-excanvas.min.js', 'webforms');
            $head->addItem('js_css', 'webforms/opentip/opentip.css', 'webforms');

            // wysiwyg
            $head->addJs('tiny_mce/tiny_mce.js', 'tiny-mce');

            // calendar
            $head->addJs('calendar/calendar.js', 'webforms');
            $head->addJs('calendar/calendar-setup.js', 'webforms');
            $head->addItem('js_css', 'calendar/calendar-blue.css', 'webforms');

            // wcag validation
            $head->addJs('webforms/validation.js', 'webforms');
        }

        if (in_array('cms_page', $layout->getUpdate()->getHandles()) || in_array('webforms_index_index', $layout->getUpdate()->getHandles())) {
            $production = $this->isProduction();
            if (!$production['verified']) {
                Mage::getSingleton('core/session')->addError($this->getNote());
            }
        }

        // add custom assets
        Mage::dispatchEvent('webforms_add_assets', array('layout' => $layout));

        return $this;
    }

    final protected function getDomain($url)
    {
        $url = str_replace(array('http://', 'https://', '/'), '', $url);
        $tmp = explode('.', $url);
        $cnt = count($tmp);

        if (empty($tmp[$cnt - 2]) || empty($tmp[$cnt - 1])) return $url;

        $suffix = $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];

        $exceptions = array(
            'com.au', 'com.br', 'com.bz', 'com.ve', 'com.gp',
            'com.ge', 'com.eg', 'com.es', 'com.ye', 'com.kz',
            'com.cm', 'net.cm', 'com.cy', 'com.co', 'com.km',
            'com.lv', 'com.my', 'com.mt', 'com.pl', 'com.ro',
            'com.sa', 'com.sg', 'com.tr', 'com.ua', 'com.hr',
            'com.ee', 'ltd.uk', 'me.uk', 'net.uk', 'org.uk',
            'plc.uk', 'co.uk', 'co.nz', 'co.za', 'co.il',
            'co.jp', 'ne.jp', 'net.au', 'com.ar'
        );

        if (in_array($suffix, $exceptions))
            $domain = $tmp[$cnt - 3] . '.' . $tmp[$cnt - 2] . '.' . $tmp[$cnt - 1];
        else
            $domain = $suffix;

        $domain = explode(':',$domain);
        $domain = $domain[0];

        return $domain;
    }

    final protected function verify($domain, $checkstr)
    {

        if ("wf" . substr(sha1(self::DKEY . $domain), 0, 18) == substr($checkstr, 0, 20)) {
            return true;
        }

        if ("wf" . substr(sha1(self::SKEY . $_SERVER['SERVER_ADDR']), 0, 10) == substr($checkstr, 0, 12)) {
            return true;
        }

        $dns_record = @dns_get_record($_SERVER['SERVER_NAME'],DNS_A);
        if(isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {

            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        $dns_record = @dns_get_record($domain,DNS_A);
        if(isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {
            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        $base = $this->getDomain(parse_url(Mage::app()->getStore(0)->getConfig('web/unsecure/base_url'), PHP_URL_HOST));
        $dns_record = @dns_get_record($base,DNS_A);
        if(isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {
            if ("wf" . substr(sha1(self::SKEY . $dns_record[0]['ip']), 0, 10) == substr($checkstr, 0, 12)) {
                return true;
            }
        }

        if (substr(sha1(self::SKEY . $base), 0, 8) == substr($checkstr, 12, 8))
            return true;

        if ($this->verifyIpMask(array($_SERVER['SERVER_ADDR'], $_SERVER['SERVER_NAME'], $domain, $base), $checkstr)) {
            return true;
        }

        return false;
    }

    final private function verifyIpMask($data, $checkstr)
    {
        if (!is_array($data)) {
            $data = array($data);
        }
        foreach ($data as $name) {
            $dns_record = @dns_get_record($name,DNS_A);
            if(isset($dns_record[0]) && !empty($dns_record[0]['ip'])) {
                $ipdata = explode('.', $dns_record[0]['ip']);
                if (isset($ipdata[3])) $ipdata[3] = '*';
                $mask = implode('.', $ipdata);
                if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                    return true;
                }
                if (isset($ipdata[2])) $ipdata[2] = '*';
                $mask = implode('.', $ipdata);
                if ("wf" . substr(sha1(self::SKEY . $mask), 0, 10) == substr($checkstr, 0, 12)) {
                    return true;
                }
            }
        }
        return false;
    }

    final public function getSerial()
    {
        $serial = Mage::getStoreConfig('webforms/license/serial');
        if (Mage::app()->getRequest()->getParam('website')) {
            $serial = Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getConfig('webforms/license/serial');
        }
        if (Mage::app()->getRequest()->getParam('store')) {
            $serial = Mage::getStoreConfig('webforms/license/serial', Mage::app()->getRequest()->getParam('store'));
        }

        return $serial;
    }

    final public function isProduction()
    {
        $errors = array();
        $warnings = array();

        $serial = $this->getSerial();

        $checkstr = strtolower(str_replace(array(" ", "-"), "", $serial));

        // check local environment
        if(self::DEV_CHECK)
            if ($this->isLocal()) return array('verified' => true, 'errors' => $errors, 'warnings' => $warnings);

        $domain = $this->getDomain($_SERVER['SERVER_NAME']);

        $domain2 = $this->getDomain(Mage::getStoreConfig('web/unsecure/base_url'));
        if (Mage::app()->getRequest()->getParam('website')) {
            $domain2 = $this->getDomain(Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getConfig('web/unsecure/base_url'));
        }
        if (Mage::app()->getRequest()->getParam('store')) {
            $domain2 = $this->getDomain(Mage::getStoreConfig('web/unsecure/base_url', Mage::app()->getRequest()->getParam('store')));
        }

        $verified = $this->verify($domain, $checkstr) || $this->verify($domain2, $checkstr);

        if (!$verified) {
            $errors[] = Mage::helper('webforms')->__('Incorrect serial number.');
        } else {
            // check development
            if (substr(strtoupper(sha1('DEV')), 0, 2) == substr($serial, -2)) {
                $warnings[] = Mage::helper('webforms')->__('Development license detected. Please do not use for production.');
            } else {
                // check Magento edition
                $magento_edition = Mage::getEdition();
                $edition = substr(strtoupper(sha1(strtoupper(substr($magento_edition, 0, 1) . 'E'))), 0, 2);
                if (substr($serial, -2) != substr(strtoupper(sha1(strtoupper('EE'))), 0, 2)) {
                    if ($edition != substr($serial, -2)) {
                        $errors[] = Mage::helper('webforms')->__('The license is not valid for Magento %s edition. Please do not use for production.', $magento_edition);
                    }
                }
            }
        }

        return array('verified' => $verified, 'errors' => $errors, 'warnings' => $warnings);
    }

    final public function isLocal()
    {
        $server_name = Mage::app()->getRequest()->getServer('SERVER_NAME');
        $domain = $this->getDomain($server_name);

        return substr($domain, -6) == '.local' ||
            substr($domain, -4) == '.dev' ||
            $server_name == 'localhost' ||
            substr($domain, -10) == '.localhost' ||
            substr($server_name, -7) == '.xip.io';
    }

    final public function getNote()
    {
        if (Mage::getStoreConfig('webforms/license/serial')) {
            return $this->__('WebForms Professional Edition license number is not valid for store domain.');
        }
        return $this->__('License serial number for WebForms Professional Edition is missing.');
    }

    public function randomAlphaNum($length = 6)
    {
        return Mage::helper('core')->getRandomString($length);
    }

    public function getVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->VladimirPopov_WebForms->version;
    }

    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        $this->rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    public function getLastCustomerResult($formCode = false)
    {
        if (!$formCode) return false;

        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        if (!$customerId) return false;

        $form = Mage::getModel('webforms/webforms')->getCollection()
            ->addFilter('code', $formCode)
            ->getFirstItem();

        if ($form && $form->getId()) {
            $result = Mage::getModel('webforms/results')->getCollection()
                ->addFilter('webform_id', $form->getId())
                ->addFilter('customer_id', $customerId)
                ->addOrder('created_time', 'desc')
                ->getFirstItem();
            return $result;
        }

        // if specified formCode is id
        $form = Mage::getModel('webforms/webforms')->getCollection()
            ->addFilter('id', $formCode)
            ->getFirstItem();

        if ($form && $form->getId()) {
            $result = Mage::getModel('webforms/results')->getCollection()
                ->addFilter('webform_id', $form->getId())
                ->addFilter('customer_id', $customerId)
                ->addOrder('created_time', 'desc')
                ->getFirstItem();
            return $result;
        }

        return false;
    }

    public function isInEmailStoplist($email){
        if(!$email) return false;

        $stoplist = preg_split("/[\s\n,;]+/",Mage::getStoreConfig('webforms/email/stoplist'));
        $flag = false;
        foreach($stoplist as $blocked_email){
            $pattern = trim($blocked_email);

            // clear global modifier
            if (substr($pattern, 0, 1) == '/' && substr($pattern, -2) == '/g') $pattern = substr($pattern, 0, strlen($pattern) - 1);

            $status = @preg_match($pattern, "Test");
            if($status !== false){
                $validate = new Zend_Validate_Regex($pattern);
                if($validate->isValid($email))
                    $flag = true;
            }
            if($email == $blocked_email) return true;
        }
        return $flag;
    }

}

// Fix for missing mime_content_type function

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}