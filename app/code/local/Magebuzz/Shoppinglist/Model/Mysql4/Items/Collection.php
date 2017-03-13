<?php

class Magebuzz_Shoppinglist_Model_Mysql4_Items_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shoppinglist/items');
    }
}