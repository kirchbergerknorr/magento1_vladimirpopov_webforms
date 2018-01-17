<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
require_once(Mage::getBaseDir('lib') . '/Webforms/mpdf.php');

class VladimirPopov_WebForms_Model_Results
    extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_NOTAPPROVED = -1;
    const STATUS_COMPLETED = 2;

    protected $_webform;

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/results');
    }

    public function addFieldArray($preserveFrontend = false, $field_types = array())
    {
        $data = $this->getData();
        $field_array = array();
        foreach ($data as $key => $value) {
            if (strstr($key, 'field_')) {
                $field_id = str_replace('field_', '', $key);
                $field = Mage::getModel('webforms/fields')->load($field_id);
                if ($field->getType() == 'select/checkbox' && !is_array($value)) $value = explode("\n", $value);
                if ($field->getType() == 'select/contact' && $preserveFrontend) {
                    $contact_array = $field->getContactArray($field->getValue('options'));
                    for ($i = 0; $i < count($contact_array); $i++) {

                        if ($field->getContactValueById($i) == $value) {
                            $value = $i;
                            break;
                        }
                    }
                }
                if (!count($field_types) || (count($field_types) && in_array($field->getType(), $field_types)))
                    $field_array[$field_id] = $value;
            }
        }
        $this->setData('field', $field_array);
        return $this;
    }

    public function getApprovalStatuses()
    {
        $statuses = new Varien_Object(array(
            self::STATUS_PENDING => Mage::helper('webforms')->__('Pending'),
            self::STATUS_APPROVED => Mage::helper('webforms')->__('Approved'),
            self::STATUS_COMPLETED => Mage::helper('webforms')->__('Completed'),
            self::STATUS_NOTAPPROVED => Mage::helper('webforms')->__('Not Approved'),
        ));

        Mage::dispatchEvent('webforms_results_statuses', array('statuses' => $statuses));

        return $statuses->getData();

    }

    public function getCustomer()
    {
        if (!$this->getCustomerId()) return false;
        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        return $customer;
    }

    public function sendEmail($recipient = 'admin', $contact = false)
    {
        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());
        if (!Mage::registry('webform'))
            Mage::register('webform', $webform);

        $emailSettings = $webform->getEmailSettings();

        // for admin
        $sender = Array(
            'name' => $this->getCustomerName(),
            'email' => $this->getReplyTo($recipient),
        );

        if (!$sender['name']) {
            $sender['name'] = $sender['email'];
        }

        // for customer
        if ($recipient == 'customer') {
            $sender['name'] = Mage::app()->getStore($this->getStoreId())->getFrontendName();
            $contact_array = $this->getContactArray();

            // send letter from selected contact
            if ($contact_array) {
                $sender = $contact_array;
            }

            if (strlen(trim($webform->getEmailCustomerSenderName())) > 0)
                $sender['name'] = $webform->getEmailCustomerSenderName();
        }

        if (Mage::getStoreConfig('webforms/email/email_from')) {
            $sender['email'] = Mage::getStoreConfig('webforms/email/email_from');
        }

        $subject = $this->getEmailSubject($recipient);

        $email = $emailSettings['email'];

        //for customer
        if ($recipient == 'customer') {
            $email = $this->getCustomerEmail();
        }

        $name = Mage::app()->getStore($this->getStoreId())->getFrontendName();

        if ($recipient == 'customer') {
            $name = $this->getCustomerName();
        }

        if ($recipient == 'contact') {
            if (empty($contact['email'])) return false;
            $email = $contact['email'];
            $name = $contact['name'];
            $recipient = 'admin';
        }

        $webformObject = new Varien_Object();
        $webformObject->setData($webform->getData());

        $store_group = Mage::app()->getStore($this->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getName();

        $customer_email = $this->getCustomerEmail();
        if (isset($customer_email[0])) $customer_email = $customer_email[0];

        $vars = Array(
            'webform_subject' => $subject,
            'webform_name' => $webform->getName(),
            'webform_result' => $this->toHtml($recipient),
            'customer_name' => $this->getCustomerName(),
            'customer_email' => $customer_email,
            'sender_email' => $email,
            'ip' => $this->getIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'result' => $this->getTemplateResultVar(),
            'webform' => $webformObject,
            'timestamp' => Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium', true),
        );

        $customer = $this->getCustomer();

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

        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            $postObject = new Varien_Object();
            $postObject->setData($post);

            // set region name if found
            if (!empty($post['region_id'])) {
                $postObject->setData('region_name', $post['region_id']);
                $region_name = Mage::getModel('directory/region')->load($post['region_id'])->getName();
                if ($region_name) {
                    $postObject->setData('region_name', $region_name);
                }
            }
            $vars['data'] = $postObject;
        }

        $vars['noreply'] = Mage::helper('webforms')->__('Please, don`t reply to this e-mail!');

        $storeId = $this->getStoreId();
        $templateId = 'webforms_notification';
        if ($webform->getEmailTemplateId()) {
            $templateId = $webform->getEmailTemplateId();
        }
        if ($recipient == 'customer') {
            if ($webform->getEmailCustomerTemplateId()) {
                $templateId = $webform->getEmailCustomerTemplateId();
            }
        }
        $file_list = $this->getFiles();
        $send_multiple_admin = false;
        if (is_string($email)) {
            if ($recipient == 'admin' && strstr($email, ','))
                $send_multiple_admin = true;
        }

        if ($recipient == 'admin') {
            $bcc_list = explode(',', $webform->getBccAdminEmail());
        }

        if ($recipient == 'customer') {
            $bcc_list = explode(',', $webform->getBccCustomerEmail());
        }
        // trim bcc array
        array_walk($bcc_list, create_function('&$val', '$val = trim($val);'));

        $validateEmail = new Zend_Validate_EmailAddress();
        if ($send_multiple_admin) {
            $email_array = explode(',', $email);
            foreach ($email_array as $email) {

                $mail = Mage::getModel('core/email_template')
                    ->setTemplateSubject($subject)
                    ->setReplyTo($this->getReplyTo($recipient));

                //file content is attached
                if ($webform->getEmailAttachmentsAdmin())
                    /** @var VladimirPopov_WebForms_Model_Files $file */
                    foreach ($file_list as $file) {
                        $attachment = file_get_contents($file->getFullPath());
                        $mail->getMail()->createAttachment(
                            $attachment,
                            Zend_Mime::TYPE_OCTETSTREAM,
                            Zend_Mime::DISPOSITION_ATTACHMENT,
                            Zend_Mime::ENCODING_BASE64,
                            $file->getName()
                        );
                    }

                //attach pdf version to email
                if ($webform->getPrintAttachToEmail() && @class_exists('mPDF')) {
                    $mpdf = @new mPDF('utf-8', 'A4');
                    @$mpdf->WriteHTML($this->toPrintableHtml());

                    $mail->getMail()->createAttachment(
                        @$mpdf->Output('', 'S'),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $this->getPdfFilename()
                    );
                }

                if (is_array($bcc_list))
                    foreach ($bcc_list as $bcc) {
                        if ($validateEmail->isValid($bcc)) {
                            $mail->addBcc($bcc);
                        }
                    }

                $mail->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))->sendTransactional($templateId, $sender, trim($email), $name, $vars, $storeId);

            }
        } else {
            $mail = Mage::getModel('core/email_template')
                ->setTemplateSubject($subject)
                ->setReplyTo($this->getReplyTo($recipient));
            //file content is attached
            if (($webform->getEmailAttachmentsAdmin() && $recipient == 'admin') || ($webform->getEmailAttachmentsCustomer() && $recipient == 'customer'))
                /** @var VladimirPopov_WebForms_Model_Files $file */
                foreach ($file_list as $file) {
                    $attachment = file_get_contents($file->getFullPath());
                    $mail->getMail()->createAttachment(
                        $attachment,
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $file->getName()
                    );
                }

            //attach pdf version to email
            if (($webform->getPrintAttachToEmail() && $recipient == 'admin') || ($webform->getCustomerPrintAttachToEmail() && $recipient == 'customer')) {
                if (@class_exists('mPDF')) {
                    $mpdf = @new mPDF('utf-8', 'A4');
                    @$mpdf->WriteHTML($this->toPrintableHtml($recipient));

                    $mail->getMail()->createAttachment(
                        @$mpdf->Output('', 'S'),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $this->getPdfFilename()
                    );
                }
            }

            if (is_array($bcc_list))
                foreach ($bcc_list as $bcc) {
                    if ($validateEmail->isValid($bcc)) {
                        $mail->addBcc($bcc);
                    }
                }

            $mail->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
            return $mail->getSentSuccess();
        }
    }

    public function getPdfFilename()
    {
        return Varien_File_Uploader::getCorrectFileName($this->getWebform()->getName()) . '-submitted-' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s', $this->getCreatedTime()) . '.pdf';
    }

    public function sendApprovalEmail()
    {
        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());

        // for customer
        $sender['name'] = Mage::app()->getStore($this->getStoreId())->getFrontendName();

        $sender['email'] = $this->getReplyTo('customer');

        if (Mage::getStoreConfig('webforms/email/email_from')) {
            $sender['email'] = Mage::getStoreConfig('webforms/email/email_from');
        }

        $email = $this->getCustomerEmail();

        $name = $this->getCustomerName();

        $webformObject = new Varien_Object();
        $webformObject->setData($webform->getData());

        $varResult = $this->getTemplateResultVar();

        $varResult->addData(array(
            'id' => $this->getId(),
            'subject' => $this->getEmailSubject(),
            'date' => Mage::helper('core')->formatDate($this->getCreatedTime()),
            'html' => $this->toHtml('customer'),
        ));

        $store_group = Mage::app()->getStore($this->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getName();

        $customer_email = $this->getCustomerEmail();
        if (isset($customer_email[0])) $customer_email = $customer_email[0];

        $vars = Array(
            'webform_name' => $webform->getName(),
            'webform_result' => $this->toHtml('customer'),
            'customer_name' => $this->getCustomerName(),
            'customer_email' => $customer_email,
            'sender_email', $email,
            'status' => $this->getStatusName(),
            'ip' => $this->getIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'result' => $varResult,
            'webform' => $webformObject,
            'timestamp' => Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium', true),
        );

        $customer = $this->getCustomer();

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

        $storeId = $this->getStoreId();
        $templateId = 'webforms_result_approval';
        $attachPDF = false;
        $pdfTemplate = 'admin';

        if ($this->getApproved() == self::STATUS_APPROVED) {
            if ($webform->getData('email_result_approved_template_id')) {
                $templateId = $webform->getData('email_result_approved_template_id');
            }
            //attach pdf version to email
            if (($webform->getApprovedPrintAttachToEmail())) {
                $attachPDF = true;
                $pdfTemplate = 'approved';
            }
        } else if ($this->getApproved() == self::STATUS_NOTAPPROVED) {
            if ($webform->getData('email_result_notapproved_template_id')) {
                $templateId = $webform->getData('email_result_notapproved_template_id');
            }
        } else if ($this->getApproved() == self::STATUS_COMPLETED) {
            if ($webform->getData('email_result_completed_template_id')) {
                $templateId = $webform->getData('email_result_completed_template_id');
            }
            //attach pdf version to email
            if (($webform->getCompletedPrintAttachToEmail())) {
                $attachPDF = true;
                $pdfTemplate = 'completed';
            }
        } else
            return false;

        $mail = Mage::getModel('core/email_template')
            ->setReplyTo($this->getReplyTo('customer'));

        if ($attachPDF && @class_exists('mPDF')) {
            $mpdf = @new mPDF('utf-8', 'A4');
            @$mpdf->WriteHTML($this->toPrintableHtml($pdfTemplate));

            $mail->getMail()->createAttachment(
                @$mpdf->Output('', 'S'),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $this->getPdfFilename()
            );
        }
        $bcc_list = explode(',', $webform->getBccApprovalEmail());
        // trim bcc array
        array_walk($bcc_list, create_function('&$val', '$val = trim($val);'));
        $validateEmail = new Zend_Validate_EmailAddress();

        if (is_array($bcc_list))
            foreach ($bcc_list as $bcc) {
                if ($validateEmail->isValid($bcc)) {
                    $mail->addBcc($bcc);
                }
            }

        $mail->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);

    }

    public function getIp()
    {
        return long2ip($this->getCustomerIp());
    }

    public function getStatusName()
    {
        $statuses = $this->getApprovalStatuses();
        foreach ($statuses as $status_id => $status_name) {
            if ($this->getApproved() == $status_id) return $status_name;
        }
    }

    public function toPrintableHtml($type = 'admin')
    {
        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());

        $webformObject = new Varien_Object();
        $webformObject->setData($webform->getData());

        $varResult = $this->getTemplateResultVar();

        $varResult->addData(array(
            'id' => $this->getId(),
            'subject' => $this->getEmailSubject(),
            'date' => Mage::helper('core')->formatDate($this->getCreatedTime()),
            'html' => $this->toHtml('customer'),
        ));

        $store_group = Mage::app()->getStore($this->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getName();

        $customer_email = $this->getCustomerEmail();
        if (isset($customer_email[0])) $customer_email = $customer_email[0];

        $vars = Array(
            'webform_name' => $webform->getName(),
            'webform_result' => $this->toHtml(),
            'result' => $varResult,
            'customer_name' => $this->getCustomerName(),
            'customer_email' => $customer_email,
            'ip' => $this->getIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'webform' => $webformObject,
        );

        $customer = $this->getCustomer();

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

        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            $postObject = new Varien_Object();
            $postObject->setData($post);

            // set region name if found
            if (!empty($post['region_id'])) {
                $postObject->setData('region_name', $post['region_id']);
                $region_name = Mage::getModel('directory/region')->load($post['region_id'])->getName();
                if ($region_name) {
                    $postObject->setData('region_name', $region_name);
                }
            }
            $vars['data'] = $postObject;
        }

        $templateId = 'webforms_result_print';
        /** @var Mage_Core_Model_Email_Template $template */
        $template = Mage::getModel('core/email_template')
            ->loadDefault($templateId)
            ->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()));

        if ($type == 'admin' && $webform->getPrintTemplateId()) {
            $templateId = $webform->getPrintTemplateId();
            $template->load($templateId);
        }

        if ($type == 'customer' && $webform->getCustomerPrintTemplateId()) {
            $templateId = $webform->getCustomerPrintTemplateId();
            $template->load($templateId);
        }

        if ($type == 'approved' && $webform->getApprovedPrintTemplateId()) {
            $templateId = $webform->getApprovedPrintTemplateId();
            $template->load($templateId);
        }

        if ($type == 'completed' && $webform->getCompletedPrintTemplateId()) {
            $templateId = $webform->getCompletedPrintTemplateId();
            $template->load($templateId);
        }
        return $template->getProcessedTemplate($vars);
    }

    public function getFiles()
    {
        $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId());
        return $files;
    }

    public function getFile($code = false)
    {
        if ($code === false) return false;
        foreach ($this->getWebform()->getFieldsToFieldsets(true) as $fieldset_id => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                if ($field->getCode() == $code) {
                    return Mage::getModel('webforms/files')->getCollection()
                        ->addFilter('result_id', $this->getId())
                        ->addFilter('field_id', $field->getId())
                        ->getFirstItem();
                }
            }
        }
    }

    public function getEmailSubject($recipient = 'admin')
    {
        /** @var VladimirPopov_WebForms_Model_Webforms $webform */
        $webform = $this->getWebform();
        $webform_name = $webform->getName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getFrontendName();

        //get default subject for admin
        $subject = Mage::helper('webforms')->__("Web-form '%s' submitted", $webform_name);

        //get subject for customer
        if ($recipient == 'customer') {
            $subject = Mage::helper('webforms')->__("You have submitted '%s' form on %s website", $webform_name, $store_name);
        }

        //iterate through fields and build subject
        $subject_array = array();
        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true);
        $logic_rules = $webform->getLogic(true);
        $this->addFieldArray();
        /** @var VladimirPopov_WebForms_Model_Fields $field */
        foreach ($fields_to_fieldsets as $fieldset) {
            foreach ($fieldset['fields'] as $field) {

                $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));

                if ($field->hasData('visible'))
                    $field_visibility = $field->getData('visible');
                else
                    $field_visibility = $webform->getLogicTargetVisibility($target_field, $logic_rules, $this->getData('field'));

                if ($field_visibility && $field->getEmailSubject()) {
                    foreach ($this->getData() as $key => $value) {
                        if ($key == 'field_' . $field->getId() && $value) {
                            $subject_array[] = $field->prepareResultValue($value);
                        }
                    }
                }
            }
        }

        if (count($subject_array) > 0) {
            $subject = implode(" / ", $subject_array);
        }
        return $subject;
    }

    public function getCustomerName()
    {
        $customer_name = array();
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getWebformId());
        foreach ($fields as $field) {
            foreach ($this->getData() as $key => $value) {
                if (is_string($value)) $value = trim($value);
                if ($key == 'field_' . $field->getId() && $value) {
                    if (
                        $field->getCode() == 'name' ||
                        $field->getCode() == 'firstname' ||
                        $field->getCode() == 'lastname' ||
                        $field->getCode() == 'middlename'
                    ) $customer_name[] = $value;
                }
            }
        }

        if (count($customer_name) == 0)
            if ($this->getCustomerId()) {
                $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
                if ($customer->getId())
                    return $customer->getName();
            }

        if (count($customer_name) == 0) {
            // try to get $_POST[''] variable
            if (Mage::app()->getRequest()->getPost('firstname'))
                $customer_name [] = Mage::app()->getRequest()->getParam('firstname');

            if (Mage::app()->getRequest()->getPost('lastname'))
                $customer_name [] = Mage::app()->getRequest()->getParam('lastname');
        }

        if (count($customer_name) == 0)
            return Mage::helper('core')->__('Guest');

        return implode(' ', $customer_name);

    }

    public function getContactArray()
    {

        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getWebformId())
            ->addFilter('type', 'select/contact');

        foreach ($fields as $field) {
            foreach ($this->getData() as $key => $value) {
                if ($key == 'field_' . $field->getId() && $value) {
                    return $field->getContactArray($value);
                }
            }
        }

        return false;
    }

    public function getTemplateResultVar()
    {
        $result = new Varien_Object(array(
            'id' => $this->getId(),
            'webform_id' => $this->getWebformId(),
        ));
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getWebformId());
        /** @var VladimirPopov_WebForms_Model_Fields $field */
        foreach ($fields as $field) {
            foreach ($this->getData() as $key => $value) {
                if (is_string($value)) $value = trim($value);
                if ($key == 'field_' . $field->getId() && $value) {
                    switch ($field->getType()) {
                        case 'date':
                        case 'datetime':
                            $data_value = $field->formatDate($value);
                            break;
                        case 'image':
                            $fileList = array();
                            $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId())->addFilter('field_id', $field->getId());
                            /** @var VladimirPopov_WebForms_Model_Files $file */
                            foreach ($files as $file) {
                                $htmlContent = '';
                                $img = '<figure><img src="' . $file->getThumbnail(Mage::getStoreConfig('webforms/images/email_thumbnail_width'), Mage::getStoreConfig('webforms/images/email_thumbnail_height')) . '"/>';
                                $htmlContent .= $img;
                                $htmlContent .= '<figcaption>' . $file->getName();
                                $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small></figcaption>';
                                $htmlContent .= '</figure>';
                                $fileList[]= $htmlContent;
                            }
                            $data_value = implode('<br>', $fileList);
                            break;
                        case 'file':
                            $fileList = array();
                            $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId())->addFilter('field_id', $field->getId());
                            foreach ($files as $file) {
                                $htmlContent = $file->getName();
                                $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small>';
                                $fileList[] = $htmlContent;
                            }
                            $data_value = implode('<br>', $fileList);
                            break;
                        case 'stars':
                            $data_value = $value . ' / ' . $field->getStarsCount();
                            break;
                        case 'select/contact':
                            $contact = $field->getContactArray($value);
                            !empty($contact["name"]) ? $data_value = $contact["name"] : $data_value = $value;
                            break;
                        default:
                            $data_value = nl2br($value);
                            break;
                    }
                    $value = new Varien_Object(array('html' => $data_value, 'value' => $this->getData('field_' . $field->getId())));
                    Mage::dispatchEvent('webforms_results_tohtml_value', array('field' => $field, 'value' => $value, 'result' => $this));
                    $data = new Varien_Object(array(
                        'value' => $value->getHtml(),
                        'name' => $field->getName(),
                        'result_label' => $field->getResultLabel(),
                    ));
                    $result->setData($field->getId(), $data);
                    if ($field->getCode()) {
                        $result->setData($field->getCode(), $data);
                    }
                }
            }
        }
        return $result;
    }

    public function getReplyTo($recipient = 'admin')
    {
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getWebformId())
            ->addFilter('type', 'email');

        $webform = Mage::getModel('webforms/webforms')->setStoreId($this->getStoreId())->load($this->getWebformId());

        $reply_to = false;

        foreach ($this->getData() as $key => $value) {
            if ($key == 'field_' . $fields->getFirstItem()->getId()) {
                $reply_to = $value;
            }
        }
        if (!$reply_to) {
            if (Mage::helper('customer')->isLoggedIn()) {
                $reply_to = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            } else {
                $reply_to = Mage::getStoreConfig('trans_email/ident_general/email', $this->getStoreId());
            }
            if (Mage::app()->getRequest()->getPost('email'))
                $reply_to = Mage::app()->getRequest()->getParam('email');
        }
        if ($recipient == 'customer') {
            if ($webform->getEmailReplyTo())
                $reply_to = $webform->getEmailReplyTo();
            elseif (Mage::getStoreConfig('webforms/email/email_reply_to', $this->getStoreId()))
                $reply_to = Mage::getStoreConfig('webforms/email/email_reply_to', $this->getStoreId());
            else
                $reply_to = Mage::getStoreConfig('trans_email/ident_general/email', $this->getStoreId());
        }
        return $reply_to;
    }

    public function getCustomerEmail()
    {
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getWebformId())
            ->addFilter('type', 'email');

        $customer_email = array();
        foreach ($this->getData() as $key => $value) {
            foreach ($fields as $field)
                if ($key == 'field_' . $field->getId()) {
                    if (strlen(trim($value)) > 0) $customer_email [] = $value;
                }
        }

        if (!count($customer_email)) {
            // try to get email by customer id
            if ($this->getCustomerId())
                $customer_email [] = Mage::getModel('customer/customer')->load($this->getCustomerId())->getEmail();
        }

        if (!count($customer_email)) {
            if (Mage::helper('customer')->isLoggedIn()) {
                $customer_email [] = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            }
        }

        if (!count($customer_email)) {
            // try to get $_POST['email'] variable
            if (Mage::app()->getRequest()->getPost('email'))
                $customer_email [] = Mage::app()->getRequest()->getParam('email');
        }

        return $customer_email;
    }

    public function toHtml($recipient = 'admin', $options = array())
    {
        /** @var VladimirPopov_WebForms_Model_Webforms $webform */
        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());

        $this->addFieldArray(true);

        if (!isset($options['header'])) {
            $options['header'] = $webform->getAddHeader();
        }
        if (!isset($options['skip_fields'])) {
            $options['skip_fields'] = array();
        }

        if (!isset($options['adminhtml_downloads'])) {
            $options['adminhtml_downloads'] = false;
        }

        if (!isset($options['explicit_links'])) {
            $options['explicit_links'] = false;
        }

        $html = "";
        $store_group = Mage::app()->getStore($this->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getName();
        if ($recipient == 'admin') {
            if ($store_group)
                $html .= Mage::helper('webforms')->__('Store group') . ": " . $store_group . "<br>";
            if ($store_name)
                $html .= Mage::helper('webforms')->__('Store name') . ": " . $store_name . "<br>";
            $html .= Mage::helper('webforms')->__('Customer') . ": " . $this->getCustomerName() . "<br>";
            $html .= Mage::helper('webforms')->__('IP') . ": " . $this->getIp() . "<br>";
        }
        $html .= Mage::helper('webforms')->__('Date') . ": " . Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium', true) . "<br>";
        $html .= "<br>";

        $head_html = "";
        if ($options['header']) $head_html = $html;

        $html = "";

        $logic_rules = $webform->getLogic(true);

        $fields_to_fieldsets = $webform
            ->getFieldsToFieldsets(true);
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $k = false;
            $field_html = "";

            $target_fieldset = array("id" => 'fieldset_' . $fieldset_id, 'logic_visibility' => $fieldset['logic_visibility']);
            $fieldset_visibility = $webform->getLogicTargetVisibility($target_fieldset, $logic_rules, $this->getData('field'));
            if ($fieldset_visibility) {
                /** @var VladimirPopov_WebForms_Model_Fields $field */
                foreach ($fieldset['fields'] as $field) {
                    $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                    if ($field->hasData('visible')) {
                        $field_visibility = $field->getData('visible');
                    } else
                        $field_visibility = $webform->getLogicTargetVisibility($target_field, $logic_rules, $this->getData('field'));
                    $value = $this->getData('field_' . $field->getId());
                    if ($field->getType() == 'html')
                        $value = $field->getValue();
                    if ($value && $field_visibility) {
                        if (!in_array($field->getType(), $options['skip_fields']) && $field->getResultDisplay() != 'off') {
                            $field_name = $field->getName();
                            if (strlen(trim($field->getResultLabel())) > 0)
                                $field_name = $field->getResultLabel();
                            if ($field->getResultDisplay() != 'value') $field_html .= '<b>' . $field_name . '</b><br>';
                            switch ($field->getType()) {
                                case 'date':
                                case 'datetime':
                                case 'date/dob':
                                    $value = $field->formatDate($value);
                                    break;
                                case 'stars':
                                    $value = $value . ' / ' . $field->getStarsCount();
                                    break;
                                case 'file':
                                    $fileList = array();
                                    $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId())->addFilter('field_id', $field->getId());
                                    foreach ($files as $file) {
                                        $htmlContent = '';
                                        if ($recipient == 'admin'  && ($webform->getFrontendDownload() || $options['explicit_links'])) $htmlContent .= '<a href="' . $file->getDownloadLink($options['adminhtml_downloads']) . '">' . $file->getName() . '</a>';
                                        else $htmlContent .= $file->getName();
                                        $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small>';
                                        $fileList[] = $htmlContent;
                                    }
                                    $value = implode('<br>', $fileList);
                                    break;
                                case 'image':
                                    $fileList = array();
                                    $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId())->addFilter('field_id', $field->getId());
                                    /** @var VladimirPopov_WebForms_Model_Files $file */
                                    foreach ($files as $file) {
                                        $htmlContent = '';
                                        $img = '<figure><img src="' . $file->getThumbnail(Mage::getStoreConfig('webforms/images/email_thumbnail_width'), Mage::getStoreConfig('webforms/images/email_thumbnail_height')) . '"/>';
                                        $htmlContent .= $img;
                                        if ($recipient == 'admin'  && ($webform->getFrontendDownload() || $options['explicit_links'])) {
                                            $htmlContent .= '<figcaption><a href="' . $file->getDownloadLink($options['adminhtml_downloads']) . '">' . $file->getName() . '</a>';
                                            $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small></figcaption>';
                                        } else {
                                                $htmlContent .= '<figcaption>' . $file->getName();
                                                $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small></figcaption>';
                                        }
                                        $htmlContent .= '</figure>';
                                        $fileList[]= $htmlContent;
                                    }
                                    $value = implode('<br>', $fileList);

                                    break;
                                case 'select/contact':
                                    $contact = $field->getContactArray($value);
                                    if (!empty($contact["name"])) $value = $contact["name"];
                                    break;
                                case 'html':
                                    $value = trim($field->getValue('html'));
                                    break;
                                case 'country':
                                    $country_name = Mage::app()->getLocale()->getCountryTranslation($value);
                                    if ($country_name) $value = $country_name;
                                    break;
                                case 'subscribe':
                                    if ($value) $value = Mage::helper('core')->__('Yes');
                                    else $value = Mage::helper('core')->__('No');
                                    break;
                                default :
                                    $value = nl2br(htmlspecialchars($value));
                                    break;
                            }
                            $k = true;
                            $value = new Varien_Object(array('html' => $value, 'value' => $this->getData('field_' . $field->getId())));
                            Mage::dispatchEvent('webforms_results_tohtml_value', array('field' => $field, 'value' => $value, 'result' => $this));
                            $field_html .= $value->getHtml() . "<br><br>";
                        }
                    }

                }
            }
            if (!empty($fieldset['name']) && $k && $fieldset['result_display'] == 'on')
                $field_html = '<h2>' . $fieldset['name'] . '</h2>' . $field_html;
            $html .= $field_html;
        }
        return $head_html . $html;

    }

    public function toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {

        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());

        if ($webform->getCode())
            $this->setData('webform_code', $webform->getCode());

        foreach ($this->getData() as $key => $value) {
            if (strstr($key, 'field_')) {
                $field = Mage::getModel('webforms/fields')
                    ->setStoreId($this->getStoreId())
                    ->load(str_replace('field_', '', $key));
                if (!empty($field) && $field->getCode()) {
                    $this->setData($field->getCode(), $value);
                }
            }
        }
        return parent::toXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * @return VladimirPopov_WebForms_Model_Webforms
     */
    public function getWebform()
    {
        if (!$this->_webform) {
            /** @var VladimirPopov_WebForms_Model_Webforms $webform */
            $webform = Mage::getModel('webforms/webforms')->setStoreId($this->getStoreId())->load($this->getWebformId());
            $this->_webform = $webform;
        }
        return $this->_webform;
    }

    public function setWebform(VladimirPopov_WebForms_Model_Webforms $webform)
    {
        if ($webform->getId() == $this->getWebformId())
            $this->_webform = $webform;
        return $this;
    }

    public function getValue($code = false)
    {
        if ($code === false) return false;
        $fieldCollection = Mage::getModel('webforms/fields')->getCollection()->addFilter('webform_id', $this->getWebformId());
        foreach ($fieldCollection as $field) {
            if ($field->getCode() == $code) {
                if($this->getData('field_' . $field->getId())) return $this->getData('field_' . $field->getId());
            }
        }
        return false;
    }

    public function setValue($fieldCode, $value)
    {
        foreach ($this->getWebform()->getFieldsToFieldsets(true) as $fieldset_id => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                if ($field->getCode() == $fieldCode) {
                    $this->setData('field_' . $field->getId(), $value);
                    $field_array = $this->getField();
                    $field_array[$field->getId()] = $value;
                    $this->setField($field_array);
                }
            }
        }
        return $this;
    }

    public function resizeImages()
    {
        if (Mage::registry('result_resize_image_' . $this->getId())) return $this;
        Mage::register('result_resize_image_' . $this->getId(), true);
        foreach ($this->getWebform()->getFieldsToFieldsets(true) as $fieldset_id => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                $field_value = $field->getValue();
                $resize = empty($field_value['image_resize']) ? false : $field_value['image_resize'];
                $width = empty($field_value['image_resize_width']) ? false : $field_value['image_resize_width'];
                $height = empty($field_value['image_resize_height']) ? false : $field_value['image_resize_height'];

                if ($field->getType() == 'image' && $resize && ($width > 0 || $height > 0)) {
                    $files = Mage::getModel('webforms/files')->getCollection()->addFilter('result_id', $this->getId())->addFilter('field_id', $field->getId());

                    /** @var VladimirPopov_WebForms_Model_Files $file */
                    foreach ($files as $file) {
                        $imageUrl = $file->getFullPath();
                        $file_info = @getimagesize($imageUrl);
                        if ($file_info) {
                            // skip bmp files
                            if (!strstr($file_info["mime"], "bmp")) {
                                if (file_exists($imageUrl)) {
                                    $file->setMemoryForImage();
                                    $imageObj = new Varien_Image($imageUrl);
                                    $imageObj->keepAspectRatio(true);
                                    $imageObj->keepTransparency(true);
                                    if (!$width) $width = $imageObj->getOriginalWidth();
                                    $imageObj->resize($width, $height);
                                    $imageObj->save($imageUrl);
                                    unset($imageObj);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    public function getUploader()
    {
        $uploader = new VladimirPopov_WebForms_Model_Uploader;
        $uploader->setResult($this);
        return $uploader;
    }

}

