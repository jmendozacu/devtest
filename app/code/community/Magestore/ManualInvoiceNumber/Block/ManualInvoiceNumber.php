<?php
class Magestore_ManualInvoiceNumber_Block_ManualInvoiceNumber extends Mage_Core_Block_Template
{
	public function _prepareLayout()
  {
		return parent::_prepareLayout();
  }
    
	public function getManualInvoiceNumber() { 
			if (!$this->hasData('manualinvoicenumber')) {
					$this->setData('manualinvoicenumber', Mage::registry('manualinvoicenumber'));
			}
			return $this->getData('manualinvoicenumber');
			
	}
}