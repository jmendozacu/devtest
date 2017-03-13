<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Model_Observer {
  public function controller_action_predispatch_customer_account_login($observer) {
    $request = Mage::app()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    if($module == 'shoppinglist'){
      if(Mage::helper('customer')->isLoggedIn()) {
        return;
      }
      else {
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($request->getRequestUri());
        header("Status: 301");
        header('Location: '.Mage::helper('customer')->getLoginUrl());  // send to the login page
      }
    }
  }

  public function sendReminder($observer) {
    $_isSetDay = false;
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $today = date('l', $currentTimestamp);
    $setReminderDay = Mage::helper('shoppinglist')->getReminderDay();

    if (strtolower($today) == $setReminderDay) {
      $_isSetDay = true;
    }

    $reminders = Mage::getModel('shoppinglist/reminder')->getCollection()
    ->addFieldToFilter('reminder', 1);
    if (count($reminders)) {
      foreach ($reminders as $reminder) {
        if ($reminder->getInterval() == 'daily') {
          Mage::helper('shoppinglist')->sendEmailReminder($reminder->getCustomerId());
        }
        else {
          if ($_isSetDay) {
            if ($reminder->getInterval() == 'weekly') {
              Mage::helper('shoppinglist')->sendEmailReminder($reminder->getCustomerId());
            }				
            else if ($reminder->getInterval() == 'biweekly') {
                //check if today is 2 week from last send 
                $dateFrom = new Zend_Date($reminder->getLasttimeSend(), 'yyyy-MM-dd HH:m:s');						
                $dateTo = new Zend_Date(now(), 'yyyy-MM-dd HH:m:s');

                $dateTo->sub($dateFrom);
                $diff = round($dateTo->getTimestamp() / (60 * 60 * 24)) ;	
                if ($diff > 13 || $reminder->getLasttimeSend() == null) {
                  Mage::helper('shoppinglist')->sendEmailReminder($reminder->getCustomerId());
                }
            }
            else if ($reminder->getInterval() == 'monthly') {
                if ($diff > 28 || $reminder->getLasttimeSend() == null) {
                  Mage::helper('shoppinglist')->sendEmailReminder($reminder->getCustomerId());
                }
            }
          }
        }
      }
    }
  }
  public function addTabShoppinglistToCustomer($observer){

    $block = $observer->getEvent()->getBlock();    
    if($this->_helperShoppinglist()->isActive()){
      if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs)
      {
        if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type'))
        {
          $block->addTab('shopping_list', array(
          'label' => Mage::helper('shoppinglist')->__('Quick List'),
          'title' => Mage::helper('shoppinglist')->__('Quick List'),
          'url'   => $block->getUrl('shoppinglist/adminhtml_customer/index', array('_current' => true)),  
          'class' =>'ajax',     
          ));          
        }
      }
    }
  }
  protected function _helperShoppinglist(){
    return Mage::helper('shoppinglist');
  }
  protected function _getRequest()
  {
    return Mage::app()->getRequest();
  }
}