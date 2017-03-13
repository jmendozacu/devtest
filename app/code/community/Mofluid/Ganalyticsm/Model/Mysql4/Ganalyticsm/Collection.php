<?php

class Mofluid_Ganalyticsm_Model_Mysql4_Ganalyticsm_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_ganalyticsm/ganalyticsm');
    }
}
