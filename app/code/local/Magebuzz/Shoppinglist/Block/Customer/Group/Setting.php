<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer_Group_Setting extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
	public function getSettingEmailReminderUrl() {
		return Mage::getUrl('shoppinglist/group/settingEmail');
	}
}