<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer_View extends Mage_Core_Block_Template {
  protected $_reminder = null;
  public function _prepareLayout() {
    parent::_prepareLayout();
    $headBlock = $this->getLayout()->getBlock('head');
    if ($headBlock) {
      $headBlock->setTitle($this->__('My Quick List'));
    }
  }

  public function getConfigSaveUrl() {
    return $this->getUrl('*/*/saveConfig');
  }

  public function getReminderOptions() {
    return Mage::helper('shoppinglist')->getReminderOptions();
  }

  public function isConfigSelected() {
    $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
    $reminder = Mage::getModel('shoppinglist/reminder')
    ->setCustomerId($customerId);
    if ($id = $reminder->loadByCustomerId()) {
      $reminder->load($id);
      return (bool)$reminder->getReminder();
    }
    return false;
  }

  protected function _getReminder() {
    if ($this->_reminder == null) {			
      $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
      $reminder = Mage::getModel('shoppinglist/reminder')
      ->setCustomerId($customerId);
      if ($id = $reminder->loadByCustomerId()) {
        $this->_reminder = $reminder->load($id);
      }
    }
    return $this->_reminder;
  }

  public function getSelectedOption() {
    $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
    $reminder = Mage::getModel('shoppinglist/reminder')
    ->setCustomerId($customerId);
    if ($id = $reminder->loadByCustomerId()) {
      $reminder->load($id);
      return $reminder->getInterval();
    }
    return '';
  }
}