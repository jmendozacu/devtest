<?php
class Vs_Stockupdate_Model_Mysql4_Stockupdate extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("stockupdate/stockupdate", "id");
    }
}