<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer extends Mage_Core_Block_Template
{
  public function _prepareLayout()
  {
    return parent::_prepareLayout();
  }
  public function getProduct() {
    $params = $this->getRequest()->getParams();
    $product = Mage::getModel('catalog/product')->load($params['id']);

    return $product;
  }	
  public function getFormAddUrl() {
    return Mage::getUrl('shoppinglist/index/add', array(
    'id'        => $this->getProduct()->getId()
    ));
  }
}