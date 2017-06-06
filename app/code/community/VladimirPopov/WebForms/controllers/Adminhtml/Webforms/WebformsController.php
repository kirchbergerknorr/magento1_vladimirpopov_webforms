<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_WebFormsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webforms/webforms');
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Manage Forms'));
        return $this;
    }

    public function indexAction()
    {
        // delete old upload folder
        $old_upload_folder = Mage::getBaseDir() . DS . 'js' . DS . 'webforms' . DS . 'upload';
        if (is_dir($old_upload_folder)) {
            try {
                Mage::helper('webforms')->rrmdir($old_upload_folder);
                if (is_dir($old_upload_folder)) {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Upgrade script error! Please remove the following folder manually: js/webforms/upload'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Upgrade script error! Please remove the following folder manually: js/webforms/upload'));
            }
        }

        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_webforms_grid')->toHtml()
        );
    }

    public function logicAction()
    {
        $webformsId = $this->getRequest()->getParam('webform_id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->load($webformsId);
        if ($webformsModel->getId() || $webformsId == 0) {
            Mage::register('webforms_data', $webformsModel);
            $this->loadLayout();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_logic')->toHtml()
            );
        }
    }

    public function editAction()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 || Mage::helper('webforms')->getMageEdition() == 'EE')
            $this->_title($this->__('Web-forms'))->_title($this->__('Edit Form'));

        $webformsId = $this->getRequest()->getParam('id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->load($webformsId);

        if ($webformsModel->getId() && $webformsModel->getAdminPermission() != 'allow') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Access denied.'));
            return $this->_redirect('*/*/');
        }

        if ($webformsModel->getId() || $webformsId == 0) {
            Mage::register('webforms_data', $webformsModel);
            $this->loadLayout();
            $this->_setActiveMenu('webforms/webforms');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Web-forms'), Mage::helper('adminhtml')->__('Web-forms'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('webforms/adminhtml_webforms_edit'))
                ->_addLeft($this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Web-form does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $id = $this->getRequest()->getParam('id');
                $postData = $this->getRequest()->getPost('form');

                $form = Mage::getModel('webforms/webforms')->setId($id);

                $store = Mage::app()->getRequest()->getParam('store');
                if ($store)
                    $form->saveStoreData($store, $postData);
                else
                    $form->setData($postData)->setId($id)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())->save();

                // update fields position
                $fieldsData = $this->getRequest()->getParam('fields');
                if (is_array($fieldsData))
                    foreach ($fieldsData['position'] as $field_id => $position) {
                        Mage::getModel('webforms/fields')->setId($field_id)->setPosition($position)->save();
                    }

                // update fieldsets position
                $fieldsetsData = $this->getRequest()->getParam('fieldsets');
                if (is_array($fieldsetsData))
                    foreach ($fieldsetsData['position'] as $fieldset_id => $position) {
                        Mage::getModel('webforms/fieldsets')->setId($fieldset_id)->setPosition($position)->save();
                    }

                if ($this->getRequest()->getParam('id') <= 0) {
                    $form->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())->save();

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
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setWebFormsData(false);


                // check if 'Save and Continue'
                $redirectBack = $this->getRequest()->getParam('back', false);
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $form->getId(), 'store' => $store, 'active_tab' => $this->getRequest()->getParam('active_tab')));
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setWebFormsData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'store' => $store));
                return;
            }

        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $webformsModel = Mage::getModel('webforms/webforms');
                $webformsModel->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $form = Mage::getModel('webforms/webforms')->load($id)->duplicate();
        if ($form->getId()) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully duplicated'));
            $this->_redirect('*/*/edit', array('id' => $form->getId()));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Error duplicating web-form'));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
    }


    public function massStatusAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        $status = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/webforms')->load($id);
                $result->setData('is_active', $status);
                $result->save();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }

        $this->_redirect('*/*/index');


    }

    public function massDuplicateAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $webform = Mage::getModel('webforms/webforms')->load($id);
                $form = $webform->duplicate();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been duplicated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while duplicating records.'));
        }

        $this->_redirect('*/*/');

    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/webforms')->load($id);
                $result->delete();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }

        $this->_redirect('*/*/');

    }

    public function exportAction()
    {
        $webformsId = $this->getRequest()->getParam('id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->load($webformsId);

        if ($webformsModel->getId() && $webformsModel->getAdminPermission() != 'allow') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Access denied.'));
            return $this->_redirect('*/*/');
        }

        $this->_prepareDownloadResponse($webformsModel->getName() . '.json', $webformsModel->toJson(), 'application/json');
    }

    protected function importAction()
    {
        $upload = new Zend_Validate_File_Upload();
        $file = $upload->getFiles('import_form');

        $webformsModel = Mage::getModel('webforms/webforms');

        if ($file) {
            $importData = file_get_contents($file['import_form']['tmp_name']);

            $parse = $webformsModel->parseJson($importData);

            if (empty($parse['errors'])) {
                $webformsModel->import($importData);
                if ($webformsModel->getId()) {
                    $this->_getSession()->addSuccess($this->__('Form "%s" successfully imported.', $webformsModel->getName()));
                } else {
                    $this->_getSession()->addError($this->__('Unknown error happened during import operation.'));
                }
            } else {
                foreach ($parse['errors'] as $error) {
                    $this->_getSession()->addError($error);
                }
            }

            if (!empty($parse['warnings'])) {
                foreach ($parse['warnings'] as $warning) {
                    $this->_getSession()->addWarning($warning);
                }
            }

            return $this->_redirect('*/*/index');
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The uploaded file contains invalid data.'));

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        $session = Mage::getSingleton('admin/session');
        $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/forms');
    }
}