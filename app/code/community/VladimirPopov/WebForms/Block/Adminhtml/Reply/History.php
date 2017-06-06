<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply_History
	extends Mage_Adminhtml_Block_Template
{

	protected $_result;

	protected $_messages;

	public function getResult(){
		return $this->_result;
	}

	protected function _construct()
	{
		parent::_construct();

		$id = $this->getRequest()->getParam('id');
		if(is_array($id) && count($id) == 1 && !empty($id[0])) $id = $id[0];

		$this->_result = Mage::getModel('webforms/results');

		if(is_numeric($id)){
			$this->_result->load($id);
			$this->setTemplate('webforms/reply/history.phtml');
		}
	}

	public function getMessages()
	{
		if(!$this->_messages){
			if($this->_result->getId()){
				$collection = Mage::getModel('webforms/message')->getCollection()
					->addFilter('result_id',$this->_result->getId());
				$collection->getSelect()->order('created_time desc');
				$this->_messages = $collection;
			} else {
				$this->_messages = false;
			}
		}
		return $this->_messages;
	}

}
