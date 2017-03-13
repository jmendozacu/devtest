<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_ItemController extends Mage_Core_Controller_Front_Action
{
  public function indexAction() {	
    $this->loadLayout();  
    $this->renderLayout();
  }

  public function addAction() {
    Mage::getSingleton('customer/session')->authenticate($this);
    $this->loadLayout();  
    $this->getLayout()->getBlock('head')->setTitle(Mage::helper('shoppinglist')->__('Add Item to My Quick List'));
    $this->renderLayout();
  }

  public function loginAction() {
    $this->loadLayout();     
    $this->renderLayout();
  }

  public function newAction() {
    $post = $this->getRequest()->getPost();
    $now = Mage::getModel('core/date')->gmtTimestamp(now());
    $groupId = '';		
    if($post){			
      try{
        $productId = $post['product_id'];
        $qty = (int)$post['qty'];

        if($post['create_group']){
          $groupId = Mage::helper('shoppinglist/group')->saveNewGroup($post['customer_id'], $post['group-name'], 1, $now);
        }
        else{
          $groupId = $post['select-group'];
        }
        Mage::helper('shoppinglist')->assignProductToList($groupId, $productId, $qty, $now);        
        $product=Mage::getModel("catalog/product");         
        $prod=$product->load($productId);
        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($prod);  
        $col = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
        
        $total_qty=0;
        foreach($col as $sprod){
            $sprod=$product->load($sprod->getId());
            $qty = intval(Mage::getModel('cataloginventory/stock_item')->loadByProduct($sprod)->getQty());
            $total_qty+=$qty;
        }       
        
        if(isset($post['sold_out']) && $post['sold_out'] == "1"){
          Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__("This product is 'Out of Stock' at this time. Availability of the product should be checked before adding the product to cart.!"));
        }else{
          Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('The product was added to your My Quick list!'));
        }        
        $this->_redirect('*/');	
      }catch (Exception $e) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
        $this->_redirect('*/');
        return;
      }
    }	
  }
}