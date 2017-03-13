<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Model_Items extends Mage_Core_Model_Abstract {
  private $_optionsHtml = null;
  private $_additionalPrice = null;

  public function _construct() {
    parent::_construct();
    $this->_init('shoppinglist/items');
  }

  public function getItemsByGroup($groupId) {
    $items = $this->getCollection();
    $items->addFieldToFilter('list_id', $groupId);
    return $items;
  }

  protected function _parseOptions($item) {
    $optionsHtml = '<dl class="item-options">';
    $additionalPrice = 0;
    $buyRequest = $item->getBuyRequest();
    if ($buyRequest)
      $buyRequest = unserialize($buyRequest);
    $typeInstance = $item->getProduct()->getTypeInstance(true);
    switch ($item->getProduct()->getTypeId()) {							
      case 'configurable':					
        if ($buyRequest AND !empty($buyRequest['super_attribute'])) {
          $attributes = $typeInstance
          ->getConfigurableAttributes($item->getProduct());
          foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getId();						
            if ($attributeId AND !empty($buyRequest['super_attribute'][$attributeId])) {
              $aPrices = $attribute->getPrices();
              if ($aPrices) {
                foreach ($aPrices as $aOptionData) {
                  if ($buyRequest['super_attribute'][$attributeId] == $aOptionData['value_index']) {
                    $optionsHtml .= '<dt>' . $attribute->getProductAttribute()->getFrontendLabel() . '</dt><dd>' . $aOptionData['store_label'] . '</dd>';
                    $additionalPrice += $aOptionData['pricing_value'];
                  }
                }
              }
            }
          }            
        }                

      case 'simple':
      case 'virtual':
        if (!empty($buyRequest['options'])) {          
          if (!$item->getProduct()->getOptions()){
            $collection = $item->getProduct()->getProductOptionsCollection();
            foreach ($collection as $o){
              $o->setProduct($item->getProduct());    
              $item->getProduct()->addOption($o);    
            }                        
          }

          foreach ($buyRequest['options'] as $iOptionId => $mValue) {
            $option = null;
            if ($mValue){
              $option = $item->getProduct()->getOptionById($iOptionId);
            }
            if ($option) {
              $group = $option->groupFactory($option->getType())
              ->setOption($option);

              if (in_array($option->getType(), array('date', 'date_time', 'time'))) {
                $group->setUserValue($mValue);                                
                $group->validateUserValue($mValue);
                $group->setIsValid(true);                                
                $group->setRequest(new Varien_Object($buyRequest));                                
                $mValue = $group->prepareForCart();
              }
              if ($option->getType() == 'file') {
                $aOrigValue = $mValue;
              }
              // add to price
              foreach ($option->getValues() as $_value) {
                if ($mValue) {
                  if (is_array($mValue) AND in_array($_value->getId(), $mValue)) {
                    $additionalPrice += $_value->getPrice(true);
                  }
                  elseif (!is_array($mValue) AND $mValue == $_value->getOptionTypeId()){
                    $additionalPrice += $_value->getPrice(true);
                  }
                }
              }

              if (is_array($mValue)) {
                $mValue = implode(',', $mValue);
              }
              $aOptionData = array(
              'label' => $option->getTitle(),            
              'print_value' => $group->getPrintableOptionValue($mValue),
              );

              if (in_array($option->getType(), array('date', 'date_time', 'time'))) {
                if ($mValue) {
                  if ($option->getPrice()) {
                    $additionalPrice += $option->getPrice(true);
                  }
                }
                else {
                  $aOptionData['print_value'] = '';
                }
              }
              else {
                if ($option->getPrice()) {                  
                  $additionalPrice += $option->getPrice(true);
                }
              }

              if ($option->getType() == 'file') {
                if ($aOrigValue['width'] > 0 && $aOrigValue['height'] > 0) {
                  $sizes = $aOrigValue['width'] . ' x ' . $aOrigValue['height'] . ' ' . Mage::helper('catalog')->__('px.');
                } else {
                  $sizes = '';
                }

                $aOptionData['print_value'] =  sprintf('%s %s',
                Mage::helper('core')->htmlEscape($aOrigValue['title']),
                $sizes
                );
              }

              if ($aOptionData['print_value']) {
                $optionsHtml .= '<dt>' . $aOptionData['label'] . '</dt><dd>' . $aOptionData['print_value'] . '</dd>';
              }
            }
          }
        }
        break;    
    }

    $optionsHtml .= '</dl>';
    $this->_additionalPrice = $additionalPrice;
    $this->_optionsHtml     = $optionsHtml;
  }

  public function getPrice() {
    if (is_null($this->_additionalPrice)){
      try{
        $this->_parseOptions($this);
      } catch (Exception $e){
      }
    }
    $price = $this->getProduct()->getFinalPrice();    
    if ($this->_additionalPrice)
      $price += $this->_additionalPrice;
    return sprintf("%.2f", $price);         
  }

  public function getOptionsHtml() {
    if (is_null($this->_optionsHtml)){
      try {
        $this->_parseOptions($this);
      } 
      catch (Exception $e){
      }
    }				
    return $this->_optionsHtml;         
  }
}