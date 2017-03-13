<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {      
    if($row->getItemId()==""){
      return "";
    }
    else{
      $productId = $row->getProductId(); 
      $product = Mage::getModel('catalog/product')->load($productId);      
      $row->setProduct($product);             
      return Mage::helper('core')->currency($row->getPrice());          
    }
  }
}