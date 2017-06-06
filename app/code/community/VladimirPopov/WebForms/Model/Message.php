<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Message
    extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/message');
    }

    public function sendEmail()
    {
        $result = Mage::getModel('webforms/results')
            ->load($this->getResultId());

        $email = $result->getCustomerEmail();

        if (!$email) return false;

        $name = $result->getCustomerName();

        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($result->getStoreId())
            ->load($result->getWebformId());

        $sender = Array(
            'name' => Mage::app()->getStore($result->getStoreId())->getFrontendName(),
            'email' => $result->getReplyTo('customer'),
        );

        if(strlen(trim($webform->getEmailCustomerSenderName()))>0)
            $sender['name'] = $webform->getEmailCustomerSenderName();

        if (Mage::getStoreConfig('webforms/email/email_from')) {
            $sender['email'] = Mage::getStoreConfig('webforms/email/email_from');
        }

        $subject = $result->getEmailSubject();

        $vars = $this->getTemplateVars();

        $storeId = $result->getStoreId();

        $templateId = 'webforms_reply';

        if ($webform->getEmailReplyTemplateId()) {
            $templateId = $webform->getEmailReplyTemplateId();
        }

        $mail = Mage::getModel('core/email_template')
            ->setTemplateSubject($subject)
            ->setReplyTo($result->getReplyTo('customer'))
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);

        return $mail->getSentSuccess();
    }

    public function getTemplateVars()
    {
        $result = Mage::getModel('webforms/results')
            ->load($this->getResultId());
        $name = $result->getCustomerName();
        
        $webform = $result->getWebform();
        $subject = $result->getEmailSubject();
        $store_group = Mage::app()->getStore($result->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($result->getStoreId())->getName();

        $varCustomer = new Varien_Object(array(
            'name' => $name
        ));

        $varResult = $result->getTemplateResultVar();

        $varResult->addData(array(
            'id' => $result->getId(),
            'subject' => $result->getEmailSubject(),
            'date' => Mage::helper('core')->formatDate($result->getCreatedTime()),
            'html' => $result->toHtml('customer'),
        ));

        $varReply = new Varien_Object(array(
            'date' => Mage::helper('core')->formatDate($this->getCreatedTime()),
            'message' => $this->getMessage(),
            'author' => $this->getAuthor()
        ));

        $vars = Array(
            'webform_subject' => $subject,
            'webform_name' => $webform->getName(),
            'customer_name' => $result->getCustomerName(),
            'customer_email' => $result->getCustomerEmail(),
            'ip' => $result->getIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'customer' => $varCustomer,
            'result' => $varResult,
            'reply' => $varReply,
            'webform' => $webform
        );

        $customer = $result->getCustomer();

        if ($customer) {
            $vars['customer'] = $customer;
            $billing_address = $customer->getDefaultBillingAddress();
            if ($billing_address) {
                $vars['billing_address'] = $billing_address;
            }
            $shipping_address = $customer->getDefaultShippingAddress();
            if ($shipping_address) {
                $vars['shipping_address'] = $shipping_address;
            }
        }

        return $vars;
    }
}