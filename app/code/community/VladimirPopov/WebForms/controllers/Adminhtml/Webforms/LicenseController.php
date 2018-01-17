<?php
class VladimirPopov_WebForms_Adminhtml_Webforms_LicenseController extends Mage_Adminhtml_Controller_Action
{
    final public function verifyAction()
    {
        $result = Mage::helper('webforms')->isProduction();
        $verified = $result['verified'];
        $errors = $result['errors'];
        $warnings = $result['warnings'];

        if($verified) {

            $url = 'https://mageme.com/licensecenter/serial/check';
            $request_params = $this->getCurlParams();

            // verify serial registration
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16');
            $dataJson = curl_exec($ch);
            if (!json_decode($dataJson)) {
                $errors[] = Mage::helper('webforms')->__('Unexpected license server response.');
            } else {
                $data = json_decode($dataJson, true);
                if (!empty($data['valid']) && $verified) {
                    $verified = $data['valid'];
                }
                if (!empty($data['errors'])) {
                    $errors = array_merge($errors, $data['errors']);
                }
                if (!empty($data['warnings'])) {
                    $warnings = array_merge($warnings, $data['warnings']);
                }
            }
        }
        if(count($errors))
        {
            $verified = false;
        }

        $json = json_encode(array('verified' => $verified, 'errors' => $errors, 'warnings' => $warnings));

        $this->getResponse()->setBody(htmlspecialchars($json, ENT_NOQUOTES));
    }

    final protected function getCurlParams()
    {
        $version = (string)Mage::helper('webforms')->getVersion();

        $serial = Mage::helper('webforms')->getSerial();

        $curl_params = array(
            'serial' => $serial,
            'product_name' => 'WFP2',
            'product_version' => $version,
            'magento_edition' => Mage::getEdition(),
            'magento_version' => Mage::getVersion()
        );

        return http_build_query($curl_params);
    }
}