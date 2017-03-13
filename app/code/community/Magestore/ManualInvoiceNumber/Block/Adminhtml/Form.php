<?php
class Magestore_ManualInvoiceNumber_Block_Adminhtml_Form extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Form
{
	public function getSaveUrl()
	{
		return $this->getUrl('manualinvoicenumber/adminhtml_invoice/save', array('order_id' => $this->getInvoice()->getOrderId()));
	}
}
