<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Helper_Group extends Mage_Core_Helper_Abstract
{
  public function saveNewGroup($customerID, $groupName, $status) {
    $helper = Mage::helper('shoppinglist');
    $sendEmailAfter = $helper->getConfigEmailReminder();
    $model = Mage::getModel('shoppinglist/group');
    $model->setCustomerId($customerID);			
    $model->setListName($groupName);			
    $model->setStatus($status);
    $model->setCreatedAt(now());
    $model->setUpdatedAt(now());
    $model->setSendReminderAfter($sendEmailAfter[0]);
    try {
      $model->setId(null)->save();
    }
    catch (Exception $ex) {
    }
    return $model->getId();
  }
  public function getGroupHtmlSelect($customerId, $groupId=null,$itemId=null,$validation=false) {
    $groupOptions = Mage::getModel('shoppinglist/group')->getGroupByCustomer($customerId);
    $options = array();
    $name= '';
    $class='select-group';
    if($validation) {
      $class .= ' validate-select';
    }
    if($groupId!=null) {
      $groupOptions->addFieldToFilter('list_id', array('neq' => $groupId));
    }
    if($itemId!=null) {
      $name .= 'item['.$itemId.'][select-group]';
    }
    else{
      $name = 'select-group';
    }
    foreach ($groupOptions as $_group) {
      $options[] = array(
      'value' => $_group->getListId(),
      'label' => $_group->getListName()
      );
    }
    array_unshift($options, array('label' => 'Select List', 'value' => ''));
    $select = Mage::app()->getLayout()->createBlock('core/html_select')
    ->setName($name)
    ->setId($name)
    ->setTitle('Select Group')
    ->setValue(null)
    ->setClass($class)
    ->setExtraParams(null)
    ->setOptions($options);
    return $select->getHtml();
  }
}