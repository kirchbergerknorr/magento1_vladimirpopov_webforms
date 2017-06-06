<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_ResultsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webforms/webforms');
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Results'));
        return $this;
    }

    public function indexAction()
    {

        $webformsId = $this->getRequest()->getParam('webform_id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->load($webformsId);

        if ($webformsModel->getId() && $webformsModel->getAdminPermission() != 'allow') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Access denied.'));
            return $this->_redirect('*/webforms/');
        }

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('webforms/adminhtml_results_edit', 'edit')
        );
        $this->renderLayout();
    }

    public function saveAction()
    {

        $postData = Mage::app()->getRequest()->getPost('result');
        $saveandcontinue = $postData["saveandcontinue"];
        $customerId = Mage::app()->getRequest()->getParam('customer_id');
        $result = Mage::getModel('webforms/results');

        if ($postData['result_id']) {
            $result->load($postData['result_id']);
            $webformId = $result->getWebformId();
        } else
            $webformId = $postData['webform_id'];

        if ($webformId) {

            $webform = Mage::getModel('webforms/webforms')->load($webformId);
            $webform->setData('disable_captcha', true);
            if ($postData['store_id'])
                $storeId = $postData['store_id'];
            else
                $storeId = $result->getStoreId();
            $result = $webform->savePostResult(
                array(
                    'prefix' => 'result'
                )
            );
            if ($result) {
                if ($postData['customer_id'])
                    $result->setCustomerId($postData['customer_id']);
                $result->setStoreId($storeId)->save();
            }

            // if we get validation error
            if (!$result) {
                if ($postData['result_id']) {
                    $resultId = $postData['result_id'];
                    if ($customerId) {
                        $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
                        return;
                    }
                    $this->_redirect('*/webforms_results/edit', array('_current' => true, 'id' => $resultId));
                    return;
                }
                $this->_redirect('*/webforms_results/new', array('webform_id' => $webformId));
                return;
            }

            // recover store id
            Mage::getModel('webforms/results')->load($result->getId())->setStoreId($storeId)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('webforms')->__('Result was successfully saved'));

            if ($saveandcontinue) {
                $this->_redirect('*/webforms_results/edit', array('_current' => true, 'id' => $result->getId()));
            } else {
                if ($customerId) {
                    $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
                    return;
                }
                $this->_redirect('*/webforms_results/index', array('webform_id' => $webformId));
            }
        }
    }

    public function replyAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('webforms/adminhtml_reply', 'reply')
        );
        $this->renderLayout();
    }

    public function saveMessageAction()
    {
        $post = $this->getRequest()->getPost('reply');
        $Ids = unserialize($post['result_id']);

        $user = Mage::getModel('admin/user')->load(Mage::helper('adminhtml')->getCurrentUserId());
        $i = 0;

        $filter = Mage::helper('cms')->getPageTemplateProcessor();

        $customerId = $this->getRequest()->getParam('customer_id');

        foreach ($Ids as $id) {
            $result = Mage::getModel('webforms/results')->load($id);

            /** @var VladimirPopov_WebForms_Model_Message $message */
            $message = Mage::getModel('webforms/message')
                ->setAuthor($user->getName())
                ->setUserId($user->getId())
                ->setResultId($id)
                ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                ->save();

            // add template processing
            $filter->setStoreId($result->getStoreId());
            $filter->setVariables($message->getTemplateVars());
            $content = $filter->filter($post['message']);
            if (Mage::getStoreConfig('webforms/message/nl2br', $result->getStoreId())) {
                $content = str_replace("</p><br>", "</p>", nl2br($content, true));
            }

            $message->setMessage($content)->save();


            if ($post['email']) {

                if ($result->getCustomerEmail()) {

                    $success = $message->sendEmail();

                    if ($success) {
                        $i++;
                        $message->setIsCustomerEmailed(1)->save();
                    }
                }

            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d reply(s) has been saved.', count($Ids)));

        if ($i) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d reply(s) has been emailed.', $i));
        }

        if ($post['email'] && $i < count($Ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Total of %d result(s) has no reply-to e-mail address.', count($Ids) - $i));
        }

        if ($customerId) {
            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
            return;
        }

        $this->_redirect('*/*/', array('webform_id' => $post['webform_id']));
    }

    public function gridAction()
    {
        $this->loadLayout();
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')
                ->setStoreId($this->getRequest()->getParam('store'))
                ->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_results_grid')->toHtml()
        );
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $result = Mage::getModel('webforms/results')->load($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Result was successfully deleted'));
                $customerId = $this->getRequest()->getParam('customer_id');
                if ($customerId) {
                    $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
                    return;
                }
                $this->_redirect('*/*/', array('webform_id' => $result->getWebformId()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $fileName = 'results.csv';
        $content = Mage::app()->getLayout()->createBlock('webforms/adminhtml_results_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $fileName = 'results.xml';
        $content = $this->getLayout()->createBlock('webforms/adminhtml_results_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massEmailAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        try {
            $k = 0;
            $contact = false;
            $recipient = 'admin';
            if ($this->getRequest()->getParam('recipient_email')) {
                $contact = array(
                    'name' => $this->getRequest()->getParam('recipient_email'),
                    'email' => $this->getRequest()->getParam('recipient_email'));
                $recipient = 'contact';
            }
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
                $success = $result->sendEmail($recipient, $contact);
                if ($success) $k++;
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d result(s) have been emailed.', count($k))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred during operation.'));
        }
        $customerId = $this->getRequest()->getParam('customer_id');
        if ($customerId) {
            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
            return;
        }
        $this->_redirect('*/*/', array('webform_id' => $this->getRequest()->getParam('webform_id')));

    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                Mage::getModel('webforms/results')->setId($id)->delete();
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
        $customerId = $this->getRequest()->getParam('customer_id');
        if ($customerId) {
            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
            return;
        }
        $this->_redirect('*/*/', array('webform_id' => $this->getRequest()->getParam('webform_id')));

    }

    public function massStatusAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        $status = (int)$this->getRequest()->getParam('status');
        $form_id = $this->getRequest()->getParam('webform_id');
        $form = Mage::getModel('webforms/webforms')->load($form_id);
        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
                if ($status != $result->getApproved()) {
                    $result->setApproved(intval($status));
                    $result->save();
                    if (!$form->getId()) {
                        $form = $result->getWebform();
                    }

                    if ($form->getEmailResultApproval()) {
                        $result->sendApprovalEmail();
                    }
                    Mage::dispatchEvent('webforms_result_approve', array('result' => $result));
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d result(s) have been updated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred during operation.'));
        }
        $customerId = $this->getRequest()->getParam('customer_id');
        if ($customerId) {
            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId, 'tab' => 'webform_results'));
            return;
        }
        $this->_redirect('*/*/', array('webform_id' => $form_id));
    }

    public function popupAction()
    {
        $this->loadLayout();

        $result_id = $this->getRequest()->getParam('id');

        $popup = $this->getLayout()->getBlock('result_popup');
        $result = Mage::getModel('webforms/results')->load($result_id);
        $popup->setResult($result);

        $this->renderLayout();
    }

    public function ajaxFindCustomerAction()
    {
        $search = $this->getRequest()->getParam('search', '');
        $data = array();
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->setPageSize(5);
        $collection->addAttributeToFilter(
            array(
                array('attribute' => 'email', 'like' => '%' . $search . '%'),
                array('attribute' => 'name', 'like' => '%' . $search . '%'),
            )
        );

        foreach ($collection as $customer) {
            $data[] = array(
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'url' => $this->getUrl('adminhtml/customer/edit', array('id' => $customer->getId(), '_current' => false))
            );
        }
        $response = "<ul>";
        foreach ($data as $item) {
            $response .= sprintf(
                "<li data-id='%s' data-url='%s' data-email='%s' data-name='%s' >%s &lt;%s&gt;</li>",
                $item['id'], $item['url'], $item['email'], $item['name'], $item['name'], $item['email']
            );
        }
        $response .= "</ul>";
        $this->getResponse()->setBody($response);
    }

    public function setStatusAction(){
        $result_id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        $result = Mage::getModel('webforms/results')->load($result_id);
        $result->setApproved(intval($status));
        $result->save();
        if ($result->getWebform()->getEmailResultApproval()) {
            $result->sendApprovalEmail();
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');

        $response = array(
            'text' => $result->getStatusName(),
            'status' => $result->getApproved()
        );
        $jsonData = json_encode($response);
        $this->getResponse()->setBody($jsonData);
    }


    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('webform_id')) {
            return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_' . $this->getRequest()->getParam('webform_id'));
        }
        if (is_numeric($this->getRequest()->getParam('id'))) {
            $result = Mage::getModel('webforms/results')->load($this->getRequest()->getParam('id'));
            return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_' . $result->getWebformId());
        }
        if (is_array($this->getRequest()->getParam('id'))) {
            $isAllowed = true;
            foreach ($this->getRequest()->getParam('id') as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
                $isAllowed *= Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_' . $result->getWebformId());
            }
            return $isAllowed;
        }
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/forms');
    }
}