<?php

class MW_Ddate_Model_Dtime extends Mage_Core_Model_Abstract
{
    private $startTimeOfFirstSlot = null;
    public function _construct()
    {
        parent::_construct();
        $this->_init('ddate/dtime');
    }
    
    public function getStartTimeOfFirstSlot(){
        if(is_null($this->startTimeOfFirstSlot)){
            $startTime = 24;
            $dtimes = $this->getCollection()->addFieldToFilter('status', array('eq' => 1));
            foreach($dtimes as $dtime){
                preg_match_all('/(\d+):/', $dtime->getInterval(), $matchesarray, PREG_SET_ORDER);
                if(isset($matchesarray[0][1]) && $matchesarray[0][1] < $startTime){
                    $startTime = $matchesarray[0][1];
                }
            }
            $this->startTimeOfFirstSlot = $startTime;
        }
        return $this->startTimeOfFirstSlot;
    }
}
?>