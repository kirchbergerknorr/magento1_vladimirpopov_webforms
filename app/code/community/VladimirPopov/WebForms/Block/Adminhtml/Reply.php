<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
    {
        $this->_controller = 'adminhtml_reply';
        $this->_blockGroup = 'webforms';

        $this->_headerText = $this->__('Selected Result(s)');
        parent::__construct();
        $this->_removeButton('delete');

        $this->_updateButton('save', 'label', $this->__('Save Reply'));

        $Ids = $this->getRequest()->getParam('id');

        if (!is_array($Ids)) {
            $Ids = array($Ids);
        }

        if (count($Ids) == 1) {
			$this->_addButton('edit', array
            (
                'label' => Mage::helper('webforms')->__('Edit Result'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/edit', array('id' => $Ids[0])) . '\')',
            ));

            $this->_addButton('print', array
            (
                'label' => Mage::helper('webforms')->__('Print'),
                'class' => 'save',
                'onclick' => 'setLocation(\'' . $this->getUrl('*/webforms_print_result/print',array('_current'=>true,'result_id' => $Ids[0])) . '\')',
            ));
         }

		$this->addButton('reply',array(
			'label' => $this->__('Save Reply And E-mail'),
			'class' => 'save',
			'onclick' => 'saveAndEmail()'
		),-100);
		
		$this->_formScripts[] = "
			function saveAndEmail(){
				$('email').value = true;
				editForm.submit();
			}
		";
	}
	
	public function getBackUrl()
	{
		if($this->getRequest()->getParam('customer_id'))
			return $this->getUrl('adminhtml/customer/edit',array('id'=>$this->getRequest()->getParam('customer_id'),'tab'=>'webform_results'));
		return $this->getUrl('*/*/',array('webform_id'=>$this->getRequest()->getParam('webform_id')));
	}
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		
		// add scripts
		$js = $this->getLayout()->createBlock('core/template','reply_js',array(
			'template' => 'webforms/reply/js.phtml',
		));
		
		$this->getLayout()->getBlock('content')->append(
			$js
		);

		if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE')
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
			{
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
				$this->_formScripts[] = "
					function toggleEditor() {
						if (tinyMCE.getInstanceById('page_content') == null) {
							tinyMCE.execCommand('mceAddControl', false, 'content');
						} else {
							tinyMCE.execCommand('mceRemoveControl', false, 'content');
						}
					}";
			}

	}
}
