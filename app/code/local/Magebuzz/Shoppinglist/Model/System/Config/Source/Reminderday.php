<?php
class Magebuzz_Shoppinglist_Model_System_Config_Source_Reminderday extends Varien_Object {
	public function toOptionArray() {	
		return array(
			array('value' => 'monday', 'label' => 'Monday'),
			array('value' => 'tuesday', 'label' => 'Tuesday'),
			array('value' => 'wednesday', 'label' => 'Wednesday'),
			array('value' => 'thursday', 'label' => 'Thursday'),
			array('value' => 'friday', 'label' => 'Friday'),
			array('value' => 'saturday', 'label' => 'Saturday'),
			array('value' => 'sunday', 'label' => 'Sunday')
		);
	}
}