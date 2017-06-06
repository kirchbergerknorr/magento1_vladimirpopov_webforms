<?php
class VladimirPopov_WebForms_Block_Adminhtml_Quickresponse_Grid
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
	
	protected function _prepareCollection(){
		$collection = Mage::getModel('webforms/quickresponse')->getCollection();
		$collection->getSelect()->order('created_time desc');
		$this->setCollection($collection);
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}

	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	protected function _prepareColumns(){
		
		$this->addColumn('id',array(
			'header' => Mage::helper('webforms')->__('ID'),
			'align'	=> 'right',
			'width'	=> '50px',
			'index'	=> 'id'
		));
		
		$this->addColumn('title',array(
			'header' => Mage::helper('core')->__('Title'),
			'index'	=> 'title'
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
		
		return parent::_prepareColumns();
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
		
		return $this;
	}
}
