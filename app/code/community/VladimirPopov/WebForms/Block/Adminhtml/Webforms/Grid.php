<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Grid
	extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('webformsGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('webforms/webforms')->getCollection();

		// filter by role permissions
		$username = Mage::getSingleton('admin/session')->getUser()->getUsername();
		$role = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username',$username)->getFirstItem()->getRole();
		$rule_all = Mage::getModel('admin/rules')->getCollection()
			->addFilter('role_id',$role->getId())
			->addFilter('resource_id','all')
			->getFirstItem();
		if($rule_all->getPermission() == 'deny'){
			$collection->addRoleFilter($role->getId());
		}

		$this->setCollection($collection);

		Mage::dispatchEvent('webforms_adminhtml_webforms_grid_prepare_collection',array('grid'=>$this));
	
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id',array(
			'header' => Mage::helper('webforms')->__('ID'),
			'align'	=> 'right',
			'width'	=> '50px',
			'index'	=> 'id',
		));
		
		$this->addColumn('name',array(
			'header' => Mage::helper('webforms')->__('Name'),
			'align' => 'left',
			'index' => 'name',
		));
		
		$this->addColumn('fields',array(
			'header' => Mage::helper('webforms')->__('Fields'),
			'align' => 'right',
			'index' => 'fields',
			'type'	=> 'number',
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Fields',
			'sortable' => false,
			'filter' => false
		));
		
		$this->addColumn('results',array(
			'header' => Mage::helper('webforms')->__('Results'),
			'align' => 'right',
			'index' => 'results',
			'type'	=> 'number',
			'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Results',
			'sortable' => false,
			'filter' => false
		));
		
		$this->addColumn('is_active', array(
			'header'    => Mage::helper('webforms')->__('Status'),
			'index'     => 'is_active',
			'type'      => 'options',
			'options'   => Mage::getModel('webforms/webforms')->getAvailableStatuses(),
		));
		
		$this->addColumn('created_time', array(
			'header'    => Mage::helper('webforms')->__('Date Created'),
			'index'     => 'created_time',
			'type'      => 'datetime',
		));

		$this->addColumn('update_time', array(
			'header'    => Mage::helper('webforms')->__('Last Modified'),
			'index'     => 'update_time',
			'type'      => 'datetime',
		));
		
		$this->addColumn('last_result_time', array(
			'header'    => Mage::helper('webforms')->__('Last Result'),
			'index'     => 'last_result_time',
            'type'	=> 'number',
            'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Lastresult',
			'filter' => false,
			'sortable' => false,
		));
		
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('webforms')->__('Action'),
				'width'     => '100',
				'filter'    => false,
				'sortable'  => false,
				'renderer'	=> 'VladimirPopov_WebForms_Block_Adminhtml_Webforms_Renderer_Action',
				'is_system' => true,
		));

		Mage::dispatchEvent('webforms_adminhtml_webforms_grid_prepare_columns',array('grid'=>$this));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('id');

		
		$this->getMassactionBlock()->addItem('delete', array(
			 'label'=> Mage::helper('webforms')->__('Delete'),
			 'url'  => $this->getUrl('*/*/massDelete'),
			 'confirm' => Mage::helper('webforms')->__('Are you sure to delete selected elements?')
		));
		
		$statuses = Mage::getModel("webforms/webforms")->getAvailableStatuses();
		
		$this->getMassactionBlock()->addItem('status', array(
			 'label'=> Mage::helper('catalog')->__('Update Status'),
			 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
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
			 'url'  => $this->getUrl('*/*/massDuplicate'),
			 'confirm' => Mage::helper('webforms')->__('Are you sure to duplicate selected web-forms?')
		));
		
		Mage::dispatchEvent('webforms_adminhtml_webforms_grid_prepare_massaction',array('grid'=>$this));

		return $this;
	}
}
