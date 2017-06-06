<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_FieldsetsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webforms/webforms');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_fieldsets')->toHtml()
        );
    }

    public function editAction()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Edit Field Set'));
        $fieldsetId = $this->getRequest()->getParam('id');
        $webformsId = $this->getRequest()->getParam('webform_id');
        $fieldset = Mage::getModel('webforms/fieldsets');
        $store = Mage::app()->getRequest()->getParam('store');
        if ($store) {
            $fieldset->setStoreId($store);
        }
        $fieldset->load($fieldsetId);
        if ($fieldset->getWebformId()) {
            $webformsId = $fieldset->getWebformId();
        }
        $webformsModel = Mage::getModel('webforms/webforms')->load($webformsId);

        if ($fieldset->getId() || $fieldsetId == 0) {
            Mage::register('webforms_data', $webformsModel);
            Mage::register('fieldsets_data', $fieldset);

            $this->loadLayout();
            $this->_setActiveMenu('webforms/webforms');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Web-forms'), Mage::helper('adminhtml')->__('Web-forms'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('webforms/adminhtml_fieldsets_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Fieldset does not exist'));
            $this->_redirect('*/webforms_webforms/edit', array('id' => $webformsId));
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
                $postData = $this->getRequest()->getPost('fieldset');
                $webform_id = $postData["webform_id"];
                $saveandcontinue = $postData["saveandcontinue"];
                unset($postData["saveandcontinue"]);

                $fieldset = Mage::getModel('webforms/fieldsets');

                $fieldset->setId($id);

                $store = Mage::app()->getRequest()->getParam('store');
                if ($store) {
                    unset($postData["webform_id"]);
                    $fieldset->saveStoreData($store, $postData);
                } else
                    $fieldset->setData($postData)->setId($id)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())->save();

                if ($this->getRequest()->getParam('id') <= 0)
                    $fieldset->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Fieldset was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setWebFormsData(false);

                if ($saveandcontinue) {
                    $this->_redirect('*/webforms_fieldsets/edit', array('id' => $fieldset->getId(), 'webform_id' => $webform_id, 'store' => $store));
                } else {
                    $this->_redirect('*/webforms_webforms/edit', array('id' => $webform_id, 'tab' => 'form_fieldsets', 'store' => $store));
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setWebFormsData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'store' => $store));
                return;
            }

        }
        $this->_redirect('*/webforms_webforms');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $fieldset = Mage::getModel('webforms/fieldsets');
                $fieldset->load($this->getRequest()->getParam('id'));
                $webform_id = $fieldset->getWebformId();
                $fieldset->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Fieldset was successfully deleted'));
                $this->_redirect('*/webforms/edit', array('id' => $webform_id, 'tab' => 'form_fieldsets'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/webforms_webforms/edit', array('id' => $webform_id, 'tab' => 'form_fieldsets'));
    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/fieldsets')->load($id);
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

        $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'form_fieldsets'));
    }

    public function massStatusAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        $status = (int)$this->getRequest()->getParam('status');
        $store = $this->getRequest()->getParam('store');
        $data = array('is_active' => $status);

        try {
            foreach ($Ids as $id) {
                if ($store) {
                    $result = Mage::getModel('webforms/fieldsets')->setId($id);
                    $result->updateStoreData($store, $data);
                } else {
                    $result = Mage::getModel('webforms/fieldsets')->load($id);
                    $result->setData('is_active', $status);
                    $result->save();
                }
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

        $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'form_fieldsets', 'store' => $store));
    }

    public function massDuplicateAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/fieldsets')->load($id);
                $result->duplicate();
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

        $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'form_fieldsets'));
    }


    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('webform_id')) {
            return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_' . $this->getRequest()->getParam('webform_id'));
        }
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms');
    }
}