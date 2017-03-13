<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Item_Add extends Mage_Catalog_Block_Product_List {
  public function _prepareLayout() {	
    return parent::_prepareLayout();
  }

  public function getProduct() {
    $param = $this->getRequest()->getParams();
    if ($param) {
      if (isset($param['id'])) {
        $productId = $param['id'];
      }
      elseif (isset($param['product'])) {
        $productId = $param['product'];
      }
      $product = Mage::getModel('catalog/product')->load($productId);
      Mage::register('product', $product);
      return $product;
    }
    return false;
  }

  protected function _getOptionSelect(Mage_Catalog_Model_Product $_product) {
    $blockOptionsHtml = null;
    $blockOption = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Options");
    $blockOption->addOptionRenderer("default","catalog/product_view_options_type_default","catalog/product/view/options/type/default.phtml");
    $blockOption->addOptionRenderer("text","catalog/product_view_options_type_text","catalog/product/view/options/type/text.phtml");
    $blockOption->addOptionRenderer("file","catalog/product_view_options_type_file","catalog/product/view/options/type/file.phtml");
    $blockOption->addOptionRenderer("select","catalog/product_view_options_type_select","catalog/product/view/options/type/select.phtml");
    $blockOption->addOptionRenderer("date","catalog/product_view_options_type_date","catalog/product/view/options/type/date.phtml") ;

    if($_product->getTypeId()=="simple"||$_product->getTypeId()=="virtual"||$_product->getTypeId()=="configurable") {
      $blockOption->setProduct($_product);
      if($_product->getOptions()) {
        foreach ($_product->getOptions() as $o) {
          $blockOptionsHtml .= $blockOption->getOptionHtml($o);
        }
      }
    }

    if ($_product->isConfigurable()) {
      $blockViewType = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Type_Configurable");
      $blockViewType->setProduct($_product);
      $blockViewType->setTemplate("catalog/product/view/type/options/configurable.phtml");
      $blockOptionsHtml .= $blockViewType->toHtml();
    }
    if($_product->getTypeId() == "bundle"){
      /**/
      $bundleblockOption = Mage::app()->getLayout()->createBlock("Mage_Bundle_Block_Catalog_Product_View_Type_Bundle");
      $bundleblockOption->addRenderer("checkbox","Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox");
      $bundleblockOption->addRenderer("multi","Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi");
      $bundleblockOption->addRenderer("radio","Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio");
      $bundleblockOption->addRenderer("select","Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Select");
      $bundleblockOption->setProduct($_product);
      if($_product->getOptions()) {
        foreach ($_product->getOptions() as $o) {
          $blockOptionsHtml .= $bundleblockOption->getOptionHtml($o);
        }
      }
    }
    if($_product->getTypeId() == "downloadable"){

    }
    if($_product->getTypeId() == "grouped"){

    }
    return $blockOptionsHtml;
  }

  public function getOptionPriceJs() {
    $js = "var optionsPrice = new Product.OptionsPrice('" . $this->getJsonConfig() . "')";
  }
}