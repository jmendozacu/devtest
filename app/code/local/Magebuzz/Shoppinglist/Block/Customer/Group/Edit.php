<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer_Group_Edit extends Mage_Core_Block_Template
{
  public function _prepareLayout()
  {
    parent::_prepareLayout();
    $headBlock = $this->getLayout()->getBlock('head');
    if ($headBlock) {
      $headBlock->setTitle($this->__('My Quick List'));
    }
  }
  public function getTitle() {
    $id = $this->getRequest()->getParam('id');
    if($id){
      $title = Mage::helper('shoppinglist')->__('Edit group "%s"', Mage::getModel('shoppinglist/group')->load($id)->getListName());
    }
    else{
      $title = Mage::helper('shoppinglist')->__('Add new group');
    }
    return $title;
  }
  public function getBackUrl()
  {
    return $this->getUrl('shoppinglist');
  }
}