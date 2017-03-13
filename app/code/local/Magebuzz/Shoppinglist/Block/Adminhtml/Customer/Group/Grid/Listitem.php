<?php
class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Listitem extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct()
  {
    parent::__construct();
    $this->setTemplate('shoppinglist/customer/edit/tab/group/grid.phtml');
    $this->setId('customer_shoppinglist_item');
    $this->setDefaultSort('item_id');
    $this->setDefaultSort('DESC');
    $this->setUseAjax(true);
    $this->setFilterVisibility(false);
    $this->setSaveParametersInSession(true);
    $this->setEmptyText(Mage::helper('catalog')->__('No records found.'));
  }

  protected function _prepareCollection()
  {
    $param = $this->getRequest()->getParams();    
    $itemsCollection = Mage::getModel('shoppinglist/items')->getItemsByGroup($param['groupid']);                                                             
    $this->setCollection($itemsCollection);        
    return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn('item_id',
    array(
    'header'=> Mage::helper('shoppinglist')->__('ID'),
    'width' => '50px',
    'type'  => 'number',
    'index' => 'item_id',
    'align'     =>'left',
    ));
    $this->addColumn('name',
    array(
    'header'=> Mage::helper('shoppinglist')->__('Name and Description'),
    'renderer'  => new Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Productname,
    'filter' => false,
    ));

    $this->addColumn('qty',
    array(
    'header'=> Mage::helper('shoppinglist')->__('Qty'),
    'renderer'  => new Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Qty,
    'filter' => false,
    ));

    $this->addColumn('price',
    array(
    'header'=> Mage::helper('shoppinglist')->__('Price'),
    'renderer'  => new  Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Price,
    'filter' => false,
    ));

    return parent::_prepareColumns();
  }

  public function getGridUrl()
  {
    return $this->getUrl('shoppinglist/adminhtml_customer/getlistitem', array('_current'=>true));
  }
  public function getRowUrl($row)
  {
    return ;
  }
}
