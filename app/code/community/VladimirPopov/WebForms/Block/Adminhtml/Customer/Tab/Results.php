<?php

class VladimirPopov_WebForms_Block_Adminhtml_Customer_Tab_Results
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_tab_results');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setAfter('tags');
        $this->setEmptyText(Mage::helper('webforms')->__('No Results Found'));

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('webforms/results')->getCollection()
            ->addFilter('customer_id', $this->getRequest()->getParam('id'))
            ->setLoadValues(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/webforms_customer/results', array('id' => $this->getRequest()->getParam('id')));
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';

        switch ($row->getApproved()) {
            case VladimirPopov_WebForms_Model_Results::STATUS_PENDING:
                $class = 'grid-severity-minor';
                break;
            case VladimirPopov_WebForms_Model_Results::STATUS_APPROVED:
                $class = 'grid-severity-notice';
                break;
            case VladimirPopov_WebForms_Model_Results::STATUS_NOTAPPROVED:
                $class = 'grid-severity-critical';
                break;
        }

        $cell = '<span class="' . $class . '"><span>' . $value . '</span></span>';
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

        $this->addcolumn('form', array(
            'header' => Mage::helper('webforms')->__('Web-form'),
            'index' => 'webform_id',
            'type' => 'options',
            'options' => Mage::getModel('webforms/webforms')->getGridOptions(),
        ));

        $this->addColumn('subject', array(
            'header' => Mage::helper('webforms')->__('Subject'),
            'filter' => false,
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Customer_Tab_Renderer_Subject'
        ));

        $this->addColumn('approved', array(
            'header' => Mage::helper('webforms')->__('Approved'),
            'index' => 'approved',
            'type' => 'options',
            'width' => '140',
            'options' => Mage::getModel('webforms/results')->getApprovalStatuses(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('webforms')->__('Date Created'),
            'index' => 'created_time',
            'type' => 'datetime',
            'width' => '200'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('webforms')->__('Delete'),
            'url' => $this->getUrl('*/webforms_results/massDelete', array('customer_id' => $this->getRequest()->getParam('id'))),
            'confirm' => Mage::helper('webforms')->__('Are you sure to delete selected results?'),
        ));

        $this->getMassactionBlock()->addItem('email', array(
            'label' => Mage::helper('webforms')->__('Send by e-mail'),
            'url' => $this->getUrl('*/webforms_results/massEmail', array('customer_id' => $this->getRequest()->getParam('id'))),
            'confirm' => Mage::helper('webforms')->__('Send selected results to specified e-mail address?'),
            'additional' => array(
                'recipient' => array(
                    'name' => 'recipient_email',
                    'type' => 'text',
                    'label' => Mage::helper('webforms')->__('Recipient e-mail'),
                    'value' => $this->getRecipientEmail(),
                )
            )
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('webforms')->__('Update status'),
            'url' => $this->getUrl('*/webforms_results/massStatus', array('customer_id' => $this->getRequest()->getParam('id'))),
            'additional' => array(
                'status' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('webforms')->__('Status'),
                    'values' => Mage::getModel('webforms/results')->getApprovalStatuses()
                )
            )
        ));

        return $this;
    }

    public function getRecipientEmail()
    {
        return Mage::app()->getWebsite(Mage::registry('current_customer')->getWebsiteId())->getConfig('webforms/email/email');
    }
}