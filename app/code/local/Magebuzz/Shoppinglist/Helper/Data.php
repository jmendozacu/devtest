<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Helper_Data extends Mage_Core_Helper_Abstract {
  const SEND_EMAIL_REMINDER_AFTER = 'shoppinglist/email_setting/email_notification';
  const XML_PATH_EMAIL_RECIPIENT  = 'shoppinglist/email_setting/mailing_address';
  const XML_PATH_EMAIL_SENDER     = 'shoppinglist/email_setting/email_sender';
  const XML_PATH_EMAIL_TEMPLATE   = 'shoppinglist/email_setting/email_template';
  const XML_PATH_SAVE_LATER_PREFIX_NAME   = 'shoppinglist/display/save_later_prefix_name';

  public function isActive(){
    return (bool)Mage::getStoreConfig('shoppinglist/general/active');
  }

  public function isShowSaveForLater(){
    return (bool)Mage::getStoreConfig('shoppinglist/display/show_save_for_later');
  }

  public function allowReminder() {
    return (bool)Mage::getStoreConfig('shoppinglist/email_setting/send_reminder');
  }

  public function getReminderOptions() {
    $reminder = Mage::getStoreConfig('shoppinglist/email_setting/email_notification');
    $reminder_options = array();
    $options = explode(',', $reminder);
    $labels = $this->_getReminderLabel();
    foreach ($labels as $key => $value) {
      if (in_array($key, $options)) {
        $reminder_options[] = array(
        'value' => $key, 
        'label' => $value
        );
      }
    }
    return $reminder_options;
  }

  public function getReminderDay() {
    return Mage::getStoreConfig('shoppinglist/email_setting/reminder_day');
  }

  protected function _getReminderLabel() {
    return array(
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'biweekly' => 'Biweekly',
    'monthly' => 'Monthly',
    );
  }

  public function saveLaterPrefixName(){
    return (bool)Mage::getStoreConfig(self::XML_PATH_SAVE_LATER_PREFIX_NAME);
  }

  public function assignProductToList($groupId, $productId, $qty, $customOptions) {
    Mage::getResourceModel('shoppinglist/items')->insertItemShoppingList($groupId, $productId, $qty, $customOptions);
  }

  public function getItemsCount() {
    $model = Mage::getModel('shoppinglist/items');
  }

  public function getFormatedDate($date)
  {
    return Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
  }

  public function getConfigEmailReminder() {
    if(Mage::getStoreConfig(self::SEND_EMAIL_REMINDER_AFTER)) {
      $optionsArray = split(',',trim(Mage::getStoreConfig(self::SEND_EMAIL_REMINDER_AFTER)));
    }else{
      $optionsArray = '';
    }
    return $optionsArray;
  }
  public function getSelectEmailReminderHtml() {
    $options = array();
    $optionConfig = $this->getConfigEmailReminder();
    foreach ($optionConfig as $key => $value) {
      $options[] = array(
      'value' => $value,
      'label' => $value
      );
    }
    array_unshift($options, array('label' => '-- Please select --', 'value' => ''));
    $select = Mage::app()->getLayout()->createBlock('core/html_select')
    ->setName('email-reminder')
    ->setId('email-reminder')
    ->setTitle('Send Email Reminder After')
    ->setValue(null)
    ->setExtraParams(null)
    ->setOptions($options);
    return $select->getHtml();
  }
  public function getOptionSelect(Mage_Catalog_Model_Product $_product)
  {
    $blockOption = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Options");
    $blockOption->addOptionRenderer("default","catalog/product_view_options_type_default","catalog/product/view/options/type/default.phtml");
    $blockOption->addOptionRenderer("text","catalog/product_view_options_type_text","catalog/product/view/options/type/text.phtml");
    $blockOption->addOptionRenderer("file","catalog/product_view_options_type_file","catalog/product/view/options/type/file.phtml");
    $blockOption->addOptionRenderer("select","checkout/product_view_options_type_select","catalog/product/view/options/type/select.phtml");
    $blockOption->addOptionRenderer("date","catalog/product_view_options_type_date","catalog/product/view/options/type/date.phtml") ;

    $blockOptionsHtml = null;

    if($_product->getTypeId()=="simple"||$_product->getTypeId()=="virtual"||$_product->getTypeId()=="configurable")
    {
      $blockOption->setProduct($_product);
      if($_product->getOptions())
      {
        foreach ($_product->getOptions() as $o)
        {
          $blockOptionsHtml .= $blockOption->getOptionHtml($o);
        };
      }
    }
    if($_product->isConfigurable())
    {

      $blockViewType = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Type_Configurable");
      $blockViewType->setProduct($_product);
      $blockViewType->setTemplate("catalog/product/view/type/options/configurable.phtml");
      $blockOptionsHtml .= $blockViewType->toHtml();
    }
    return $blockOptionsHtml;
  } 
  /* Time Gap */
  public function getTimeGap($firstTime, $lastTime) {

    $firstTime = strtotime($firstTime);
    $lastTime  = strtotime($lastTime);
    $timeGap = $lastTime - $firstTime;
    $hours = round($timeGap / 60); 
    return $hours;
  }

  public function sendEmailReminder($customerId) {
    $translate = Mage::getSingleton('core/translate');
    $translate->setTranslateInline(false);
    $storeId = Mage::app()->getStore()->getId();
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
    $customerEmail = $customer->getEmail();		

    try {
      $mailTemplate = Mage::getModel('core/email_template');
      $mailTemplate->setDesignConfig(array('area' => 'frontend'))
      ->setReplyTo(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT))
      ->sendTransactional(
      Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
      Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
      $customerEmail,
      null,
      array('customer' => $customer)
      );

      if (!$mailTemplate->getSentSuccess()) {
        throw new Exception();
      }      
    }
    catch(Exception $e) {
      Mage::log($e->getMessage(), null, 'shoppinglist.log');
    }
    $translate->setTranslateInline(true);
  }	

  public function getProductOptionsHtml(Mage_Catalog_Model_Product $product) {
    $blockOption = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Options");
    $blockOption->addOptionRenderer("default","catalog/product_view_options_type_default","catalog/product/view/options/type/default.phtml");
    $blockOption->addOptionRenderer("text","catalog/product_view_options_type_text","catalog/product/view/options/type/text.phtml");
    $blockOption->addOptionRenderer("file","catalog/product_view_options_type_file","catalog/product/view/options/type/file.phtml");
    $blockOption->addOptionRenderer("select","shoppinglist/product_view_options_type_select","catalog/product/view/options/type/select.phtml");
    $blockOption->addOptionRenderer("date","catalog/product_view_options_type_date","catalog/product/view/options/type/date.phtml") ;
    $blockOptionsHtml = null;

    if($product->getTypeId()=="simple"||$product->getTypeId()=="virtual"||$product->getTypeId()=="configurable") {
      $blockOption->setProduct($product);
      if($product->getOptions()) {
        foreach ($product->getOptions() as $o) {
          $blockOptionsHtml .= $blockOption->getOptionHtml($o);
        }
      }
    }
    if($product->getTypeId()=="configurable") {
      $blockViewType = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Type_Configurable");
      $blockViewType->setProduct($product);
      $blockViewType->setTemplate("catalog/product/view/type/options/configurable.phtml");
      $blockOptionsHtml .= $blockViewType->toHtml();
    }
    if($product->getTypeId()=="grouped") {
      $blockViewType = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Type_Grouped");
      $blockViewType->setProduct($product);
      $blockViewType->setTemplate("shoppinglist/catalog/product/view/type/grouped.phtml");
      $blockOptionsHtml .= $blockViewType->toHtml();
    }
    if($product->getTypeId()=="bundle") {
    }
    return $blockOptionsHtml;
  }

  public function saveCartForLater($groupId, $items) {
    foreach ($items as $item) {			
      $option = new Varien_Object();
      $option->setValue($item->getOptionByCode('info_buyRequest')->getValue())
      ->setProductId($item->getProductId())     
      ->setCode('info_buyRequest');
      $request = new Varien_Object();  
      $request->setCustomOptions(array($option));					
      Mage::getResourceModel('shoppinglist/items')->insertItemShoppingList($groupId, $item->getProductId(), $item->getQty(), array($request));
    }
    return;
  }
}