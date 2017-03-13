<?php

class MW_Ddate_Model_Mysql4_Ddate extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        // Note that the news_id refers to the key field in your database table.
        $this->_init('ddate/ddate', 'ddate_id');
    }

    /*
     * save Ddate with order after save DDate
     * */

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        //var_dump($object);
        //$condition = $this->_getWriteAdapter()->quoteInto('ddate_id = ?', $object->getId());
        //$this->_getWriteAdapter()->delete($this->getTable('ddate_store'), $condition);

        if ($object->getData('increment_id')) {
            $storeArray = array();
            $storeArray['ddate_store_id'] = "";
            $storeArray['ddate_id'] = $object->getId();
            $storeArray['increment_id'] = $object->getData('increment_id');
            $storeArray['ddate_comment'] = $object->getData('ddate_comment');            
            $this->_getWriteAdapter()->insert($this->getTable('ddate_store'), $storeArray);
        }
		
        return parent::_afterSave($object);
    }
	 /**
     * Perform actions before object save
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
		$dtimeid=$object->getDtime();
		$dtime = Mage::getModel('ddate/dtime')->load($dtimeid);
		if($dtime->getDtime()) $object->setDtimetext($dtime->getDtime());
        return parent::_beforeSave($object);
    }
    /*
     * get Delivery Date of Order
     * return : array()
     * */

    public function getDdateByOrder($order_id) {
        if ($order_id) {
            $ddate = Mage::getModel('ddate/ddate')->getCollection();
            $ddate->getSelect()
                    ->join($this->getTable('ddate_store'), $this->getTable('ddate_store') . '.ddate_id = main_table.ddate_id AND ' . $this->getTable('ddate_store') . '.increment_id=' . '"'.$order_id.'"', array($this->getTable('ddate_store') . '.ddate_comment')
            );
            if (count($ddate->getData()) > 0) {
                $ddate_store = array();
                foreach ($ddate as $date) {
                    $ddate_store['ddate_id'] = $date->getDdateId();
                    $ddate_store['ddate'] = $date->getDdate();
                    if(Mage::getModel('ddate/dtime')->load($date->getDtime()))
                    $ddate_store['dtime'] = Mage::getModel('ddate/dtime')->load($date->getDtime())->getDtime();else $ddate_store['dtime']=''; 
					$ddate_store['dtimetext']=$date->getDtimetext();
                    $ddate_store['ddate_comment'] = $date->getDdateComment();
                    return $ddate_store;
                }
            }
            return false;
        }
        return false;
    }

    /*
     * @get Delivery Slot Time of the Store View
     * */

    public function getDtime() {
        if (!Mage::registry('mw_ddate_dtime')) {
            $dtimes = Mage::getModel('ddate/dtime')->getCollection();
            $dtimes->getSelect()
                    ->join(array('dtime_store' => $this->getTable('dtime_store')), 'dtime_store.dtime_id = main_table.dtime_id ', array('main_table.dtime_id')
                    )
                    ->where('dtime_store.store_id in (?)', array('0', Mage::app()->getStore()->getId()))
                    ->where('main_table.status = 1')
					->order('main_table.dtimesort ASC');//sorting the time slot
            
            foreach ($dtimes as $dtime) {
                
                //process data about time interval
                $interval = $dtime->getInterval();
                preg_match("/-(\d+):/", $interval, $hours, PREG_OFFSET_CAPTURE);
                preg_match("/:(\d+)$/", $interval, $minutes, PREG_OFFSET_CAPTURE);

                $additionMin = "";
                if (isset($minutes[0])) {
                    $additionMin = " " . $minutes[1][0] . " minutes";
                }
                $dtime->setHighBoundHour($hours[1][0]);
                $dtime->setAdditionMin($additionMin);
                
                //process data about specified dtime's special day
                $specialDay = $dtime->getSpecialDay();
                if(!empty($specialDay)){
                    $specialDays = explode(';', $specialDay);
                    if(is_array($specialDays)){
                        foreach($specialDays as $specialD){
                            $specialD = Mage::helper('ddate')->validateDate($specialD);
                            $specialDays[$specialD] = 1;
                            
                        }
                    }else{
                        $specialD = Mage::helper('ddate')->validateDate($specialDay);
                        $specialDays[$specialD] = 1;
                    }
                }else{
                    $specialDays = array();
                }
                $dtime->setSpecialDays($specialDays);
                
                $dtimeArray[$dtime->getId()] = $dtime;
            }
            Mage::register('mw_ddate_dtime', $dtimeArray);
        }
        return Mage::registry('mw_ddate_dtime');
    }
    

    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {

        // Cleanup stats on blog delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete testimonial/store
        $adapter->delete($this->getTable('ddate/ddate_store'), 'ddate_id=' . $object->getId());
    }

}