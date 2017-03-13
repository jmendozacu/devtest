<?php
class Vs_Reward_Model_Mysql4_Program_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {  
        $this->_init('vs_reward/program');
    }  
}