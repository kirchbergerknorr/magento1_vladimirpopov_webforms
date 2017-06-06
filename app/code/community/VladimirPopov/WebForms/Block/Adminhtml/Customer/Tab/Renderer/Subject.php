<?php
class VladimirPopov_WebForms_Block_Adminhtml_Customer_Tab_Renderer_Subject
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        $subject = $row->getEmailSubject();
        $title = str_replace("'","\'",$subject);
        return <<<HTML
        <a href="javascript:Admin_JsWebFormsResultModal('{$title}','{$this->getPopupUrl($row)}')">{$subject}</a>
HTML;
    }

    public function getPopupUrl(Varien_Object $row){
        return $this->getUrl('adminhtml/webforms_results/popup',array('id'=>$row->getId(),'customer_id'=>Mage::registry('current_customer')->getId()));
    }
}