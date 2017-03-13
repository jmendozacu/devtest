<?php
class Vs_Reward_Model_Observerr extends Varien_Event_Observer
{
    public function addReward($observer)
    {
       
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        echo "<pre>";print_r($params);die();
    }
}
