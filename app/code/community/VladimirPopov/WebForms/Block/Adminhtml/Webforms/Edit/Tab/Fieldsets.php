<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Fieldsets
	extends Mage_Adminhtml_Block_Widget_Grid
{
	
	protected function _prepareLayout(){
		
		parent::_prepareLayout();
	}
	
	/**
	 * Set grid params
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('form_fieldsets_grid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('asc');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
	}
	
	public function getGridUrl(){
		return $this->getUrl('*/webforms_fieldsets/grid',array('id'=> $this->getRequest()->getParam('id'),'store'=>$this->getRequest()->getParam('store')));
	}

	public function getRowUrl($row){
		return $this->getUrl('*/webforms_fieldsets/edit', array('id' => $row->getId(), 'webform_id' => $this->getRequest()->getParam('id'),'store'=>$this->getRequest()->getParam('store')));
	}
	
	protected function _prepareCollection(){
		$store = $this->getRequest()->getParam('store');
		$collection = Mage::getModel('webforms/fieldsets')->setStoreId($store)->getCollection()->addFilter('webform_id', $this->getRequest()->getParam('id'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	 * Add columns to grid
	 *
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header'    => Mage::helper('webforms')->__('ID'),
			'width'     => 60,
			'index'     => 'id'
		));

		$this->addColumn('name', array(
			'header'    => Mage::helper('webforms')->__('Name'),
			'index'     => 'name'
		));

		$this->addColumn('is_active', array(
			'header'    => Mage::helper('webforms')->__('Status'),
			'index'     => 'is_active',
			'type'      => 'options',
			'options'   => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
		));
        $config =array(
            'header'            => Mage::helper('webforms')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 60,
            'prefix'            => 'fieldsets',
        );
        if(!$this->getRequest()->getParam('store')){
            $config['renderer'] ='VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Renderer_Position';
            $config['editable'] = true;
        }
		$this->addColumn('position', $config);

		Mage::dispatchEvent('webforms_adminhtml_webforms_tab_fieldsets_prepare_columns',array('grid'=>$this));
				
		return parent::_prepareColumns();
	}
	
	protected function _prepareMassaction()
	{
		if((float)substr(Mage::getVersion(),0,3)<=1.3 && Mage::helper('webforms')->getMageEdition() != 'EE') return $this;
		
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('id');
		
		$this->getMassactionBlock()->addItem('delete', array(
			 'label'=> Mage::helper('webforms')->__('Delete'),
			 'url'  => $this->getUrl('*/webforms_fieldsets/massDelete', array('webform_id' => $this->getParam('id'))),
			 'confirm' => Mage::helper('webforms')->__('Are you sure to delete selected elements?')
		));
		
		$statuses = Mage::getModel("webforms/webforms")->getAvailableStatuses();
		
		$this->getMassactionBlock()->addItem('status', array(
			 'label'=> Mage::helper('catalog')->__('Change status'),
			 'url'  => $this->getUrl('*/webforms_fieldsets/massStatus', array('webform_id' => $this->getParam('id'),'store'=>$this->getRequest()->getParam('store'))),
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
				
		$this->getMassactionBlock()->addItem('duplicate', array(
			 'label'=> Mage::helper('webforms')->__('Duplicate'),
			 'url'  => $this->getUrl('*/webforms_fieldsets/massDuplicate',array('webform_id' => $this->getParam('id'))),
			 'confirm' => Mage::helper('webforms')->__('Are you sure to duplicate selected fieldsets?')
		));
		
		Mage::dispatchEvent('webforms_adminhtml_webforms_grid_prepare_massaction',array('grid'=>$this));

		return $this;
	}
}  
