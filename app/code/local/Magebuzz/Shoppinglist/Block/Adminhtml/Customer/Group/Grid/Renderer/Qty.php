<?php
/*
* @copyright   Copyright ( c ) 2013 www.magebuzz.com
*/

class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {      
    if($row->getItemId()==""){
      return "";
    }
    else{
       $qty = $row->getQty();        
      return $qty*1;
    }
  }
}