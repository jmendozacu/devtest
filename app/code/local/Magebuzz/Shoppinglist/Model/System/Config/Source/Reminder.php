<?php
class Magebuzz_Shoppinglist_Model_System_Config_Source_Reminder extends Varien_Object {
	public function toOptionArray() {	
		return array(
			array('value' => 'daily', 'label' => 'Daily'),
			array('value' => 'weekly', 'label' => 'Weekly'),
			array('value' => 'biweekly', 'label' => 'Biweekly'),
			array('value' => 'monthly', 'label' => 'Monthly')
		);
	}
}