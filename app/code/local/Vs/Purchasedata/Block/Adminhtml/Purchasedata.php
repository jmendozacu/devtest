<?php  

class Vs_Purchasedata_Block_Adminhtml_Purchasedata extends Mage_Adminhtml_Block_Template {

		public function getImportUrl($action) {
			return $this->getUrl('*/*/'.$action);
	}

}