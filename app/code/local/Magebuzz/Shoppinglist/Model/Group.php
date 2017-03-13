<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Model_Group extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('shoppinglist/group');
  }

  public function getGroupByCustomer($customerId) {
    $collection = $this->getCollection();
    $collection->addFieldToFilter('customer_id', $customerId);
    return $collection;
  }

  public function getGroupLastUpdatedTime($customerId) {
    $groups = $this->getGroupByCustomer($customerId);
    $groups->setOrder('updated_at', 'DESC');
    $groups->getFirstItem();
    $groups->setPageSize(1);
    return $groups;
  }

  public function getItems() {

    $collection = Mage::getModel('shoppinglist/items')->getCollection()
    ->addFieldToFilter('list_id', $this->getId())
    ->setOrder('item_id')
    ->load();
    $products = $this->_getProductsArray($collection); 
    $items = array();
    foreach ($collection as $item) {
      if (isset($products[$item->getProductId()])){
        $item->setProduct($products[$item->getProductId()]);
        $items[] = $item;
      }
    }
    return $items; 	
  }

  protected function _getProductsArray($items) {
    $productIds = array();
    foreach ($items as $item) {
      $productIds[] = $item->getProductId();
    }
    $productIds = array_unique($productIds);

    $collection = Mage::getModel('catalog/product')->getResourceCollection()
    ->addIdFilter($productIds)
    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
    $collection->load();
    $products = array(); 
    foreach ($collection as $prod) {
      $products[$prod->getId()] = $prod; 
    }
    return $products;
  }
}