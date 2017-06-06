<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_LogicController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initObjects()
    {
        $logic_id = $this->getRequest()->getParam('id');
        $field_id = $this->getRequest()->getParam('field_id');
        $store = Mage::app()->getRequest()->getParam('store');
        $webform = Mage::getModel('webforms/webforms')->setStoreId($store);
        $logic = Mage::getModel('webforms/logic')->setStoreId($store);
        $field = Mage::getModel('webforms/fields')->setStoreId($store);
        if ($store) {
            $logic->setStoreId($store);
        }
        $logic->load($logic_id);
        if ($logic->getFieldId()) {
            $field_id = $logic->getFieldId();
        }

        if ($field_id) {
            $field->load($field_id);
            $logic->setFieldId($field_id);
        }

        if ($field->getWebformId()) $webform->load($field->getWebformId());

        Mage::register('field', $field);
        Mage::register('logic', $logic);
        Mage::register('webform', $webform);
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('webforms/webforms');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_initObjects();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();

        $this->_initObjects();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_fields_edit_tab_logic')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Edit Logic'));

        $this->_initObjects();

        if (Mage::registry('webform')->getId()) {

            $this->loadLayout();
            $this->_setActiveMenu('webforms/webforms');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Web-forms'), Mage::helper('adminhtml')->__('Web-forms'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('webforms/adminhtml_logic_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Web-form does not exist'));
            $this->_redirect('*/webforms_webforms/index');
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $id = $this->getRequest()->getParam('id');
                $postData = $this->getRequest()->getPost('logic');
                $field_id = $postData["field_id"];
                $saveandcontinue = $postData["saveandcontinue"];
                unset($postData["saveandcontinue"]);

                $logic = Mage::getModel('webforms/logic');

                $logic->setId($id);

                $store = Mage::app()->getRequest()->getParam('store');
                if ($store) {
                    unset($postData["field_id"]);
                    $logic->saveStoreData($store, $postData);
                } else
                    $logic->setData($postData)->setId($id)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())->save();

                if ($id <= 0)
                    $logic->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Logic was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setWebFormsData(false);

                if ($saveandcontinue) {
                    $this->_redirect('*/webforms_logic/edit', array('id' => $logic->getId(), 'webform_id' => $this->getRequest()->getParam('webform_id'), 'store' => $store));
                } else {
                    if ($this->getRequest()->getParam('webform_id')) {
                        $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'logic', 'store' => $store));
                    } else
                        $this->_redirect('*/webforms_fields/edit', array('id' => $field_id, 'tab' => 'logic', 'store' => $store));
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setWebFormsData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'store' => $store));
                return;
            }

        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Unexpected error'));
        $this->_redirect('*/webforms_webforms/index');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $logic = Mage::getModel('webforms/logic');
                $logic->load($this->getRequest()->getParam('id'));
                $field_id = $logic->getFieldId();
                $logic->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Logic was successfully deleted'));
                if ($this->getRequest()->getParam('webform_id')) {
                    $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'logic'));
                } else
                    $this->_redirect('*/webforms_fields/edit', array('id' => $field_id, 'tab' => 'logic'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Unexpected error'));
        $this->_redirect('*/webforms_webforms/index');
    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        $store = $this->getRequest()->getParam('store');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/logic')->load($id);
                $result->delete();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }
        if ($this->getRequest()->getParam('webform_id')) {
            $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'logic', 'store' => $store));
            return;
        }
        $this->_redirect('*/webforms_fields/edit', array('id' => $this->getRequest()->getParam('field_id'), 'tab' => 'logic', 'store' => $store));
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
                    $result = Mage::getModel('webforms/logic')->setId($id);
                    $result->updateStoreData($store, $data);
                } else {
                    $result = Mage::getModel('webforms/logic')->load($id);
                    $result->setData('is_active', $status);
                    $result->save();
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }
        if ($this->getRequest()->getParam('webform_id')) {
            $this->_redirect('*/webforms_webforms/edit', array('id' => $this->getRequest()->getParam('webform_id'), 'tab' => 'logic', 'store' => $store));
            return;
        }
        $this->_redirect('*/webforms_fields/edit', array('id' => $this->getRequest()->getParam('field_id'), 'tab' => 'logic', 'store' => $store));
    }

    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('webform_id')){
            return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_'.$this->getRequest()->getParam('webform_id'));
        }
        if($this->getRequest()->getParam('field_id')){
            $field = Mage::getModel('webforms/fields')->load($this->getRequest()->getParam('field_id'));
            return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_'.$field->getWebformId());
        }
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms');
    }
}