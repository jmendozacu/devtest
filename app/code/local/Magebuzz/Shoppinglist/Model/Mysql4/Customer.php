<?php

class Magebuzz_Shoppinglist_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the item_id refers to the key field in your database table.
        $this->_init('shoppinglist/customer', 'id');
    }
}