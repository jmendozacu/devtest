<?php

class Mofluid_Ganalyticsm_Model_Mysql4_Ganalyticsm extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('mofluid_ganalyticsm/ganalyticsm', 'mofluid_ga_id');
    }
}
