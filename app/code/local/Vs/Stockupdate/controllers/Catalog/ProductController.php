<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
include_once "Mage/Adminhtml/controllers/Catalog/ProductController.php";
class Vs_Stockupdate_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    protected $massactionEventDispatchEnabled = true;
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Vs_Stockupdate');

    }
    
    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('purchasedata/stockupdatebackend');

        $this->_addContent(
            $this->getLayout()->createBlock('stockupdate/catalog_product')
        );

        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('stockupdate/catalog_product_grid')->toHtml()
        );
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('purchasedata/stockupdatebackend');
    }
    
     ///////////////////////////////////////////////////////////////////////////////////////////////
    // Mass Functions BEGIN -->               /////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    
    

     public function massInstockAction(){

        $roleStoreId[0] = Mage::getSingleton('adminhtml/session')->getRoleStoreId();
         $storeIds = $this->getRequest()->getParam('checkstock');
         $productIds = $this->getRequest()->getParam('product');
         $count = $this->getRequest()->getParam('countwebsite');
        // print_r($productIds);echo count($productIds);exit;
          $modelStockupdate = Mage::getModel('stockupdate/stockupdate')->load();
         

         if (!is_array($productIds)|| !array_filter($productIds)) { 
            $this->_getSession()->addError($this->__('Please select product(s)'));
        }
        else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $currentIds =  $product->getWebsiteIds();
                  
                   if($count>1) {//multistore
                         if($roleStoreId[0]==0) { //super admin

                            $webIds  = array_merge($currentIds,$storeIds );
                             if (!is_array($storeIds)) {
                                $this->_getSession()->addError($this->__('Please select Website(s)'));
                            }
                            else {
                                $product->setWebsiteIds($webIds); //assigning website IDs
                                $modelStockupdate->updateInStockTable($productId,$storeIds);  

                            }
                        }
                        else { 
                            $webIds  = array_merge($currentIds,$roleStoreId );
                            $product->setWebsiteIds($webIds); //assigning website IDs
                            $modelStockupdate->updateInStockTable($productId,$roleStoreId);

                        }
                        
                    }
                    else {
                        $stockItem = $product->getStockItem();
                        $stockItem->setData('manage_stock', 1);
                        $stockItem->setData('is_in_stock', 1);
                        $stockItem->setData('qty', 100);

                        $stockItem->save();
                    }
                   $product->getResource()->save($product);
                }
                    $this->_getSession()->addSuccess('Total of '. count($productIds).' record(s) were successfully updated.');

                    
                } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }

     }
      $this->_redirect('*/*/index'); 
 }
public function massOutofstockAction(){
        
         $roleStoreId[0] = Mage::getSingleton('adminhtml/session')->getRoleStoreId();
         $storeIds = $this->getRequest()->getParam('checkstock');
         $productIds = $this->getRequest()->getParam('product');
         $count = $this->getRequest()->getParam('countwebsite');

        $modelStockupdate = Mage::getModel('stockupdate/stockupdate')->load();
           
          

         if (!is_array($productIds)|| !array_filter($productIds)){
            $this->_getSession()->addError($this->__('Please select product(s)'));
        }
        else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $currentIds =  $product->getWebsiteIds();
                    
                    if($count>1) {//multistore
                        if($roleStoreId[0]==0) { //super admin
                            $webIds  = array_diff($currentIds,$storeIds );
                             if (!is_array($storeIds)) {
                                $this->_getSession()->addError($this->__('Please select Website(s)'));
                            }
                            else {
                                $product->setWebsiteIds($webIds); //assigning website IDs
                                $modelStockupdate->updateOutofStockTable($productId,$storeIds);  

                            }
                        }
                        else { 
                            $webIds  = array_diff($currentIds,$roleStoreId );
                            $product->setWebsiteIds($webIds); //assigning website IDs
                            $modelStockupdate->updateOutofStockTable($productId,$roleStoreId);

                        }
                    }
                    else {
                        $stockItem = $product->getStockItem();
                        $stockItem->setData('manage_stock', 1);
                        $stockItem->setData('is_in_stock', 0);
                        $stockItem->setData('qty', 0);
                        $stockItem->save();
                    }

                    $product->getResource()->save($product);
                    }
                    $this->_getSession()->addSuccess('Total of '. count($productIds).' record(s) were successfully updated.');
                    
                } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }

     }
      $this->_redirect('*/*/index'); 
 }

public function updatePriceAction()
{
     $roleStoreId = Mage::getSingleton('adminhtml/session')->getRoleStoreId();
     $fieldId = (int) $this->getRequest()->getParam('id');
     $price = $this->getRequest()->getParam('price');
    if ($fieldId) {
        $product = Mage::getModel('catalog/product')->load($fieldId);
        $websiteIds = $product->getWebsiteIds();
        if(!Mage::app()->isSingleStoreMode()) {
          if(in_array($roleStoreId, $websiteIds)) {
              $product->setStoreId($roleStoreId)->setPrice($price);
              $product->getResource()->save($product);
              $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
              $process->reindexAll();
              echo 1;
           }
            else {
              echo 0;           
            }
        }
        else {
              if ($product->getStockItem()->getIsInStock()) { 
                $product->setPrice($price);
                $product->setStoreId($websiteIds[0])->setPrice($price);
                $product->getResource()->save($product);
                $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
                $process->reindexAll();
                  echo "1";
                }else{
                    echo 0;
                }

        }
    }

}


public function updateSpecialPriceAction()
{
     $roleStoreId = Mage::getSingleton('adminhtml/session')->getRoleStoreId();
     $fieldId = (int) $this->getRequest()->getParam('id');
     $price = $this->getRequest()->getParam('price');
     if ($fieldId) {
        $product = Mage::getModel('catalog/product')->load($fieldId);
        $websiteIds = $product->getWebsiteIds();
         if(!Mage::app()->isSingleStoreMode()) {
          if(in_array($roleStoreId, $websiteIds)) {
              $product->setStoreId($roleStoreId)->setSpecialPrice($price);
              $product->getResource()->save($product);
              $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
              $process->reindexAll();
              echo 1;
           }
            else {
              echo 0;           
            }
        }
        else {
              if ($product->getStockItem()->getIsInStock()) { 
                $product->setSpecialPrice($price);
                $product->setStoreId($websiteIds[0])->setSpecialPrice($price);
                $product->getResource()->save($product);
                $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
                $process->reindexAll();
                echo "1";
                }else{
                    echo 0;
                }

        }
    }
  }
}