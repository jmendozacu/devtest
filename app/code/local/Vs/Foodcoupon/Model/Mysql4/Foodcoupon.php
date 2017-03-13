<?php
class Vs_Foodcoupon_Model_Mysql4_Foodcoupon extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("foodcoupon/foodcoupon", "id");
    }
}