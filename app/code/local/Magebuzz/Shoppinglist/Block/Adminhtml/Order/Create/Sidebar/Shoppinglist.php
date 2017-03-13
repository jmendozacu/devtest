<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Adminhtml_Order_Create_Sidebar_Shoppinglist extends Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Abstract {
  protected function _construct() {
    parent::_construct();
    $this->setId('sales_order_create_sidebar_shoppinglist');
    $this->setDataId('shoppinglist');
  }

  public function getHeaderText() {
    return Mage::helper('shoppinglist')->__('Quick List Items');
  }

  /**
  * Retrieve item collection
  * @return mixed
  */
  public function getItemCollection() {
    $productCollection = $this->getData('item_collection');
    if (is_null($productCollection)) {
      $stores = array();
      $website = Mage::app()->getStore($this->getStoreId())->getWebsite();
      foreach ($website->getStores() as $store) {
        $stores[] = $store->getId();
      }
      $collection = Mage::getModel('shoppinglist/items')
      ->getCollection()
      ->addFieldToFilter('main_table.store_id', array('in' => $stores));
      $collection->getSelect()
      ->join(array('sgroup'=>Mage::getSingleton('core/resource')->getTableName('shoppinglist/group')), 'main_table.list_id=sgroup.list_id')
      ->where('sgroup.customer_id', $this->getCustomerId());
      $productIds = array();
      foreach ($collection as $event) {
        $productIds[] = $event->getProductId();
      }
      $productCollection = null;
      if ($productIds) {
        $productCollection = Mage::getModel('catalog/product')
        ->getCollection()
        ->setStoreId($this->getQuote()->getStoreId())
        ->addStoreFilter($this->getQuote()->getStoreId())
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('price')
        ->addAttributeToSelect('small_image')
        ->addIdFilter($productIds)
        ->load();
      }
      $this->setData('item_collection', $productCollection);
    }
    return $productCollection;
  }

  public function canRemoveItems() {
    return false;
  }

  public function getIdentifierId($item) {
    return $item->getId();
  }
}