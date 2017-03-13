<?php

class Vs_Foodcoupon_Model_Foodcoupon extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("foodcoupon/foodcoupon", "id");
	   

    }

    public function updateFoodcouponTable($order_id,$store_id,$customer_id,$amount) {  
      	    $collectionfood = Mage::getModel('foodcoupon/foodcoupon');
            $collectionfood->setOrder_id($order_id)
            ->setStore_id($store_id)
            ->setCustomer_id($customer_id)
            ->setAmount($amount)
            ->save();
        

	  }

    
      
    public function getFoodcouponAmount($order_id) {

        	$collectionfood = Mage::getModel('foodcoupon/foodcoupon')->getCollection();         
          $collectionfood->addFieldToSelect('*')
          ->addFieldToFilter('order_id ', array('eq' =>  $order_id) )
          ->load();
             
          $all      = $collectionfood->getData();    
          foreach ($all as $item) {
              $amount = $item['amount'];
          }
          
      return $amount;

    }
        
}
	 