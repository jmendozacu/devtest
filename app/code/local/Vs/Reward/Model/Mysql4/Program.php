<?php
class Vs_Reward_Model_Mysql4_Program
    extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {  
        $this->_init('vs_reward/program', 'id');
		//$this->_init('vs_reward/program', 'customer_id');
    }  
}