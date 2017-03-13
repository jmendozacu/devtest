<?php

class Magebuzz_Shoppinglist_Adminhtml_CustomerController
extends Mage_Adminhtml_Controller_Action
{
  public function indexAction()
  {
    $this->loadLayout();        
    $this->renderLayout();
  }
  public function getlistitemAction(){  
    $param = $this->getRequest()->getParam('groupid');
    $block = $this->getLayout()->createBlock('shoppinglist/adminhtml_customer_group_grid_listitem')->toHtml();    
    $this->getResponse()->setBody($block);    
  }

  public function deleteAction(){
    $groupId = $this->getRequest()->getParam('list_id');
    $customerId = $this->getRequest()->getParam('customer_id'); 
    if($groupId > 0){
      try {
        $model = Mage::getModel('shoppinglist/group');
        $model->setId($groupId)
        ->delete();              
        $block = $this->getLayout()->createBlock('shoppinglist/adminhtml_customer_group_grid')->setCustomerId($customerId)->toHtml();
        $this->getResponse()->setBody($block);    
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        die('false');
      }
    }
  }
}
