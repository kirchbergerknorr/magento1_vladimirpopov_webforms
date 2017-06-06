<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Adminhtml_Webforms_QuickresponseController
	extends Mage_Adminhtml_Controller_Action
{
	public function indexAction(){
		$this->loadLayout();
		
		// add grid
		$this->getLayout()->getBlock('content')->append(
			$this->getLayout()->createBlock('webforms/adminhtml_quickresponse')
		);
		
		$this->renderLayout();
	}
	
	public function gridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('webforms/adminhtml_quickresponse_grid')->toHtml()
		);
	}	

	public function newAction()
	{
		$this->_forward('edit');
	}
	
	public function editAction()
	{
		$this->loadLayout();
		
		// add grid
		$this->getLayout()->getBlock('content')->append(
			$this->getLayout()->createBlock('webforms/adminhtml_quickresponse_edit')
		);
		
		$this->renderLayout();
		
	}
	
	public function saveAction()
	{
		if( $this->getRequest()->getPost()){
			try{
				$postData = $this->getRequest()->getPost('quickresponse');
				$id = $postData['quickresponse_id'];
				$saveandcontinue = $postData["saveandcontinue"];			
				
				$quickresponse = Mage::getModel('webforms/quickresponse')->setData($postData);
				
				if($id)$quickresponse->setId($id);
				
				$quickresponse->save();

				if( $id <= 0 )
					$quickresponse->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Quick response was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				
				if($saveandcontinue){
					$this->_redirect('*/webforms_quickresponse/edit',array('id' => $quickresponse->getId()));
				} else {
					$this->_redirect('*/webforms_quickresponse/index');
				}
				return;
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($postData);
				$this->_redirect('*/*/edit',array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			
		}
		$this->_redirect('*/webforms_quickresponse/index');
		
	}
	
	public function deleteAction()
	{
		if( $this->getRequest()->getParam('id') > 0){
			try{
				$quickresponse = Mage::getModel('webforms/quickresponse');
				$quickresponse->load($this->getRequest()->getParam('id'));
				$quickresponse->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Quick response was successfully deleted'));
				$this->_redirect('*/webforms_quickresponse/index');
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/webforms_quickresponse/index');
	}
	
	public function massDeleteAction()
	{
		$Ids = (array)$this->getRequest()->getParam('id');
		
		try {
			foreach($Ids as $id){
				$quickresponse = Mage::getModel('webforms/quickresponse')->load($id);
				$quickresponse->delete();
			}

			$this->_getSession()->addSuccess(
				$this->__('Total of %d record(s) have been deleted.', count($Ids))
			);
		}
		catch (Mage_Core_Model_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
		}

		$this->_redirect('*/webforms_quickresponse/index');
		
	}
	
	public function getAjaxMessageAction()
	{
		$id = $this->getRequest()->getPost('id');
		
		if($id){
			$quickresponse = Mage::getModel('webforms/quickresponse')->load($id);
			$this->getResponse()->setBody($quickresponse->getMessage());
		}
	}


	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/quickresponses');
	}
}