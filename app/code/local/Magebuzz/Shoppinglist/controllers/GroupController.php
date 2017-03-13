<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_GroupController extends Mage_Core_Controller_Front_Action {	
  public function viewAction() {
    if(Mage::helper('customer')->isLoggedIn()) {			
      $group_id = $this->getRequest()->getParam('id');			
      if ($group_id) {
        $group = Mage::getModel('shoppinglist/group')->load($group_id);
        if ($group->getCustomerId() != Mage::helper('customer')->getCustomer()->getId()) {
          $this->_redirect('*/index/index');
          return;
        }
        Mage::register('current_group', $group);
        $this->loadLayout();   
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle('My Quick List | Group - '.$group->getListName());
        $this->renderLayout();
        return;			
      }
    } else $this->_redirectUrl(Mage::getBaseUrl().'shoppinglist');	
  }

  public function saveAction() {
    $post = $this->getRequest()->getParams();
    $model = Mage::getModel('shoppinglist/group');
    $customer = Mage::getSingleton('customer/session')->getCustomer();
    $now = Mage::getModel('core/date')->gmtTimestamp(now());
    try {
      if($post['group_id'] != ''){
        /* Update existed group */
        $group = $model->load($post['group_id']);
        $group->setListName($post['group-name']);
        $model->setSendReminderAfter($post['email-reminder']);
        $group->setUpdatedAt($now);
        $group->save();
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('The group has been updated.'));	
      }
      else{
        /* Creat new group */
        $model->setCustomerId($customer->getId());			
        $model->setListName($post['group-name']);			
        $model->setSendReminderAfter($post['email-reminder']);
        $model->setStatus(1);
        $model->setCreatedAt($now);
        $model->setUpdatedAt($now);
        $model->save();
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('The group has been created.'));
      }
      $this->_redirect('*/');			
    } catch (Exception $e) {
      Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to submit your request. Please, try again later'));
      $this->_redirect('*/');
      return;
    }
  }
  public function editAction() {
    $this->loadLayout();  
    $this->renderLayout();
  }
  public function newAction() {
    $this->_forward('edit');
  }
  public function deleteAction() {
    if( $this->getRequest()->getParam('id') > 0 ) {
      try {
        $model = Mage::getModel('shoppinglist/group');

        $model->setId($this->getRequest()->getParam('id'))
        ->delete();
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('The group was successfully deleted'));
        $this->_redirect('*/');
      } catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to delete your group. Please, try again later'));
        $this->_redirect('*/');
        return;
      }
    }
    $this->_redirect('*/');
  }

  public function updateAction() {
    $post = $this->getRequest()->getParams();		
    if($post){
      try{
        $groupId = $post['group_id'];
        $group = Mage::getModel('shoppinglist/group')->load($groupId);
        $group->setListName($post['list_name'])
        ->save();
      }catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to submit your request. Please try again later.'));
        $this->_redirect('*/*/view/', array('id'=>$groupId));
        return;
      }      
      try{      
        foreach($post['item'] as $itemId) {
          $itemProduct = Mage::getModel('shoppinglist/items')->load($itemId['itemId']);
          if($itemId['qty'] <=0){
            $itemProduct->delete();
          }else{
            $itemProduct->setQty($itemId['qty']);
            if($itemId['select-group'] > 0){        
              $itemProduct->setListId($itemId['select-group']);             
              $id = Mage::getResourceModel('shoppinglist/items')->isExisted($itemProduct);
              $itemNewGroup = Mage::getSingleton('shoppinglist/items')->load($id['item_id']);              
              $itemProduct->setQty($id['qty']+$itemProduct->getQty()); 
              $itemNewGroup->setData($itemProduct->getData()); 
              $itemNewGroup->setItemId($id['item_id']) ;
              $itemProduct->delete();
              $itemNewGroup->save();   
            }
            $itemProduct->save(); 
          }

        }
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Your Quick list was successfully updated'));
        $this->_redirect('*/*/view/', array('id'=>$groupId));
      }catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to submit your request. Please try again later.'));
        $this->_redirect('*/*/view/', array('id'=>$groupId));
        return;
      }	
    }else{
      $this->_redirect('*/index/index');
    }
  }
  public function removeItemAction() {
    $item_ids = split(',',$this->getRequest()->getParam('id'));
    $post = $this->getRequest()->getPost();  
    $groupId = $this->getRequest()->getParam('group_id');
    if($item_ids){
      try {
        foreach($item_ids as $item_id) {
          if($item_id !='' || $item_id != null){
            $model = Mage::getModel('shoppinglist/items');        
            $model->setId($item_id)
            ->delete();
          }
        } 
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Item was successfully deleted'));
        $this->_redirect('*/*/view/', array('id'=>$groupId));
      } catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to delete this item. Please, try again later'));
        $this->_redirect('*/*/view/', array('id'=>$groupId));
        return;
      }
    }		
  }
  public function settingEmailAction() {
    $post = $this->getRequest()->getPost();
    if($post){
      try{
        $model = Mage::getModel('shoppinglist/group')->load($post['group_id']);
        $model->setSendReminderAfter($post['email-reminder']);
        $model->setUpdatedAt(now());
        $model->save();
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Your setting was successfully updated'));
        $this->_redirect('*/');
      }catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('Unable to submit your request. Please, try again later'));
        $this->_redirect('*/');
        return;
      }	
    }
  }

  public function cartAction() {
    $quote = Mage::getSingleton('checkout/cart');    
    $item_ids = split(',',$this->getRequest()->getParam('items'));  
    $groupId = $this->getRequest()->getParam('groupId');
    try {
      if ($item_ids[0] == 'all') {     
        $group = Mage::getModel('shoppinglist/group')->load($groupId);
        $items = $group->getItems();
        if (count($items)) {
          $x = 0;
          foreach ($items as $item) {					
            $qty = $item->getQty();
            $product = Mage::getModel('catalog/product')
            ->load($item->getProductId())
            ->setQty($qty);
            $req = unserialize($item->getBuyRequest());
            $req['qty'] = $product->getQty();            
            
            $productid = $item->getProductId();
            if(isset($req['super_attribute'])){
              $key = key($req['super_attribute']);
              $superattrval = $req['super_attribute'][$key];            
              $product_conf =  Mage::getModel('catalog/product')->load($item->getProductId());            
              $superattributes = array($key => $superattrval);             
              $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product_conf);
              $subProduct = $conf->getProductByAttributes($superattributes, $product_conf);
              $productid = $subProduct->getId();
            }
            $_productn = Mage::getModel('catalog/product')->load($productid);            
            $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_productn)->getQty();            
            if($stocklevel == 0){
              $x++;              
            }else{              
              $quote->addProduct($product, $req);
            }            
          }
          
          $quote->save();
          if($x != 0){
            Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('shoppinglist')->__('Products other than out of stock was successfully added to shopping cart'));
          }else{
            Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('shoppinglist')->__('Product was successfully added to shopping cart'));
          }
          $this->_redirect('checkout/cart');
          return;
        }
      }
      else{
        if($item_ids){
          $x = 0;
          foreach($item_ids as $item_id){
            if($item_id !='' || $item_id != null){
              $item = Mage::getModel('shoppinglist/items')->load($item_id);
              $qty = $item->getQty();
              $product = Mage::getModel('catalog/product')
              ->load($item->getProductId())
              ->setQty($qty);//  max(0.01) ;
              $req = unserialize($item->getBuyRequest());
              $req['qty'] = $product->getQty();
              
              $productid = $item->getProductId();
              if(isset($req['super_attribute'])){
                $key = key($req['super_attribute']);
                $superattrval = $req['super_attribute'][$key];            
                $product_conf =  Mage::getModel('catalog/product')->load($item->getProductId());            
                $superattributes = array($key => $superattrval);             
                $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product_conf);
                $subProduct = $conf->getProductByAttributes($superattributes, $product_conf);
                $productid = $subProduct->getId();
              }
              $_productn = Mage::getModel('catalog/product')->load($productid);            
              $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_productn)->getQty();            
              if($stocklevel == 0){
                $x++;              
              }else{              
                $quote->addProduct($product, $req);
              }
              
              //$quote->addProduct($product, $req);
              
            }                                    
          }          
          $quote->save();  
          
          if($x != 0){
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Products other than out of stock was successfully added to shopping cart'));
          }else{
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Product was successfully added to shopping cart'));
          }
          
          //Mage::getSingleton('customer/session')->addSuccess(Mage::helper('shoppinglist')->__('Product was successfully added to shopping cart'));
          //$this->_redirect('checkout/cart');
          $this->_redirect('*/*/view/', array('id'=>$groupId));
          return;
        }
      }
    }
    catch (Exception $e) {      
      Mage::getSingleton('customer/session')->addError(Mage::helper('shoppinglist')->__('There was problem when adding product to cart. Please check that all the products are in stock.'));
      $this->_redirect('*/*/view/', array('id'=>$groupId));
      return;	
    }
  }
}