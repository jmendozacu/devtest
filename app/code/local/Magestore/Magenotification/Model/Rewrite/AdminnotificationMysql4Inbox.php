<?php

class Magestore_Magenotification_Model_Rewrite_AdminnotificationMysql4Inbox extends Mage_AdminNotification_Model_Mysql4_Inbox
{
    public function loadLatestNotice(Mage_AdminNotification_Model_Inbox $object)
    {
		$select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' desc')
            ->where('is_read <> 1')
            ->where('is_remove <> 1')
            ->limit(1);

		if(! Mage::getStoreConfig('magenotification/general/enabled'))
		{
			$ids = array(0);
			
			$collection = Mage::getModel('magenotification/magenotification')
								->getCollection();
								
			if(count($collection))
			foreach($collection as $item)
			{
				$ids[] = $item->getNotificationId();
			}
			
			$ids = implode(',',$ids);
			
			$select = $this->_getReadAdapter()->select()
				->from($this->getMainTable())
				->order($this->getIdFieldName() . ' desc')
				->where('is_read <> 1')
				->where('is_remove <> 1')
				->where('notification_id NOT IN ('. $ids .')')
				->limit(1);					
		}
		
        $data = $this->_getReadAdapter()->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
	
    public function getNoticeStatus(Mage_AdminNotification_Model_Inbox $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(
                'severity'     => 'severity',
                'count_notice' => 'COUNT(' . $this->getIdFieldName() . ')'))
            ->group('severity')
            ->where('is_remove=?', 0)
            ->where('is_read=?', 0);
			
		if(! Mage::getStoreConfig('magenotification/general/enabled'))
		{
			$ids = array(0);
			
			$collection = Mage::getModel('magenotification/magenotification')
								->getCollection();
								
			if(count($collection))
			foreach($collection as $item)
			{
				$ids[] = $item->getNotificationId();
			}
			
			$ids = implode(',',$ids);
			
			$select = $this->_getReadAdapter()->select()
				->from($this->getMainTable(), array(
					'severity'     => 'severity',
					'count_notice' => 'COUNT(' . $this->getIdFieldName() . ')'))
				->group('severity')
				->where('is_remove=?', 0)
				->where('is_read=?', 0)
				->where('notification_id NOT IN ('. $ids .')')
			;									
		}			
			
        $return = array();
        $rowSet = $this->_getReadAdapter()->fetchAll($select);
        foreach ($rowSet as $row) {
            $return[$row['severity']] = $row['count_notice'];
        }
        return $return;
    }	
}