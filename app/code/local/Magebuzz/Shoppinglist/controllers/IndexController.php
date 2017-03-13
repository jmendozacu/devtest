<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_IndexController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {
    if(Mage::helper('customer')->isLoggedIn()) {
      $this->loadLayout(); 
      $this->_initLayoutMessages('customer/session');	
      $this->renderLayout();
    } else 
      $this->_redirectUrl(Mage::getBaseUrl().'customer/account');
  }

  public function loginAction(){
    $params = $this->getRequest()->getParams();
    Mage::app()->getResponse()->setRedirect($params['path']);
    Mage::getSingleton('customer/session')->authenticate($this);
  }

  protected function _getProductRequest() {
    $requestInfo = $this->getRequest()->getParams();
    if ($requestInfo instanceof Varien_Object) {
      $request = $requestInfo;
    }
    elseif (is_numeric($requestInfo)) {
      $request = new Varien_Object();
      $request->setQty($requestInfo);
    }
    else {
      $request = new Varien_Object($requestInfo);
    }
    if (!$request->hasQty()) {
      $request->setQty(1);
    }		
    return $request;
  }

  /**
  ** Load form for adding product from detail page
  **/
  public function detailformAction() {
    $this->loadLayout();
    $this->renderLayout();
  }

  public function additemAction() {
    $post = $this->getRequest()->getParams();
    $now = Mage::getModel('core/date')->gmtTimestamp(now());
    $groupId = '';		
    if ($post) {
      try {
        $productId = $post['product_id'];
        $qty = (int)$post['qty'];														

        if($post['create_group']) {
          $groupId = Mage::helper('shoppinglist/group')->saveNewGroup($post['customer_id'], $post['group-name'], 1, $now);
        }
        else {
          $groupId = $post['select-group'];
        }

        if ($post['product_type_id']=='grouped') {
          $subProducts = $post['super_group'];
          foreach ($subProducts as $pId => $qty) {
            if ($qty>0) {
              $product = Mage::getModel('catalog/product')
              ->setStoreId(Mage::app()->getStore()->getId())
              ->load($pId);
              $request = $this->_getProductRequest();

              $customOptions = $product->getTypeInstance()->prepareForCart($request, $product);
              Mage::helper('shoppinglist')->assignProductToList($groupId, $pId, $qty, $customOptions);
            }
          }
        }	
        else {
          $product = Mage::getModel('catalog/product')
          ->setStoreId(Mage::app()->getStore()->getId())
          ->load($productId);
          $request = $this->_getProductRequest(); 
          $customOptions = $product->getTypeInstance()->prepareForCart($request, $product);
          Mage::helper('shoppinglist')->assignProductToList($groupId, $productId, $qty, $customOptions);								
        }
        if(isset($post['sold_out']) && $post['sold_out'] == "1"){
          Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__("This product is 'Out of Stock' at this time. Availability of the product should be checked before adding the product to cart.!"));
        }else{
          Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('The product was added to your My Quick list!'));
        }
      } catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to submit your request. Please, try again later'));
        return;
      }
    }		
    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');	
    $this->renderLayout();
  }

  public function addAction() {
    $this->loadLayout();  
    $this->renderLayout();
  }	

  public function addGroupAction() {
    $this->loadLayout();  
    $this->renderLayout();
  }
  public function sendRemindersAction() {
    if (Mage::helper('shoppinglist')->isActive()) {
      $groups = Mage::getModel('shoppinglist/group')->getCollection();
      foreach($groups as $_group) {
        $dateFrom = new Zend_Date($_group->getUpdatedAt(), 'yyyy-MM-dd HH:m:s');
        $dateTo = new Zend_Date(now(), 'yyyy-MM-dd HH:m:s');

        $dateTo->sub($dateFrom);
        $diff = round($dateTo->getTimestamp() / (60 * 60 * 24)) ;

        /* Get time setting */
        $sendEmailAfter = $_group->getSendReminderAfter();
        $items = Mage::getModel('shoppinglist/items')->getItemsByGroup($_group->getListId());

        if(count($items) > 0){ // The system will be send an email notification to the group has an item
          if($diff > $sendEmailAfter) { // Check
            try{
              /* Send email */
              Mage::helper('shoppinglist')->sendEmailReminder($_group->getListId(), $_group->getListName(), $_group->getCustomerId(), $items);
              /* After send email success, update time */
              $_group->setUpdatedAt(now());
              $_group->save();

            } catch(Exception $e){
              echo $e->getMessage();
              return;
            }	
          }	
        }
      }
    }
  }

  public function savelaterAction() {
    
    $quote = Mage::getSingleton('checkout/session')->getQuote();
    $_customer = Mage::getSingleton('customer/session')->getCustomer();
    //$prefixName = Mage::helper('shoppinglist')->saveLaterPrefixName();
    //$prefixName = "New List (Re-name)";
    $prefixName = $_POST['group-name'];    
    $now = Mage::getModel('core/date')->gmtTimestamp(now());	
     // $groupName = $prefixName.date('m-d-y-h-i-s', $now);
    $groupName = $prefixName;
    $cartItems = $quote->getAllVisibleItems();
    if (count($cartItems) > 0) {
      $groupId = Mage::helper('shoppinglist/group')->saveNewGroup($_customer->getId(), $groupName, 1);
      try{	
        Mage::helper('shoppinglist')->saveCartForLater($groupId, $cartItems);
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('All items successfully saved to your My Quick list'));
        $this->_redirect('*/');
      }catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to save cart to your My Quick list!'));
        $this->_redirect('*/');
        return;
      }
    }
  }


  public function saveConfigAction() {
    if ($post = $this->getRequest()->getPost()) {
      $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
      if (isset($post['receive_email_config']) && $post['receive_email_config']) {
        // set to 1
        $reminder = Mage::getModel('shoppinglist/reminder')
        ->setReminder(1)
        ->setInterval($post['reminder_option'])
        ->setCustomerId($customerId);				
      }
      else {
        $reminder = Mage::getModel('shoppinglist/reminder')
        ->setReminder(0)
        ->setInterval(null)
        ->setCustomerId($customerId);
      }
      if ($id = $reminder->loadByCustomerId()) {
        $reminder->setId($id);
      }
      try {
        $reminder->save();

        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Your reminder config was successfully saved.'));
        $this->_redirect('*/');
        return;
      }
      catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to set your reminder Config. Please try again.'));
        $this->_redirect('*/');
        return;
      }
    }
    $this->_redirect('*/');
    return;

  }
  public function loginpostAction() {
    $session = $this->_getCustomerSession();
    $login['username'] = $this->getRequest()->getParam('username');     
    $login['password'] = $this->getRequest()->getParam('password');     
    try {
      $session->login($login['username'], $login['password']);
      if ($session->getCustomer()->getIsJustConfirmed()) {
        echo $this->_welcomeCustomer($session->getCustomer(), true);
      }
    } catch (Mage_Core_Exception $e) {
      switch ($e->getCode()) {
        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
          $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
          echo $message = Mage::helper('customer')->__('Account Not Confirmed', $value);
          break;
        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
          echo $message = $this->__('Invalid Email Address or Password');
          break;
        default:
          echo $message = $e->getMessage();
      }
      $session->setUsername($login['username']);
    } 
    if ($session->getCustomer()->getId()) {
      echo 'loginsuccess';
    }
  }
  private function _getCustomerSession() {
    return Mage::getSingleton('customer/session');
  }
  public function testAction() {
    //check if today is the configured day
    //get a list of customers who received reminder emails
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
                Mage::log('biweekly email', null, 'shoppinglist.log');
                //check if today is 2 week from last send 
                $dateFrom = new Zend_Date($reminder->getLasttimeSend(), 'yyyy-MM-dd HH:m:s');						
                $dateTo = new Zend_Date(now(), 'yyyy-MM-dd HH:m:s');

                $dateTo->sub($dateFrom);
                $diff = round($dateTo->getTimestamp() / (60 * 60 * 24)) ;	
                Mage::log($diff . ' days', null, 'shoppinglist.log');
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
    die('test OK');
  }
}
