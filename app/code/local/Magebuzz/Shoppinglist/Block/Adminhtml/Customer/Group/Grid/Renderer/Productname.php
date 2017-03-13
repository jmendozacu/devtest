<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Productname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
      $itemModel = Mage::getModel('shoppinglist/items')->load($row->getItemId())->setProduct($productModel);    
      $name = "<a class='product-name'>".$this->htmlEscape($product->getName())."</a>";
      $name .= $row->getOptionsHtml();

      return $name;
    }
  }
}