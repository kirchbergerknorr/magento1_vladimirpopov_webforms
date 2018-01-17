<?php

class VladimirPopov_WebForms_Block_Adminhtml_Element_File extends Varien_Data_Form_Element_Abstract
{

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('file');
        $this->setExtType('file');
    }

    public function _getName()
    {
        return "file_{$this->getData('field_id')}";
    }

    public function removeClass($class)
    {
        $classes = array_unique(explode(' ', $this->getClass()));
        if (false !== ($key = array_search($class, $classes))) {
            unset($classes[$key]);
        }
        $this->setClass(implode(' ', $classes));
        return $this;
    }

    public function getElementHtml()
    {
        $this->addClass('input-file');
        if ($this->getRequired()) {
            $this->removeClass('required-entry');
        }

        $element = sprintf('<input id="%s" name="%s" %s />%s',
            $this->getHtmlId(),
            $this->_getName(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getAfterElementHtml()
        );

        return $this->_getPreviewHtml() . $element . $this->_getDropzoneHtml();
    }

    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getData('result_id')) {
            $result = Mage::getModel('webforms/results')->load($this->getData('result_id'));
            $field_id = $this->getData('field_id');
            $files = Mage::getModel('webforms/files')->getCollection()
                ->addFilter('result_id', $result->getId())
                ->addFilter('field_id', $field_id);
            if (count($files)) {
                $html .= '<div class="webforms-file-pool">';
                if(count($files) > 1)
                    $html .= $this->_getSelectAllHtml();
                /** @var VladimirPopov_WebForms_Model_Files $file */
                foreach ($files as $file) {
                    $nameStart = '<div class="webforms-file-link-name">' . substr($file->getName(), 0, strlen($file->getName()) - 7) . '</div>';
                    $nameEnd = '<div class="webforms-file-link-name-end">' . substr($file->getName(), -7) . '</div>';

                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                    }

                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }
        }

        return $html;
    }

    protected function _getSelectAllHtml()
    {
        $id = $this->getHtmlId() . 'selectall';
        $html = '';
        $html .= '<script>function checkAll(elem){elem.up().up().select("input[type=checkbox]").invoke("writeAttribute","checked",elem.checked);}</script>';
        $html .= '<div class="webforms-file-pool-selectall"><input id="' . $id . '" type="checkbox" class="webforms-file-delete-checkbox" onchange="checkAll(this)"/> <label for="' . $id . '">' . Mage::helper('webforms')->__('Select All') . '</label></div>';
        return $html;
    }

    public function getDropzoneName()
    {
        $name = $this->getData('dropzone_name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    protected function _getDropzoneHtml()
    {
        $config = array();

        $config['url'] = Mage::getUrl('webforms/files/dropzone');
        $config['fieldId'] = $this->getHtmlId();
        $config['fieldName'] = $this->getDropzoneName();
        $config['dropZone'] = $this->getData('dropzone') ? 1 : 0;
        $config['dropZoneText'] = $this->getData('dropzone_text') ? $this->getData('dropzone_text') : Mage::helper('webforms')->__('Add files or drop here');
        $config['maxFiles'] = $this->getData('dropzone_maxfiles') ? $this->getData('dropzone_maxfiles') : 5;
        $config['allowedSize'] = $this->getData('allowed_size');
        $config['allowedExtensions'] = $this->getData('allowed_extensions');
        $config['restrictedExtensions'] = $this->getData('restricted_extensions');
        $config['validationCssClass'] = '';
        $config['errorMsgAllowedExtensions'] = Mage::helper('webforms')->__('Selected file has none of allowed extensions: %s');
        $config['errorMsgRestrictedExtensions'] = Mage::helper('webforms')->__('Uploading of potentially dangerous files is not allowed.');
        $config['errorMsgAllowedSize'] = Mage::helper('webforms')->__('Selected file exceeds allowed size: %s kB');
        $config['errorMsgUploading'] = Mage::helper('webforms')->__('Error uploading file');
        $config['errorMsgNotReady'] = Mage::helper('webforms')->__('Please wait... the upload is in progress.');

        return '<script>new JsWebFormsDropzone(' . json_encode($config) . ')</script>';

    }

    protected function _getDeleteCheckboxHtml($file)
    {
        $html = '';
        if ($file) {
            $checkboxId = 'delete_file_' . $file->getId();
            $checkboxName = str_replace('file_', 'delete_file_', $this->getName()) . '[]';

            $checkbox = array(
                'type' => 'checkbox',
                'name' => $checkboxName,
                'value' => $file->getLinkHash(),
                'class' => 'webforms-file-delete-checkbox',
                'id' => $checkboxId
            );

            $label = array(
                'for' => $checkboxId
            );

            $html .= '<p>';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</p>';
        }
        return $html;
    }

    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-file';
    }

    protected function _getDeleteCheckboxLabel()
    {
        return Mage::helper('adminhtml')->__('Delete');
    }

    protected function _drawElementHtml($element, array $attributes, $closed = true)
    {
        $parts = array();
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

}
