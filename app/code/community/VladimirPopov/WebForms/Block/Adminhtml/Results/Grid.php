<?php

class VladimirPopov_WebForms_Block_Adminhtml_Results_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('webforms_results_grid_' . $this->getRequest()->getParam('webform_id'));
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/reply', array('id' => $row->getId(), 'webform_id' => $row->getWebformId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _filterCustomerCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        while (strstr($value, "  ")) {
            $value = str_replace("  ", " ", $value);
        }
        $customers_array = array();
        $name = explode(" ", $value);
        $firstname = $name[0];
        $lastname = $name[count($name) - 1];
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('firstname', $firstname);
        if (count($name) == 2)
            $customers->addAttributeToFilter('lastname', $lastname);
        foreach ($customers as $customer) {
            $customers_array[] = $customer->getId();
        }
        $collection->addFieldToFilter('customer_id', array('in' => $customers_array));
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addFilter('store_id', $value);
    }

    protected function _filterFieldCondition($collection, $column)
    {
        $field_id = str_replace('field_', '', $column->getIndex());
        $value = $column->getFilter()->getValue();
        if (!is_array($value)) $value = trim($value);
        if ($field_id && $value)
            $collection->addFieldFilter($field_id, $value);
    }

    protected function _prepareCollection()
    {
        $store = $this->getRequest()->getParam('store');
        $collection = Mage::getModel('webforms/results')->getCollection()
            ->setLoadValues(true)
            ->addFilter('webform_id', $this->getRequest()->getParam('webform_id'));
        if ($store)
            $collection->addFilter('store_id', $store);

        if ($this->_isExport) {
            $Ids = (array)Mage::app()->getRequest()->getParam('internal_id');
            if (count($Ids) == 1 && !empty($Ids[0])) $Ids = explode(',', $Ids[0]);
            if (count($Ids)) {
                $collection->addFieldToFilter('id', array('in' => $Ids));
            }
        }

        $this->setCollection($collection);

        Mage::dispatchEvent('webforms_block_adminhtml_results_grid_prepare_collection', array('grid' => $this));

        return parent::_prepareCollection();
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $approve_url = $this->getUrl('*/*/setStatus', array('id' => $row->getId(), 'status' => VladimirPopov_WebForms_Model_Results::STATUS_APPROVED));
        $reject_url = $this->getUrl('*/*/setStatus', array('id' => $row->getId(), 'status' => VladimirPopov_WebForms_Model_Results::STATUS_NOTAPPROVED));
        $complete_url = $this->getUrl('*/*/setStatus', array('id' => $row->getId(), 'status' => VladimirPopov_WebForms_Model_Results::STATUS_COMPLETED));

        $class = 'grid-status ';
        $progress = '<span class="grid-button-action request-progress" style="display: none;"><span>'.$this->__('Sending...').'</span></span>';
        $button_approve_style = "";
        $button_complete_style = "";
        $button_reject_style = "";


        if ($isExport) return $value;
        switch ($row->getApproved()) {
            case VladimirPopov_WebForms_Model_Results::STATUS_PENDING:
                $class .= 'pending';
                $button_complete_style = "display:none";
                break;
            case VladimirPopov_WebForms_Model_Results::STATUS_APPROVED:
                $button_approve_style = "display:none";
                $class .= 'approved';
                break;
            case VladimirPopov_WebForms_Model_Results::STATUS_COMPLETED:
                $button_approve_style = "display:none";
                $button_complete_style = "display:none";
                $button_reject_style = "display:none";
                $class .= 'completed';
                break;
            case VladimirPopov_WebForms_Model_Results::STATUS_NOTAPPROVED:
                $button_reject_style = "display:none";
                $button_complete_style = "display:none";
                $class .= 'notapproved';
                break;
        }
        $button_approve = '<a href="javascript:void(0)" style="'.$button_approve_style.'" onclick="setResultStatus(this, \'' . $approve_url . '\'); return false" class="grid-button-action approve"><span>'.$this->__('Approve').'</span></a>';
        $button_complete = '<a href="javascript:void(0)" style="'.$button_complete_style.'" onclick="setResultStatus(this, \'' . $complete_url . '\'); return false" class="grid-button-action complete"><span>'.$this->__('Complete').'</span></a>';
        $button_reject = '<a href="javascript:void(0)" style="'.$button_reject_style.'" onclick="if(confirm(\''.$this->__('Are you sure you want to disapprove the result?').'\'))setResultStatus(this, \'' . $reject_url . '\'); return false" class="grid-button-action reject"><span>'.$this->__('Reject').'</span></a>';

        $cell = '<div class="' . $class . '">' . $value . '</div>';
        $cell .= $button_reject;
        $cell .= $button_approve;
        $cell .= $button_complete;
        $cell .= $progress;
        return $cell;
    }


    protected function _prepareColumns()
    {
        $renderer = 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Id';
        if ($this->_isExport) {
            $renderer = false;
        }
        $this->addColumn('id', array(
            'header' => Mage::helper('webforms')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
            'renderer' => $renderer
        ));

        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->getCollection()
            ->addFilter('webform_id', $this->getRequest()->getParam('webform_id'));
        $fields->getSelect()->order('position asc');

        $maxlength = Mage::getStoreConfig('webforms/results/fieldname_display_limit');
        foreach ($fields as $field) {
            if ($field->getType() != 'html') {
                $field_name = $field->getName();
                if ($field->getResultLabel()) {
                    $field_name = $field->getResultLabel();
                }
                if (strlen($field_name) > $maxlength && $maxlength > 0) {
                    if (function_exists('mb_substr')) {
                        $field_name = mb_substr($field_name, 0, $maxlength) . '...';
                    } else {
                        $field_name = substr($field_name, 0, $maxlength) . '...';
                    }
                }
                $config = array(
                    'header' => $field_name,
                    'index' => 'field_' . $field->getId(),
                    'sortable' => false,
                    'filter_condition_callback' => array($this, '_filterFieldCondition'),
                    'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Value'
                );
                if ($this->_isExport) {
                    $config['renderer'] = false;
                } else {
                    if ($field->getType() == 'image') {
                        $config['filter'] = false;
                        $config['width'] = Mage::getStoreConfig('webforms/images/grid_thumbnail_width') . 'px';
                    }

                    if ($field->getType() == 'image' || $field->getType() == 'file') {
                        $config['renderer'] = 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Files';
                    }

                    if (strstr($field->getType(), 'select')) {
                        $config['type'] = 'options';
                        $config['options'] = $field->getSelectOptions();
                    }

                    if ($field->getType() == 'subscribe') {
                        $config['type'] = 'options';
                        $config['renderer'] = false;
                        $config['options'] = array(
                            0 => Mage::helper('adminhtml')->__('No'),
                            1 => Mage::helper('adminhtml')->__('Yes'),
                        );
                    }

                    if ($field->getType() == 'number' || $field->getType() == 'stars') {
                        $config['type'] = 'number';
                        $config['align'] = 'right';
                    }
                    if (strstr($field->getType(), 'date')) {
                        $config['type'] = 'date';
                    }
                    if ($field->getType() == 'datetime') {
                        $config['type'] = 'datetime';
                    }
                    if ($field->getType() == 'country') {
                        $config['type'] = 'country';
                        $config['renderer'] = false;
                    }
                    if ($field->getType() == 'textarea' || $field->getType() == 'wysiwyg') {
                        $config['width'] = '300px';
                    }
                }
                $config = new Varien_Object($config);
                Mage::dispatchEvent('webforms_block_adminhtml_results_grid_prepare_columns_config', array('field' => $field, 'config' => $config));

                $this->addColumn('field_' . $field->getId(), $config->getData());
            }
        }
        $config = array(
            'header' => Mage::helper('webforms')->__('Customer'),
            'align' => 'left',
            'index' => 'customer_id',
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Customer',
            'filter_condition_callback' => array($this, '_filterCustomerCondition'),
            'sortable' => false
        );
        if ($this->_isExport) {
            $config['export'] = true;
        }
        $this->addColumn('customer_id', $config);

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('webforms')->__('Store View'),
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter' => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }

        if (Mage::registry('webform_data')->getApprove()) {
            $this->addColumn('approved', array(
                'header' => Mage::helper('webforms')->__('Status'),
                'index' => 'approved',
                'type' => 'options',
                'options' => Mage::getModel('webforms/results')->getApprovalStatuses(),
                'frame_callback' => array($this, 'decorateStatus')
            ));
        }

        $this->addColumn('ip', array(
            'header' => Mage::helper('webforms')->__('IP'),
            'index' => 'ip',
            'sortable' => false,
            'filter' => false,
        ));


        $this->addColumn('created_time', array(
            'header' => Mage::helper('webforms')->__('Date Created'),
            'index' => 'created_time',
            'type' => 'datetime',
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('webforms')->__('Action'),
                'width' => '200',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Action',
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('webforms')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('webforms')->__('Excel XML'));

        Mage::dispatchEvent('webforms_block_adminhtml_results_grid_prepare_columns', array('grid' => $this));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('webforms')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete', array('webform_id' => $this->getRequest()->getParam('webform_id'))),
            'confirm' => Mage::helper('webforms')->__('Are you sure to delete selected results?'),
        ));

        $this->getMassactionBlock()->addItem('email', array(
            'label' => Mage::helper('webforms')->__('Send by e-mail'),
            'url' => $this->getUrl('*/*/massEmail', array('webform_id' => $this->getRequest()->getParam('webform_id'))),
            'confirm' => Mage::helper('webforms')->__('Send selected results to specified e-mail address?'),
            'additional' => array(
                'recipient' => array(
                    'name' => 'recipient_email',
                    'type' => 'text',
                    'class' => 'required-entry validate-email',
                    'label' => Mage::helper('webforms')->__('Recipient e-mail'),
                    'value' => $this->getRecipientEmail(),
                )
            )
        ));

        if (Mage::registry('webform_data')->getApprove()) {
            $statuses = Mage::getModel('webforms/results')->getApprovalStatuses();
            $this->getMassactionBlock()->addItem('status', array(
                'label' => Mage::helper('webforms')->__('Update status'),
                'url' => $this->getUrl('*/*/massStatus', array('webform_id' => $this->getRequest()->getParam('webform_id'))),
                'additional' => array(
                    'status' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('webforms')->__('Status'),
                        'values' => $statuses
                    )
                )
            ));
        }

        $this->getMassactionBlock()->addItem('reply', array(
            'label' => Mage::helper('webforms')->__('Reply'),
            'url' => $this->getUrl('*/*/reply', array('webform_id' => $this->getRequest()->getParam('webform_id'))),
            'confirm' => Mage::helper('webforms')->__('Reply to selected results?'),
        ));

        Mage::dispatchEvent('webforms_adminhtml_results_grid_prepare_massaction', array('grid' => $this));

        return $this;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        // add store switcher
        if (!Mage::app()->isSingleStoreMode() && $this->getRequest()->getParam('webform_id') && !$this->getRequest()->getParam('ajax')) {
            $store_switcher = $this->getLayout()->createBlock('adminhtml/store_switcher', 'store_switcher');
            $store_switcher->setUseConfirm(false);
            $html = $store_switcher->toHtml() . $html;
        }
        return $html;
    }

    protected function getRecipientEmail()
    {
        $webform = Mage::getModel('webforms/webforms')->setStoreId($this->getRequest()->getParam('store_id'))
            ->load($this->getRequest()->getParam('webform_id'));

        $email = $webform->getEmailSettings();

        return $email['email'];
    }
}

