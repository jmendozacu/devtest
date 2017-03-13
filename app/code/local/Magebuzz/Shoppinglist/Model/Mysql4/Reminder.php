<?php
class Magebuzz_Shoppinglist_Model_Mysql4_Reminder extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		$this->_init('shoppinglist/reminder', 'id');
	}
	
	public function loadByCustomerId($item) {
		$select = $this->_getReadAdapter()->select()
			->from($this->getMainTable(), 'id')
			->where('customer_id = ?',  $item->getCustomerId())
			->limit(1);
		$id = $this->_getReadAdapter()->fetchOne($select);          
		return $id;
	}
}