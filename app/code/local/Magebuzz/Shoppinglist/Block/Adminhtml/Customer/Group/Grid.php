<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();   
    $this->setTemplate('shoppinglist/customer/edit/tab/group/grid.phtml');
    $this->setId('shoppinglistGrid');
    $this->setUseAjax(true);
    $this->setDefaultSort('list_id');
    $this->setFilterVisibility(true);     
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(true);    
  }

  protected function _prepareCollection()
  {
    $customerId = $this->getRequest()->getParam('id');
    if(!$customerId){
      $customerId  = $this->getCustomerId();
    }
    $collection = Mage::getModel('shoppinglist/group')->getCollection()->addFieldToFilter('customer_id',$customerId);
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn('list_id', array(
    'header'    => Mage::helper('shoppinglist')->__('ID'),
    'align'     =>'right',
    'width'     => '50px',
    'index'     => 'list_id',
    ));

    $this->addColumn('list_name', array(
    'header'    => Mage::helper('shoppinglist')->__('Group Name'),
    'align'     =>'left',
    'index'     => 'list_name',
    ));

    /*
    $this->addColumn('content', array(
    'header'    => Mage::helper('shoppinglist')->__('Item Content'),
    'width'     => '150px',
    'index'     => 'content',
    ));
    */

    $this->addColumn('status', array(
    'header'    => Mage::helper('shoppinglist')->__('Status'),
    'align'     => 'left',
    'width'     => '80px',
    'index'     => 'status',
    'type'      => 'options',
    'options'   => array(
    1 => 'Enabled',
    2 => 'Disabled',
    ),
    ));

    $this->addColumn('created_at', array(
    'header'    => Mage::helper('shoppinglist')->__('Date Created'),
    'index'     => 'created_at',
    'type'      => 'datetime',
    ));    

    $this->addColumn('action',
    array(
    'header'    =>  Mage::helper('shoppinglist')->__('Action'),
    'width'     => '100',
    'renderer' => 'Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Action',
    'filter'    => false,
    'sortable'  => false,
    'index'     => 'stores',
    'is_system' => true,
    ));      

    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
    return '';$this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  public function getGridUrl()
  {
    return $this->getUrl('*/*/index', array('_current'=> true));
  }  

}