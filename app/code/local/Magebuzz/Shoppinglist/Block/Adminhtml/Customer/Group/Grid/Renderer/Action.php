<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Adminhtml_Customer_Group_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {  
    $html = '';  
    if($row->getListId()==""){
      return $html;
    }
    else{
      $html .="<dl>";
      $html .="<dd><a title='View Product' onclick='getListItem(".$row->getListId().")' style='cursor: pointer;'><span>".$this->__('View Product')."</span></a></dd>";
      $html .="<dd><a title='View Product' onclick='return shoppinglistControl.removeItem(".$row->getListId().")' style='cursor: pointer;'><span>".$this->__('Delete')."</span></a></dd>";
      $html .="</dl>";
      return $html;             
    }
  }
}