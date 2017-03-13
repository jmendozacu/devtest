<?php
/*
* Copyright (c) 2013 www.magebuzz.com
*/
class Magebuzz_Shoppinglist_Block_Customer_View extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('My Quick List'));
        }
    }
}