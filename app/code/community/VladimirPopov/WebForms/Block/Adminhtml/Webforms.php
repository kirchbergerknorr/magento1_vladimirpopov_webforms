<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Block_Adminhtml_Webforms extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_webforms';
        $this->_blockGroup = 'webforms';
        $this->_headerText = Mage::helper('webforms')->__('Manage Forms');
        $this->_addButtonLabel = Mage::helper('webforms')->__('Add New Form');

        $import_url = $this->getUrl('*/*/import');

        $import_form = '
		<form action="' . $import_url . '" style="display:none" method="post" enctype="multipart/form-data">
		    <input name="form_key" type="hidden" value="' . $this->getFormKey() . '" />
		    <input type="file" id="import_form" name="import_form" accept="application/json" onchange="this.up().submit()"/>
        </form>';

        $this->addButton('import', array(
            'before_html' => $import_form,
            'label' => $this->__('Import Form'),
            'class' => 'add',
            'onclick' => "$('import_form').click()"
        ));

        parent::__construct();
    }
}  
