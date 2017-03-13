<?php
class Magebuzz_Shoppinglist_Model_Mysql4_Items extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		$this->_init('shoppinglist/items', 'item_id');
	}
	
	/*
	* Insert item to shopping list
	*/
	public function insertItemShoppingList($groupId, $productId, $qty, $customOptions) {
		$model = Mage::getModel('shoppinglist/items');
		/* Update time updated to the group has item*/
		$group = Mage::getModel('shoppinglist/group')->load($groupId);
		$storeId = Mage::app()->getStore()->getId();
		$product = Mage::getModel('catalog/product')->load($productId);
		$data = array(
			'list_id' => $groupId,
			'product_id' => $productId,
			'store_id' => $storeId,
			'qty' => $qty,
			'updated_at' => now()
		);
		$model->setData($data);
		
		if ($customOptions) {
			foreach ($customOptions as $_product) {
				$options = $_product->getCustomOptions();
				foreach ($options as $option) {
					if ($option->getProductId() == $productId && $option->getCode() == 'info_buyRequest'){
						$v = unserialize($option->getValue());
						$qty = isset($v['qty']) ? max(0.01, $v['qty']) : 1;
						$model->setQty($qty);
						$model->setBuyRequest(serialize(array('super_attribute'=>$v['super_attribute'],'product_id'=>$v['product_id'])));
					}
				}
			}
		} 
		
		$group->setUpdatedAt(now());
		try {
			$group->save();
			if ($id = $this->isExisted($model)) {
        $model->setId($id[item_id]);
				$model->setQty($id[qty]+$model->getQty());
        $model->save();      
			}
			else {
        $model->setCreatedAt(now());
				$model->save();			
			}
		} catch(Exception $e) {
			 Mage::throwException($e->getMessage()); 	
		}	
	}
	  
	public function updateShoppingListItem($groupId, $itemId, $updateInfo) {
		try{
			if($updateInfo['select-group'] != '') {
				$updateData = array(
					  'list_id' => $updateInfo['select-group'],
					  'qty' => $updateInfo['qty'],
					  'updated_at' => now(),
				);
			}
			else{
				$updateData = array('qty' => $updateInfo['qty'],'updated_at' => now(),);
			}
			$this->_getWriteAdapter()->update('shoppinglist_item',$updateData,"item_id = ".$itemId);
			/* Update time updated to the group has item*/
			$group = Mage::getModel('shoppinglist/group')->load($groupId);
			$group->setUpdatedAt(now());
			$group->save();
		}catch(Exception $e){
		}
	}
	
	/**
	* check if an item is already in the shopping list
	* return item id on true  
	**/
	public function isExisted($item) {
		$select = $this->_getReadAdapter()->select()
			->from($this->getMainTable(),array('item_id','qty'))
			->where('list_id = ?',  $item->getListId())
			->where('product_id = ?',  $item->getProductId())
			->where('buy_request = ?',  $item->getBuyRequest())
			->limit(1);
		$id = $this->_getReadAdapter()->fetchRow($select);       
		return $id;
	}
	
}