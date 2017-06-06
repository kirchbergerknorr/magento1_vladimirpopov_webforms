<?php
class VladimirPopov_WebForms_Block_Adminhtml_Fields_Edit_Tab_Logic
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('field_logic_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/webforms_logic/grid', array('id' => $this->getRequest()->getParam('id'), 'store' => $this->getRequest()->getParam('store')));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/webforms_logic/edit', array('id' => $row->getId(), 'store' => $this->getRequest()->getParam('store')));
    }

    protected function _prepareCollection()
    {
        $store = $this->getRequest()->getParam('store');
        $collection = Mage::getModel('webforms/logic')->setStoreId($store)->getCollection()->addFilter('field_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('webforms')->__('ID'),
            'width' => 60,
            'index' => 'id'
        ));

        $this->addColumn('logic_condition', array(
            'header' => Mage::helper('webforms')->__('Condition'),
            'index' => 'logic_condition',
            'type' => 'options',
            'options' => Mage::getModel('webforms/logic_condition')->getOptions()
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('webforms')->__('Trigger value(s)'),
            'index' => 'value',
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Logic_Renderer_Value'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('webforms')->__('Action'),
            'index' => 'action',
            'type' => 'options',
            'options' => Mage::getModel('webforms/logic_action')->getOptions()
        ));

        $this->addColumn('target', array(
            'header' => Mage::helper('webforms')->__('Target element(s)'),
            'filter' => false,
            'index' => 'target',
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Logic_Renderer_Target'
        ));

        if (Mage::registry('field')->getType() == 'select/checkbox')
            $this->addColumn('aggregation', array(
                'header' => Mage::helper('webforms')->__('Logic aggregation'),
                'index' => 'aggregation',
                'type' => 'options',
                'options' => Mage::getModel('webforms/logic_aggregation')->getOptions()
            ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('webforms')->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) <= 1.3 && Mage::helper('webforms')->getMageEdition() != 'EE') return $this;

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('webforms')->__('Delete'),
            'url' => $this->getUrl('*/webforms_logic/massDelete', array(
                'field_id' => $this->getParam('id'),
                'store' => $this->getRequest()->getParam('store'))),
            'confirm' => Mage::helper('webforms')->__('Are you sure to delete selected elements?')
        ));

        $statuses = Mage::getModel("webforms/webforms")->getAvailableStatuses();

        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('catalog')->__('Change status'),
            'url' => $this->getUrl('*/webforms_logic/massStatus', array(
                'field_id' => $this->getParam('id'),
                'store' => $this->getRequest()->getParam('store'))),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('webforms')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        Mage::dispatchEvent('webforms_adminhtml_fields_logic_grid_prepare_massaction', array('grid' => $this));

        return $this;
    }
}