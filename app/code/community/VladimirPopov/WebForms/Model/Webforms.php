<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Webforms
    extends VladimirPopov_WebForms_Model_Abstract
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_fields_to_fieldsets = array();
    protected $_hidden = array();
    protected $_logic_target = array();

    public function _getFieldsToFieldsets()
    {
        return $this->_fields_to_fieldsets;
    }

    public function _setLogicTarget($logic_target)
    {
        $this->_logic_target = $logic_target;
        return $this;
    }

    public function _getLogicTarget($uid = false)
    {
        $logic_target = $this->_logic_target;
        // apply unique id
        if ($uid) {
            $logic_target = array();
            foreach ($this->_logic_target as $target) {
                if (strstr($target['id'], 'field_')) $target['id'] = str_replace('field_', 'field_' . $uid, $target['id']);
                if (strstr($target['id'], 'fieldset_')) $target['id'] = str_replace('fieldset_', 'fieldset_' . $uid, $target['id']);
                $logic_target[] = $target;
            }
        }
        return $logic_target;
    }

    public function _setFieldsToFieldsets($fields_to_fieldsets)
    {
        $this->_fields_to_fieldsets = $fields_to_fieldsets;
        return $this;
    }

    public function _getHidden()
    {
        return $this->_hidden;
    }

    public function _setHidden($hidden)
    {
        $this->_hidden = $hidden;
        return $this;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/webforms');
    }

    public function getAvailableStatuses()
    {
        $statuses = new Varien_Object(array(
            self::STATUS_ENABLED => Mage::helper('webforms')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('webforms')->__('Disabled'),
        ));

        Mage::dispatchEvent('webforms_statuses', array('statuses' => $statuses));

        return $statuses->getData();

    }

    public function toOptionArray()
    {
        $collection = $this->getCollection()->addOrder('name', 'asc');

        // filter by role permissions
        $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $role = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username', $username)->getFirstItem()->getRole();
        $rule_all = Mage::getModel('admin/rules')->getCollection()
            ->addFilter('role_id', $role->getId())
            ->addFilter('resource_id', 'all')
            ->getFirstItem();
        if ($rule_all->getPermission() == 'deny') {
            $collection->addRoleFilter($role->getId());
        }

        $option_array = array();
        foreach ($collection as $webform)
            $option_array[] = array('value' => $webform->getId(), 'label' => $webform->getName());
        return $option_array;
    }

    public function getGridOptions($store_id = false)
    {
        $collection = $this->getCollection()->addOrder('name', 'asc');
        if ($store_id) {
            $collection->setStoreId($store_id);
        }

        $option_array = array();
        foreach ($collection as $webform) {
            $option_array[$webform->getId()] = $webform->getName();
        }
        return $option_array;

    }

    public function getFieldsetsOptionsArray()
    {
        $collection = Mage::getModel('webforms/fieldsets')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());
        $collection->getSelect()->order('position asc');
        $options = array(0 => '...');
        foreach ($collection as $o) {
            $options[$o->getId()] = $o->getName();
        }
        return $options;
    }

    public function getTemplatesOptions()
    {
        $options = array(0 => Mage::helper('webforms')->__('Default'));
        $templates = Mage::getResourceSingleton('core/email_template_collection');
        foreach ($templates as $template) {
            $options[$template->getTemplateId()] = $template->getTemplateCode();
        }
        return $options;
    }

    public function getEmailSettings()
    {
        $settings["email_enable"] = $this->getSendEmail();
        $settings["email"] = Mage::getStoreConfig('webforms/email/email');
        if ($this->getEmail())
            $settings["email"] = $this->getEmail();
        return $settings;
    }

    public function getFieldsToFieldsets($all = false, VladimirPopov_WebForms_Model_Results $result = null)
    {
        if ($this->_fields_to_fieldsets) return $this->_fields_to_fieldsets;

        $logic_rules = $this->getLogic(true);

        //get form fieldsets
        $fieldsets = Mage::getModel('webforms/fieldsets')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());

        if (!$all)
            $fieldsets->addFilter('is_active', self::STATUS_ENABLED);

        $fieldsets->getSelect()->order('position asc');

        //get form fields
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());

        if (!$all) {
            $fields->addFilter('is_active', self::STATUS_ENABLED);
        }

        $fields->getSelect()->order('position asc');

        //fields to fieldsets
        //make zero fieldset
        $fields_to_fieldsets = array();
        $hidden = array();
        $required_fields = array();
        $default_data = array();

        foreach ($fields as $field) {
            // set default data
            if (strstr($field->getType(), 'select')) {
                $options = $field->getOptionsArray();
                $checked_options = array();
                foreach ($options as $o) {
                    if ($o['checked']) {
                        $checked_options[] = $o['value'];
                    }
                }
                if (count($checked_options)) {
                    $default_data[$field->getId()] = $checked_options;
                }
            }

            //set default visibility
            $field->setData('logic_visibility', VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE);

            if ($field->getFieldsetId() == 0) {
                if ($all || $field->getType() != 'hidden') {
                    if ($field->getRequired()) $required_fields[] = 'field_' . $field->getId();
                    if ($all || $field->getIsActive())
                        $fields_to_fieldsets[0]['fields'][] = $field;
                } elseif ($field->getType() == 'hidden') {
                    $hidden[] = $field;
                }
            }
        }


        foreach ($fieldsets as $fieldset) {
            foreach ($fields as $field) {
                if ($field->getFieldsetId() == $fieldset->getId()) {
                    if ($all || $field->getType() != 'hidden') {
                        if ($all || $field->getIsActive())
                            $fields_to_fieldsets[$fieldset->getId()]['fields'][] = $field;
                    } elseif ($field->getType() == 'hidden') {
                        if ($all || $field->getIsActive())
                            $hidden[] = $field;
                    }
                }
            }
            if (!empty($fields_to_fieldsets[$fieldset->getId()]['fields'])) {
                $fields_to_fieldsets[$fieldset->getId()]['name'] = $fieldset->getName();
                $fields_to_fieldsets[$fieldset->getId()]['result_display'] = $fieldset->getResultDisplay();
                $fields_to_fieldsets[$fieldset->getId()]['css_class'] = $fieldset->getCssClass();
            }
        }

        // set logic attributes
        $logic_target = array();
        $hidden_targets = array();
        $logicModel = Mage::getModel('webforms/logic');
        $target = array();
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE;
            if (count($logic_rules))
                foreach ($logic_rules as $logic) {
                    if ($logic->getAction() == VladimirPopov_WebForms_Model_Logic_Action::ACTION_SHOW && $logic->getIsActive()) {

                        // check fieldset visibility
                        if (in_array('fieldset_' . $fieldset_id, $logic->getTarget())) {
                            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN;
                        }

                        // check fields visibility
                        foreach ($fieldset['fields'] as $field) {
                            if (in_array('field_' . $field->getId(), $logic->getTarget())) {
                                $field->setData('logic_visibility', VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN);
                            }
                        }
                    }
                }
        }

        $field_map = array();
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                $field_map['fieldset_' . $fieldset_id][] = $field->getId();
            }
        }

        // check field values and assign visibility
        if ($result && $result->getId()) {
            $result->addFieldArray();
            $default_data = $result->getData('field');
        }
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $target['id'] = 'fieldset_' . $fieldset_id;
            $target['logic_visibility'] = $fieldset['logic_visibility'];
            $visibility = $logicModel->getTargetVisibility($target, $logic_rules, $default_data, $field_map);
            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = $visibility ?
                VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE :
                VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN;
            if (!$visibility) $hidden_targets[] = "fieldset_" . $fieldset_id;

            // check fields visibility
            foreach ($fieldset['fields'] as $field) {
                $target['id'] = 'field_' . $field->getId();
                $target['logic_visibility'] = $field->getData('logic_visibility');
                $visibility = $logicModel->getTargetVisibility($target, $logic_rules, $default_data, $field_map);
                $field->setData('logic_visibility', $visibility ?
                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE :
                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN);
                if (!$visibility) $hidden_targets[] = "field_" . $field->getId();
            }

        }

        // set logic target
        foreach ($logic_rules as $logic)
            if ($logic->getIsActive())
                foreach ($logic->getTarget() as $target) {
                    $required = false;
                    if (in_array($target, $required_fields)) $required = true;
                    if (!in_array($target, $logic_target))
                        $logic_target[] = array(
                            "id" => $target,
                            "logic_visibility" =>
                                in_array($target, $hidden_targets) ?
                                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN :
                                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE,
                            "required" => $required
                        );
                }

        $this->_setLogicTarget($logic_target);
        $this->_setFieldsToFieldsets($fields_to_fieldsets);
        $this->_setHidden($hidden);
        $this->setHiddenTargets($hidden_targets);

        $this->_fields_to_fieldsets = $fields_to_fieldsets;
        return $fields_to_fieldsets;
    }

    public function getDashboardGroups()
    {
        if ($this->getData('dashboard_groups')) return $this->getData('dashboard_groups');
        return array();
    }

    public function getAccessGroups()
    {
        if ($this->getData('access_groups')) return $this->getData('access_groups');
        return array();
    }

    public function useCaptcha()
    {
        $useCaptcha = true;
        if ($this->getCaptchaMode() != 'default') {
            $captcha_mode = $this->getCaptchaMode();
        } else {
            $captcha_mode = Mage::getStoreConfig('webforms/captcha/mode');
        }
        if ($captcha_mode == "off" || !Mage::helper('webforms')->captchaAvailable())
            $useCaptcha = false;
        if (Mage::getSingleton('customer/session')->getCustomerId() && $captcha_mode == "auto")
            $useCaptcha = false;
        if ($this->getData('disable_captcha'))
            $useCaptcha = false;

        return $useCaptcha;
    }

    public function duplicate()
    {
        // duplicate form
        $form = Mage::getModel('webforms/webforms')
            ->setData($this->getData())
            ->setId(null)
            ->setName($this->getName() . ' ' . Mage::helper('webforms')->__('(new copy)'))
            ->setIsActive(false)
            ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
            ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
            ->save();

        // duplicate store data
        $stores = Mage::getModel('webforms/store')
            ->getCollection()
            ->addFilter('entity_id', $this->getId())
            ->addFilter('entity_type', $this->getEntityType());

        foreach ($stores as $store) {
            Mage::getModel('webforms/store')
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($form->getId())
                ->save();
        }

        $fieldset_update = array();
        $field_update = array();

        // duplicate fieldsets and fields
        $fields_to_fieldsets = $this->getFieldsToFieldsets(true);
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            if ($fieldset_id) {
                $fs = Mage::getModel('webforms/fieldsets')->load($fieldset_id);
                $new_fieldset = Mage::getModel('webforms/fieldsets')
                    ->setData($fs->getData())
                    ->setId(null)
                    ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setWebformId($form->getId())
                    ->save();
                $new_fieldset_id = $new_fieldset->getId();

                $fieldset_update[$fieldset_id] = $new_fieldset_id;

                // duplicate store data
                $stores = Mage::getModel('webforms/store')
                    ->getCollection()
                    ->addFilter('entity_id', $fs->getId())
                    ->addFilter('entity_type', $fs->getEntityType());

                foreach ($stores as $store) {
                    Mage::getModel('webforms/store')
                        ->setData($store->getData())
                        ->setId(null)
                        ->setEntityId($new_fieldset_id)
                        ->save();
                }
            } else {
                $new_fieldset_id = 0;
            }
            foreach ($fieldset['fields'] as $field) {
                $new_field = Mage::getModel('webforms/fields')
                    ->setData($field->getData())
                    ->setId(null)
                    ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setWebformId($form->getId())
                    ->setFieldsetId($new_fieldset_id)
                    ->save();

                $field_update[$field->getId()] = $new_field->getId();

                // duplicate store data
                $stores = Mage::getModel('webforms/store')
                    ->getCollection()
                    ->addFilter('entity_id', $field->getId())
                    ->addFilter('entity_type', $field->getEntityType());

                foreach ($stores as $store) {
                    Mage::getModel('webforms/store')
                        ->setData($store->getData())
                        ->setId(null)
                        ->setEntityId($new_field->getId())
                        ->save();
                }
            }
        }

        // duplicate logic
        $logic_rules = $this->getLogic();
        foreach ($logic_rules as $logic) {
            $new_field_id = $field_update[$logic->getFieldId()];
            $new_target = array();
            foreach ($logic->getTarget() as $target) {
                foreach ($fieldset_update as $old_id => $new_id) {
                    if ($target == 'fieldset_' . $old_id)
                        $new_target[] = 'fieldset_' . $new_id;
                }
                foreach ($field_update as $old_id => $new_id) {
                    if ($target == 'field_' . $old_id)
                        $new_target[] = 'field_' . $new_id;
                }
            }
            $new_logic = Mage::getModel('webforms/logic')
                ->setData($logic->getData())
                ->setId(null)
                ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                ->setFieldId($new_field_id)
                ->setTarget($new_target)
                ->save();

            // duplicate store data
            $stores = Mage::getModel('webforms/store')
                ->getCollection()
                ->addFilter('entity_id', $logic->getId())
                ->addFilter('entity_type', $logic->getEntityType());

            foreach ($stores as $store) {
                $new_target = array();
                $store_data = $store->getStoreData();
                if (!empty($store_data['target']))
                    foreach ($store_data['target'] as $target) {
                        foreach ($fieldset_update as $old_id => $new_id) {
                            if ($target == 'fieldset_' . $old_id)
                                $new_target[] = 'fieldset_' . $new_id;
                        }
                        foreach ($field_update as $old_id => $new_id) {
                            if ($target == 'field_' . $old_id)
                                $new_target[] = 'field_' . $new_id;
                        }
                    }
                $store->setData('target', $new_target);
                Mage::getModel('webforms/store')
                    ->setData($store->getData())
                    ->setId(null)
                    ->setEntityId($new_logic->getId())
                    ->save();
            }
        }

        // set form permission
        $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $role = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username', $username)->getFirstItem()->getRole();
        $rule_all = Mage::getModel('admin/rules')->getCollection()
            ->addFilter('role_id', $role->getId())
            ->addFilter('resource_id', 'all')
            ->getFirstItem();
        if ($rule_all->getPermission() == 'deny') {
            Mage::getModel('admin/rules')
                ->setRoleId($role->getId())
                ->setResourceId('admin/webforms/webform_' . $form->getId())
                ->setRoleType('G')
                ->setPermission('allow')
                ->save();
        }

        return $form;
    }

    public function getUploadFields()
    {
        $upload_fields = array();
        foreach ($this->getFieldsToFieldsets() as $fieldset_id => $fieldset) {
            if (isset($fieldset['fields']))
                foreach ($fieldset['fields'] as $field) {
                    if ($field->getType() == 'file' || $field->getType() == 'image')
                        $upload_fields[] = $field;
                }
        }
        return $upload_fields;
    }

    public function getUploadedFiles()
    {
        $uploaded_files = array();
        $upload_fields = $this->getUploadFields();
        foreach ($upload_fields as $field) {
            $field_id = $field->getId();
            $file_id = 'file_' . $field_id;
            $uploader = new Zend_Validate_File_Upload;
            $valid = $uploader->isValid($file_id);
            if ($valid) {
                $file = $uploader->getFiles($file_id);
                $uploaded_files[$field_id] = $file[$file_id];
            }
        }
        return $uploaded_files;
    }

    public function getUploadLimit($type = 'file')
    {
        $upload_limit = Mage::getStoreConfig('webforms/files/upload_limit');
        if ($this->getFilesUploadLimit())
            $upload_limit = $this->getFilesUploadLimit();
        if ($type == 'image') {
            $upload_limit = Mage::getStoreConfig('webforms/images/upload_limit');
            if ($this->getImagesUploadLimit())
                $upload_limit = $this->getImagesUploadLimit();
        }
        return intval($upload_limit);
    }

    public function validatePostResult()
    {
        $postData = $this->getPostData();

        if (Mage::registry('webforms_errors_flag_' . $this->getId())) return Mage::registry('webforms_errors_' . $this->getId());

        $errors = array();

        // check form key
        if (Mage::app()->getStore($this->getStoreId())->getConfig('webforms/general/formkey')) {
            if (Mage::app()->getRequest()->getPost('form_key')) {
                if (Mage::getSingleton('core/session')->getFormKey() != Mage::app()->getRequest()->getPost('form_key')) {
                    $errors[] = Mage::helper('webforms')->__('Invalid form key.');
                }
            } else {
                $errors[] = Mage::helper('webforms')->__('Form key is missing.');
            }
        }

        // check captcha
        if ($this->useCaptcha()) {
            if (Mage::app()->getRequest()->getPost('g-recaptcha-response')) {
                $verify = Mage::helper('webforms')->getCaptcha()->verify(Mage::app()->getRequest()->getPost('g-recaptcha-response'));
                if (!$verify) {
                    $errors[] = Mage::helper('webforms')->__('Verification code was not correct. Please try again.');
                }
            } else {
                $errors[] = Mage::helper('webforms')->__('Verification code was not correct. Please try again.');
            }
        }

        // check honeypot captcha
        if (Mage::getStoreConfig('webforms/honeypot/enable')) {
            if (Mage::app()->getRequest()->getPost('message')) {
                $errors[] = Mage::helper('webforms')->__('Spam bot detected. Honeypot field should be empty.');
            }
        }

        // check custom validation
        $logic_rules = $this->getLogic();
        $fields_to_fieldsets = $this->getFieldsToFieldsets();
        $fields_to_fieldsets['hidden']['fields'] = $this->_getHidden();
        $fields_to_fieldsets['hidden']['logic_visibility'] = true;
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset)
            foreach ($fieldset['fields'] as $field) {

                $hint = htmlspecialchars(trim($field->getHint()));

                $requiredFailed = false;

                if ($field->getRequired() && empty($postData[$field->getId()]) && $field->getType() == 'hidden') {
                    $requiredFailed = true;
                    $errorMsg = $field->getValidationAdvice() ? $field->getValidationAdvice() : Mage::helper('webforms')->__('%s is required', $field->getName());
                    if (!in_array($errorMsg, $errors))
                        $errors[] = $errorMsg;
                }

                if ($field->getRequired() && is_array($postData) && $field->getType() != 'file' && $field->getType() != 'image') {
                    $dataMissing = true;

                    foreach ($postData as $key => $value) {
                        if (is_array($value)) {
                            $value = implode("\n", $value);
                        }
                        $value = trim(strval($value));

                        if ($key == $field->getId()) {
                            $dataMissing = false;
                        }
                        if (
                            $key == $field->getId()
                            &&
                            ($value == $hint || $value == '')
                        ) {
                            // check logic visibility
                            $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                            $target_fieldset = array("id" => 'fieldset_' . $fieldset_id, 'logic_visibility' => $fieldset['logic_visibility']);

                            if (
                                $this->getLogicTargetVisibility($target_field, $logic_rules, $postData) &&
                                $this->getLogicTargetVisibility($target_fieldset, $logic_rules, $postData)
                            )
                            {
                                $requiredFailed = true;
                                $errorMsg = $field->getValidationAdvice() ? $field->getValidationAdvice() : Mage::helper('webforms')->__('%s is required', $field->getName());
                                if (!in_array($errorMsg, $errors))
                                    $errors[] = $errorMsg;
                            }
                        }
                    }

                    if ($dataMissing) {
                        // check logic visibility
                        $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                        $target_fieldset = array("id" => 'fieldset_' . $fieldset_id, 'logic_visibility' => $fieldset['logic_visibility']);

                        if (
                            $this->getLogicTargetVisibility($target_field, $logic_rules, $postData) &&
                            $this->getLogicTargetVisibility($target_fieldset, $logic_rules, $postData)
                        ) {
                            $requiredFailed = true;
                            $errorMsg = $field->getValidationAdvice() ? $field->getValidationAdvice() : Mage::helper('webforms')->__('%s is required', $field->getName());
                            if (!in_array($errorMsg, $errors))
                                $errors[] = $errorMsg;
                        }
                    }
                }

                // custom validation
                if ($field->getIsActive() && $field->getValidateRegex() && $field->getRequired() && !$requiredFailed) {
                    // check logic visibility
                    $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                    $target_fieldset = array("id" => 'fieldset_' . $fieldset_id, 'logic_visibility' => $fieldset['logic_visibility']);

                    if (
                        $this->getLogicTargetVisibility($target_field, $logic_rules, $postData) &&
                        $this->getLogicTargetVisibility($target_fieldset, $logic_rules, $postData)
                    ) {
                        $pattern = trim($field->getValidateRegex());

                        // clear global modifier
                        if (substr($pattern, 0, 1) == '/' && substr($pattern, -2) == '/g') $pattern = substr($pattern, 0, strlen($pattern) - 1);

                        $status = @preg_match($pattern, "Test");
                        if (false === $status) {
                            $pattern = "/" . $pattern . "/";
                        }
                        $validate = new Zend_Validate_Regex($pattern);
                        foreach ($postData as $key => $value) {
                            if ($key == $field->getId() && !$validate->isValid($value)) {
                                $errors[] = $field->getName() . ": " . $field->getValidateMessage();
                            }
                        }
                    }
                }


                // check e-mail
                if ($field->getIsActive() && $field->getType() == 'email') {
                    if (!empty($postData[$field->getId()])) {
                        $email_validate = new Zend_Validate_EmailAddress;
                        if (!$email_validate->isValid($postData[$field->getId()])) {
                            $errors[] = Mage::helper('webforms')->__('Invalid e-mail address specified.');
                        }
                        if (Mage::helper('webforms')->isInEmailStoplist($postData[$field->getId()])) {
                            $errors[] = Mage::helper('webforms')->__('E-mail address is blocked: %s', $postData[$field->getId()]);
                        }
                    }
                }

                // validate unique
                if ($field->getIsActive() && $field->getValidateUnique()) {
                    if (!empty($postData[$field->getId()])) {
                        $value = $postData[$field->getId()];
                        $count = Mage::getModel('webforms/results')->getCollection()->addFieldFilter($field->getId(), $value, true)->getSize();
                        if ($count) {
                            $errors[] = $field->getValidateUniqueMessage() ? $field->getValidateUniqueMessage() : Mage::helper('webforms')->__('Duplicate value has been found: %s', $postData[$field->getId()]);
                        }
                    }
                }
            }

        // check files
        $files = $this->getUploadedFiles();
        foreach ($files as $field_name => $file) {
            $field_id = str_replace('file_', '', $field_name);

            $field = Mage::getModel('webforms/fields')
                ->setStoreId($this->getStoreId())
                ->load($field_id);
            $errors = array_merge($errors, $field->validate($file));
        }
        $validate = new Varien_Object(array('errors' => $errors));

        Mage::dispatchEvent('webforms_validate_post_result', array('webform' => $this, 'validate' => $validate));

        Mage::register('webforms_errors_flag_' . $this->getId(), true);
        Mage::register('webforms_errors_' . $this->getId(), $validate->getData('errors'));

        return $validate->getData('errors');
    }

    public function getPost($config)
    {

        $postData = Mage::app()->getRequest()->getPost();
        if (!empty($config['prefix'])) {
            $postData = Mage::app()->getRequest()->getPost($config['prefix']);
        }
        if (empty($postData['field'])) $postData['field'] = array();

        // check visibility
        $fields_to_fieldsets = $this->getFieldsToFieldsets();
        $logic_rules = $this->getLogic(true);
        foreach ($fields_to_fieldsets as $fieldset) {
            foreach ($fieldset['fields'] as $field) {
                $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                $field_visibility = $this->getLogicTargetVisibility($target_field, $logic_rules, $postData['field']);
                $field->setData('visible', $field_visibility);
                if (!$field_visibility) {
                    $postData['field'][$field->getId()] = '';
                }
            }
        }
        return $postData;
    }

    public function savePostResult($config = array())
    {
        try {
            $postData = $this->getPost($config);

            $result = Mage::getModel('webforms/results');

            $new_result = true;
            if (!empty($postData['result_id'])) {
                $new_result = false;
                $result->load($postData['result_id'])->addFieldArray(false, array('select/radio', 'select/checkbox'));

                foreach ($result->getData('field') as $key => $value) {
                    if (!array_key_exists($key, $postData['field'])) {
                        $postData['field'][$key] = '';
                    }
                }

            }

            if (empty($postData['field'])) $postData['field'] = array();

            $this->setData('post_data', $postData['field']);

            $errors = $this->validatePostResult();

            if (count($errors)) {
                foreach ($errors as $error) {
                    Mage::getSingleton('core/session')->addError($error);
                    if (Mage::app()->getStore($this->getStoreId())->getConfig('webforms/general/store_temp_submission_data'))
                        Mage::getSingleton('core/session')->setData('webform_result_tmp_' . $this->getId(), $postData);
                }
                return false;
            }

            Mage::getSingleton('core/session')->setData('webform_result_tmp_' . $this->getId(), false);

            $iplong = ip2long(Mage::helper('webforms')->getRealIp());

            $files = $this->getUploadedFiles();
            foreach ($files as $field_name => $file) {
                $field_id = str_replace('file_', '', $field_name);
                if ($file['name']) {
                    $postData['field'][$field_id] = $file['name'];
                }

            }

            // delete files

            foreach ($this->_getFieldsToFieldsets() as $fieldset) {
                foreach ($fieldset['fields'] as $field) {
                    if ($field->getType() == 'file' || $field->getType() == 'image') {
                        if (!empty($postData['delete_file_' . $field->getId()]) && is_array($postData['delete_file_' . $field->getId()])) {
                            foreach ($postData['delete_file_' . $field->getId()] as $link_hash) {
                                $resultFiles = Mage::getModel('webforms/files')->getCollection()
                                    ->addFilter('link_hash', $link_hash);
                                foreach ($resultFiles as $resultFile) {
                                    $resultFile->delete();
                                }
                            }
                        }
                    }
                }
            }

            if ($new_result) {
                $approve = 1;
                if ($this->getApprove()) $approve = 0;
            }

            $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
            if (!empty($config['customer_id']))
                $customer_id = $config['customer_id'];

            $result->setData('field', $postData['field'])
                ->setWebformId($this->getId())
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setCustomerId($customer_id)
                ->setCustomerIp($iplong);
            if (!empty($approve))
                $result->setApproved($approve);
            $result->setWebform($this);
            $result->save();


            // upload files
            $result->getUploader()->upload();

            Mage::dispatchEvent('webforms_result_submit', array('result' => $result, 'webform' => $this));

            // send e-mail

            if ($new_result) {

                $emailSettings = $this->getEmailSettings();

                // send admin notification
                if ($emailSettings['email_enable']) {
                    $result->sendEmail();
                }

                // send customer notification
                if ($this->getDuplicateEmail()) {
                    $result->sendEmail('customer');
                }
                // email contact
                $logic_rules = $this->getLogic();
                $fields_to_fieldsets = $this->_getFieldsToFieldsets();
                foreach ($fields_to_fieldsets as $fieldset_id => $fieldset)
                    foreach ($fieldset['fields'] as $field) {
                        foreach ($result->getData() as $key => $value) {

                            if ($key == 'field_' . $field->getId() && strlen($value) && $field->getType() == 'select/contact') {
                                $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                                if ($this->getLogicTargetVisibility($target_field, $logic_rules, $result->getData('field'))) {
                                    $contactInfo = $field->getContactArray($value);
                                    if (strstr($contactInfo['email'], ',')) {
                                        $contactEmails = explode(',', $contactInfo['email']);
                                        foreach ($contactEmails as $cEmail) {
                                            $result->sendEmail('contact', array('name' => $contactInfo['name'], 'email' => $cEmail));
                                        }
                                    } else {
                                        $result->sendEmail('contact', $contactInfo);
                                    }
                                }
                            }

                            if ($key == 'field_' . $field->getId() && $value && $field->getType() == 'subscribe') {
                                // subscribe to newsletter
                                $customer_email = $result->getCustomerEmail();
                                foreach ($customer_email as $email) {
                                    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                                    if (!$subscriber->getId() && $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                                        Mage::getModel('newsletter/subscriber')->subscribe($email);
                                }
                            }

                        }
                    }

            }
            $result->resizeImages();

            Mage::getModel('webforms/dropzone')->cleanup();

            return $result;
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            return false;
        }
    }

    public function getLogic($active = false)
    {
        $collection = Mage::getModel('webforms/logic')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addWebformFilter($this->getId());
        if ($active)
            $collection->addFilter('main_table.is_active', 1);
        return $collection;
    }

    public function getLogicTargetVisibility($target, $logic_rules, $data)
    {
        $logic = Mage::getModel('webforms/logic');
        $field_map = array();
        foreach ($this->_fields_to_fieldsets as $fieldset_id => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                $field_map['fieldset_' . $fieldset_id][] = $field->getId();
            }
        }
        return $logic->getTargetVisibility($target, $logic_rules, $data, $field_map);
    }

    public function getSubmitButtonText()
    {
        $submit_button_text = trim($this->getData('submit_button_text'));
        if (strlen($submit_button_text) == 0)
            $submit_button_text = 'Submit';
        return $submit_button_text;
    }

    public function getAdminPermission()
    {
        // filter by role permissions
        $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $role = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username', $username)->getFirstItem()->getRole();
        $rule_all = Mage::getModel('admin/rules')->getCollection()
            ->addFilter('role_id', $role->getId())
            ->addFilter('resource_id', 'all')
            ->getFirstItem();
        if ($rule_all->getPermission() == 'allow') return 'allow';

        return Mage::getModel('admin/rules')->getCollection()
            ->addFilter('role_id', $role->getId())
            ->addFilter('resource_id', 'admin/webforms/webform_' . $this->getId())
            ->getFirstItem()
            ->getPermission();

    }

    public function canAccess()
    {
        if ($this->getAccessEnable()) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if (in_array($groupId, $this->getAccessGroups()))
                return true;
            return false;
        }
        return true;
    }

    public function getStatusEmailTemplateId($status)
    {
        switch ($status) {
            case VladimirPopov_WebForms_Model_Results::STATUS_APPROVED:
                return $this->getEmailTemplateApproved();
            case VladimirPopov_WebForms_Model_Results::STATUS_NOTAPPROVED:
                return $this->getEmailTemplateNotapproved();
            case VladimirPopov_WebForms_Model_Results::STATUS_COMPLETED:
                return $this->getEmailTemplateCompleted();
        }
    }

    public function toJson(array $arrAttributes = array())
    {
        $data = $this->getData();

        unset(
            $data['id'],
            $data['email_template_id'],
            $data['email_customer_template_id'],
            $data['email_reply_template_id'],
            $data['email_result_approved_template_id'],
            $data['email_result_completed_template_id'],
            $data['email_result_notapproved_template_id'],
            $data['customer_print_template_id'],
            $data['approved_print_template_id'],
            $data['completed_print_template_id'],
            $data['created_time'],
            $data['update_time'],
            $data['is_active'],
            $data['access_groups'],
            $data['access_groups_serialized'],
            $data['dashboard_groups'],
            $data['dashboard_groups_serialized'],
            $data['access_enable'],
            $data['dashboard_enable']);

        /* export store view data */

        $data['store_data'] = array();

        $storeDataArray = Mage::getModel('webforms/store')->getCollection()
            ->addFilter('entity_id', $this->getId())
            ->addFilter('entity_type', VladimirPopov_WebForms_Model_Mysql4_Webforms::ENTITY_TYPE);

        foreach ($storeDataArray as $storeData) {
            $storeCode = Mage::app()->getStore($storeData['store_id'])->getCode();
            $data['store_data'][$storeCode] = unserialize($storeData['store_data']);
        }

        $data['fields'] = array();
        $data['fieldsets'] = array();

        foreach ($this->getFieldsToFieldsets(true) as $fsId => $fsArray) {
            $fieldset = Mage::getModel('webforms/fieldsets')->load($fsId);
            $fsData = $fieldset->getData();
            $fsData['tmp_id'] = $fsId;
            if ($fsId == 0) {
                foreach ($fsArray['fields'] as $field) {
                    $fData = $field->getData();
                    $fData['tmp_id'] = $fData['id'];
                    unset(
                        $fData['id'],
                        $fData['webform_id'],
                        $fData['fieldset_id'],
                        $fData['created_time'],
                        $fData['update_time']
                    );
                    $fData['store_data'] = array();
                    $storeDataArray = Mage::getModel('webforms/store')->getCollection()
                        ->addFilter('entity_id', $field->getId())
                        ->addFilter('entity_type', VladimirPopov_WebForms_Model_Mysql4_Fields::ENTITY_TYPE);

                    foreach ($storeDataArray as $storeData) {
                        $storeCode = Mage::app()->getStore($storeData['store_id'])->getCode();
                        $fData['store_data'][$storeCode] = unserialize($storeData['store_data']);
                    }

                    $data['fields'][] = $fData;
                }
            } else {
                unset(
                    $fsData['id'],
                    $fsData['webform_id'],
                    $fsData['created_time'],
                    $fsData['update_time']
                );
                $fsData['store_data'] = array();
                $storeDataArray = Mage::getModel('webforms/store')->getCollection()
                    ->addFilter('entity_id', $fieldset->getId())
                    ->addFilter('entity_type', VladimirPopov_WebForms_Model_Mysql4_Fieldsets::ENTITY_TYPE);

                foreach ($storeDataArray as $storeData) {
                    $storeCode = Mage::app()->getStore($storeData['store_id'])->getCode();
                    $fsData['store_data'][$storeCode] = unserialize($storeData['store_data']);
                }

                $fsData['fields'] = array();
                foreach ($fsArray['fields'] as $field) {
                    $fData = $field->getData();
                    $fData['tmp_id'] = $fData['id'];
                    unset(
                        $fData['id'],
                        $fData['webform_id'],
                        $fData['fieldset_id'],
                        $fData['created_time'],
                        $fData['update_time']
                    );
                    $fData['store_data'] = array();
                    $storeDataArray = Mage::getModel('webforms/store')->getCollection()
                        ->addFilter('entity_id', $field->getId())
                        ->addFilter('entity_type', VladimirPopov_WebForms_Model_Mysql4_Fields::ENTITY_TYPE);

                    foreach ($storeDataArray as $storeData) {
                        $storeCode = Mage::app()->getStore($storeData['store_id'])->getCode();
                        $fData['store_data'][$storeCode] = unserialize($storeData['store_data']);
                    }

                    $fsData['fields'][] = $fData;
                }
                $data['fieldsets'][] = $fsData;
            }
        }

        /* export logic */

        $data['logic'] = array();

        $logic = $this->getLogic();
        foreach ($logic as $l) {
            $lData = $l->getData();
            unset(
                $lData['id'],
                $lData['webform_id'],
                $lData['created_time'],
                $lData['value_serialized'],
                $lData['target_serialized'],
                $lData['update_time']
            );
            $lData['store_data'] = array();
            $storeDataArray = Mage::getModel('webforms/store')->getCollection()
                ->addFilter('entity_id', $l->getId())
                ->addFilter('entity_type', VladimirPopov_WebForms_Model_Mysql4_Logic::ENTITY_TYPE);

            foreach ($storeDataArray as $storeData) {
                $storeCode = Mage::app()->getStore($storeData['store_id'])->getCode();
                $lData['store_data'][$storeCode] = unserialize($storeData['store_data']);
            }
            $data['logic'][] = $lData;
        }
        return json_encode($data);
    }

    public function parseJson($jsonData)
    {
        $errors = array();
        $warnings = array();

        $data = json_decode($jsonData, true);

        if (!$data) {
            $errors[] = Mage::helper('webforms')->__('Incorrect JSON data');
            return array('errors' => $errors, 'warnings' => $warnings);
        }

        if (empty($data["name"]))
            $errors[] = Mage::helper('webforms')->__('Missing form name');

        if (!empty($data["fields"])) {
            foreach ($data["fields"] as $field) {
                if (empty($field["name"]))
                    $errors[] = Mage::helper('webforms')->__('Missing field name');
                if (empty($field["type"]))
                    $errors[] = Mage::helper('webforms')->__('Field type not defined');
            }
            if (!empty($field['store_data'])) {
                foreach ($field['store_data'] as $storeCode => $storeData) {
                    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                    if (!$storeId) {
                        $text = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                        if (!in_array($text, $warnings))
                            $warnings[] = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                    }
                }
            }
        }

        if (!empty($data["fieldsets"])) {
            foreach ($data["fieldsets"] as $fieldset) {
                if (empty($fieldset["name"]))
                    $errors[] = Mage::helper('webforms')->__('Fieldset found and missing name');
                if (!empty($fieldset["fields"])) {
                    foreach ($fieldset["fields"] as $field) {
                        if (empty($field["name"]))
                            $errors[] = Mage::helper('webforms')->__('Missing field name');
                        if (empty($field["type"]))
                            $errors[] = Mage::helper('webforms')->__('Field type not defined');
                        if (!empty($field['store_data'])) {
                            foreach ($field['store_data'] as $storeCode => $storeData) {
                                $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                                if (!$storeId) {
                                    $text = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                                    if (!in_array($text, $warnings))
                                        $warnings[] = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                                }
                            }
                        }
                    }
                }
                if (!empty($fieldset['store_data'])) {
                    foreach ($fieldset['store_data'] as $storeCode => $storeData) {
                        $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                        if (!$storeId) {
                            $text = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                            if (!in_array($text, $warnings))
                                $warnings[] = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                        }
                    }
                }
            }
        }

        if (!empty($data['store_data'])) {
            foreach ($data['store_data'] as $storeCode => $storeData) {
                $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                if (!$storeId) {
                    $text = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                    if (!in_array($text, $warnings))
                        $warnings[] = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                }
            }
        }

        if (!empty($data['logic'])) {
            foreach ($data['logic'] as $l) {
                if (empty($l['field_id']))
                    $warnings[] = Mage::helper('webforms')->__('Logic rule is missing trigger field');
                if (empty($l['value']))
                    $warnings[] = Mage::helper('webforms')->__('Logic rule is missing value');
                if (empty($l['target']))
                    $warnings[] = Mage::helper('webforms')->__('Logic rule is missing target');
                if (empty($l['action']))
                    $warnings[] = Mage::helper('webforms')->__('Logic rule is missing action');

                if (!empty($l['store_data'])) {
                    foreach ($l['store_data'] as $storeCode => $storeData) {
                        $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                        if (!$storeId) {
                            $text = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                            if (!in_array($text, $warnings))
                                $warnings[] = Mage::helper('webforms')->__('Store view contained within data not found: %s', $storeCode);
                        }
                    }
                }
            }
        }

        return array('errors' => $errors, 'warnings' => $warnings);
    }

    public function import($jsonData)
    {
        $parse = $this->parseJson($jsonData);

        if ($parse['errors'])
            return $this;

        $data = json_decode($jsonData, true);
        $this->setData($data);
        $this->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
        $this->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
        $this->save();

        // transitional matrix for logic rules
        $logicMatrix = array();

        if ($this->getId()) {

            // import fields
            if (!empty($data['fields'])) {

                foreach ($data['fields'] as $fieldData) {

                    /** @var VladimirPopov_WebForms_Model_Fields $fieldModel */
                    $fieldModel = Mage::getModel('webforms/fields')->setData($fieldData);
                    $fieldModel->setData('webform_id', $this->getId());
                    $fieldModel->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                    $fieldModel->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
                    $fieldModel->save();
                    $logicMatrix['field_' . $fieldData['tmp_id']] = $fieldModel->getId();

                    // import store data
                    if (!empty($fieldData['store_data'])) {
                        foreach ($fieldData['store_data'] as $storeCode => $storeData) {
                            $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                            if ($storeId) {
                                $fieldModel->saveStoreData($storeId, $storeData);
                            }
                        }
                    }
                }
            }

            // import fieldsets
            if (!empty($data['fieldsets'])) {

                foreach ($data['fieldsets'] as $fieldsetData) {

                    /** @var VladimirPopov_WebForms_Model_Fieldsets $fieldsetModel */
                    $fieldsetModel = Mage::getModel('webforms/fieldsets')->setData($fieldsetData);
                    $fieldsetModel->setData('webform_id', $this->getId());
                    $fieldsetModel->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                    $fieldsetModel->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
                    $fieldsetModel->save();
                    $logicMatrix['fieldset_' . $fieldsetData['tmp_id']] = $fieldsetModel->getId();

                    // import store data
                    if (!empty($fieldsetData['store_data'])) {
                        foreach ($fieldsetData['store_data'] as $storeCode => $storeData) {
                            $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                            if ($storeId) {
                                $fieldsetModel->saveStoreData($storeId, $storeData);
                            }
                        }
                    }

                    if (!empty($fieldsetData['fields'])) {
                        foreach ($fieldsetData['fields'] as $fieldData) {

                            /** @var VladimirPopov_WebForms_Model_Fields $fieldModel */
                            $fieldModel = Mage::getModel('webforms/fields')->setData($fieldData);
                            $fieldModel->setData('fieldset_id', $fieldsetModel->getId());
                            $fieldModel->setData('webform_id', $this->getId());
                            $fieldModel->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                            $fieldModel->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
                            $fieldModel->save();
                            $logicMatrix['field_' . $fieldData['tmp_id']] = $fieldModel->getId();

                            // import store data
                            if (!empty($fieldData['store_data'])) {
                                foreach ($fieldData['store_data'] as $storeCode => $storeData) {
                                    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                                    if ($storeId) {
                                        $fieldModel->saveStoreData($storeId, $storeData);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // import logic rules
            if (!empty($data['logic'])) {

                foreach ($data['logic'] as $logicData) {

                    /** @var VladimirPopov_WebForms_Model_Logic $logicModel */
                    $logicModel = Mage::getModel('webforms/logic')->setData($logicData);
                    $logicModel->setData('field_id', $logicMatrix['field_' . $logicData['field_id']]);
                    $target = array();
                    foreach ($logicData['target'] as $targetData) {
                        $prefix = 'field_';
                        if (strstr($targetData, 'fieldset_')) $prefix = 'fieldset_';
                        if (!empty($logicMatrix[$targetData])) $target[] = $prefix . $logicMatrix[$targetData];
                    }

                    $logicModel->setData('target', $target);
                    $logicModel->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                    $logicModel->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
                    $logicModel->save();

                    // import store data
                    if (!empty($logicData['store_data'])) {
                        foreach ($logicData['store_data'] as $storeCode => $storeData) {
                            $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();

                            if ($storeId) {
                                $target = array();
                                foreach ($storeData['target'] as $targetData) {
                                    $prefix = 'field_';
                                    if (strstr($targetData, 'fieldset_')) $prefix = 'fieldset_';
                                    if (!empty($logicMatrix[$targetData])) $target[] = $prefix . $logicMatrix[$targetData];
                                }
                                $storeData['target'] = $target;
                                $logicModel->saveStoreData($storeId, $storeData);
                            }
                        }
                    }
                }
            }

            // import store data
            if (!empty($data['store_data'])) {
                foreach ($data['store_data'] as $storeCode => $storeData) {
                    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();
                    if ($storeId) {
                        $this->saveStoreData($storeId, $storeData);
                    }
                }
            }
        }
        return $this;
    }
}