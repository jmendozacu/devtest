<?php

class Vs_Stockupdate_Model_Stockupdate extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("stockupdate/stockupdate", "id");
	   

    }

    public function updateOutofStockTable($productId,$roleStoreIds) {   	 
      	

        foreach ($roleStoreIds as $roleStoreId) {
          $collectionOthersLooking = Mage::getModel('stockupdate/stockupdate')->load();
            $collectionOthersLooking->setProduct_id($productId)
            ->setStore_id($roleStoreId)
            //->setCreated_at()
            ->save();
          }

	  }

    
       public function updateInStockTable($productId,$roleStoreIds) {     

         foreach ($roleStoreIds as $roleStoreId) {

          $collectionOthersLooking = Mage::getModel('stockupdate/stockupdate')->load();

            $collection = Mage::getModel('stockupdate/stockupdate')->getCollection()
            ->addFieldToFilter('product_id',array('eq'=> $productId))
            ->addFieldToFilter('store_id',array('eq' => $roleStoreId))
        ;

        foreach($collection as $coll)
        { 
          if($coll->getId())
            Mage::getModel('stockupdate/stockupdate')->load($coll->getId())->delete();
        }
          }

    }



    public function gettingProductIds($storeId) {


        	$collectionStockUpdate = Mage::getModel('stockupdate/stockupdate')->getCollection();
         
          $collectionStockUpdate->addFieldToSelect('product_id')
              ->addFieldToFilter('store_id ', array('eq' =>  $storeId) )
              ->load();
             
      
          $idAll      = $collectionStockUpdate->getData();           
          $productIds = array();
          foreach ($idAll as $productId) {
              $productIds[] = $productId['product_id'];
          }
          
      return $productIds;

    }
}
	 