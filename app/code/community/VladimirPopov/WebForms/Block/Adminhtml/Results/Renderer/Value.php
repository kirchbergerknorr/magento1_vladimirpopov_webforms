<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Value extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $field = Mage::getModel('webforms/fields')->load($field_id);
        $value = $row->getData($this->getColumn()->getIndex());
        $html = '';
        if ($field->getType() == 'stars') {
            $html = $this->getStarsBlock($row);
        }
        if ($field->getType() == 'textarea') {
            $html = $this->getTextareaBlock($row);
        }
        if ($field->getType() == 'wysiwyg') {
            $html = $this->getHtmlTextareaBlock($row);
        }
        if (strstr($field->getType(), 'date')) {
            $html = $field->formatDate($value);
        }
        if ($field->getType() == 'email') {
            if($value){
                $websiteId = false;
                try{$websiteId = Mage::app()->getStore($row->getStoreId())->getWebsite()->getId();}
                catch(Exception $e){}
                $customer = Mage::getModel('customer/customer')->setData('website_id',$websiteId)->loadByEmail($value);
                $html = htmlspecialchars($value);
                if($customer->getId()){
                    $html.= " [<a href='" . $this->getCustomerUrl($customer->getId()) . "' target='_blank'>" . $customer->getName() . "</a>]";
                }
            }
        }

        $html_object = new Varien_Object(array('html' => $html));

        Mage::dispatchEvent('webforms_block_adminhtml_results_renderer_value_render', array('field' => $field, 'html_object' => $html_object, 'value' => $value));

        if ($html_object->getHtml())
            return $html_object->getHtml();

        return nl2br(htmlspecialchars($value));
    }

    public function getTextareaBlock(Varien_Object $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $value = htmlspecialchars($row->getData($this->getColumn()->getIndex()));
        if (strlen($value) > 200 || substr_count($value, "\n") > 11) {
            $div_id = 'x_' . $field_id . '_' . $row->getId();
            $onclick = "Effect.toggle('$div_id', 'slide', { duration: 0.3 }); this.style.display='none';  return false;";
            $pos = strpos($value, "\n", 200);
            if ($pos > 300 || !$pos)
                $pos = strpos($value, " ", 200);
            if ($pos > 300)
                $pos = 200;
            if (!$pos) $pos = 200;
            $html = '<div>' . nl2br(substr($value, 0, $pos)) . '</div>';
            $html .= '<div id="' . $div_id . '" style="display:none">' . nl2br(substr($value, $pos, strlen($value))) . '<br></div>';
            $html .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . $this->__('Expand') . ']</a>';
            return $html;
        }
        return nl2br($value);
    }

    public function getHtmlTextareaBlock(Varien_Object $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $value = $row->getData($this->getColumn()->getIndex());
        if (strlen(strip_tags($value)) > 200 || substr_count($value, "\n") > 11) {
            $div_id = 'x_' . $field_id . '_' . $row->getId();
            $preview_div_id = 'preview_x_' . $field_id . '_' . $row->getId();
            $onclick = "$('{$preview_div_id}').hide(); Effect.toggle('$div_id', 'slide', { duration: 0.3 }); this.style.display='none';  return false;";
            $html = '<div style="min-width:400px" id="' . $preview_div_id . '">' . Mage::helper('webforms')->htmlCut($value, 200) . '</div>';
            $html .= '<div id="' . $div_id . '" style="display:none;min-width:400px">' . $value . '</div>';
            $html .= '<a onclick="' . $onclick . '" style="text-decoration:none;float:right">[' . $this->__('Expand') . ']</a>';
            return $html;
        }
        return $value;
    }

    public function getStarsBlock(Varien_Object $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());
        $field = Mage::getModel('webforms/fields')->load($field_id);
        $value = (int)$row->getData($this->getColumn()->getIndex());
        $blockwidth = ($field->getStarsCount() * 16) . 'px';
        $width = round(100 * $value / $field->getStarsCount()) . '%';
        $html = "<div class='stars' style='width:$blockwidth'><ul class='stars-bar'><li class='stars-value' style='width:$width'></li></ul></div>";
        return $html;
    }

    public function getCustomerUrl($customerId)
    {

        return $this->getUrl('adminhtml/customer/edit', array('id' => $customerId, '_current' => false));
    }

}
