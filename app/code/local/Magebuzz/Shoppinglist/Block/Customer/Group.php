<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer_Group extends Mage_Core_Block_Template {
  public function _prepareLayout() {
    parent::_prepareLayout();
    $headBlock = $this->getLayout()->getBlock('head');
    if ($headBlock) {
      $headBlock->setTitle($this->__('My Quick List'));
    }
  }
  public function getItems($groupId) {
    $items = Mage::getModel('shoppinglist/items')->getItemsByGroup($groupId);
    return $items;
  }

  public function getGroup() {
    return Mage::registry('current_group');
  }

  public function getListItemsUrl($listId) {
    return Mage::getUrl('shoppinglist/group/view', array('id'=> $listId));
  }

  public function getUpdateUrl($listId) {
    return Mage::getUrl('shoppinglist/group/edit', array('id'=> $listId));
  }

  public function getCreateGroupUrl() {
    return Mage::getUrl('shoppinglist/group/new');
  }

  public function getDeleteUrl($listId) {
    return Mage::getUrl('shoppinglist/group/delete', array('id'=> $listId));
  }

  public function getRemoveItemUrl($groupId, $itemId) {
    return Mage::getUrl('shoppinglist/group/removeItem', array('group_id' => $groupId, 'id' => $itemId));
  }

  public function getBackUrl()
  {
    return $this->getUrl('shoppinglist');
  }
}