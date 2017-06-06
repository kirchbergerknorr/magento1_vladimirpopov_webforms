<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
require_once(Mage::getBaseDir('lib').'/Webforms/mpdf.php');

class VladimirPopov_WebForms_Adminhtml_Webforms_Print_ResultController
    extends Mage_Adminhtml_Controller_Action
{
    public function printAction()
    {
        $result_id = $this->getRequest()->getParam('result_id');
        $result = Mage::getModel('webforms/results')->load($result_id);

        if(@class_exists('mPDF')) {
            $mpdf = @new mPDF('utf-8', 'A4');
            @$mpdf->WriteHTML($result->toPrintableHtml());

            $this->_prepareDownloadResponse($result->getPdfFilename(), @$mpdf->Output('', 'S'), 'application/pdf');
        } else {
            $this->_getSession()->addError($this->__('Printing is disabled. Please install mPDF library. <a href=\'http://mageme.com/downloads/mpdf.zip\'>Click here to download</a>'));
            $this->_redirect('*/webforms_webforms');
        }

    }

    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('result_id')){
			$result = Mage::getModel('webforms/results')->load($this->getRequest()->getParam('result_id'));
			return Mage::getSingleton('admin/session')->isAllowed('admin/webforms/webform_'.$result->getWebformId());
		}
        return Mage::getSingleton('admin/session')->isAllowed('admin/webforms');
    }
}