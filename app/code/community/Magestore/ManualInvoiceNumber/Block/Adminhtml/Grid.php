<?php
class Magestore_ManualInvoiceNumber_Block_Adminhtml_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{
	protected function _prepareColumns()
	{
		$this->addColumn('increment_id', array(
				'header'    => Mage::helper('sales')->__('Invoice #'),
				'index'     => 'increment_id',
				'type'      => 'text',
		));

		$this->addColumn('created_at', array(
				'header'    => Mage::helper('sales')->__('Invoice Date'),
				'index'     => 'created_at',
				'type'      => 'datetime',
		));

		$this->addColumn('order_increment_id', array(
				'header'    => Mage::helper('sales')->__('Order #'),
				'index'     => 'order_increment_id',
				'type'      => 'number',
		));

		$this->addColumn('order_created_at', array(
				'header'    => Mage::helper('sales')->__('Order Date'),
				'index'     => 'order_created_at',
				'type'      => 'datetime',
		));

		$this->addColumn('billing_firstname', array(
				'header' => Mage::helper('sales')->__('Bill to First name'),
				'index' => 'billing_firstname',
		));

		$this->addColumn('billing_lastname', array(
				'header' => Mage::helper('sales')->__('Bill to Last name'),
				'index' => 'billing_lastname',
		));

		$this->addColumn('state', array(
				'header'    => Mage::helper('sales')->__('Status'),
				'index'     => 'state',
				'type'      => 'options',
				'options'   => Mage::getModel('sales/order_invoice')->getStates(),
		));

		$this->addColumn('grand_total', array(
				'header'    => Mage::helper('customer')->__('Amount'),
				'index'     => 'grand_total',
				'type'      => 'currency',
				'align'     => 'right',
				'currency'  => 'order_currency_code',
		));

		$this->addColumn('action',
				array(
						'header'    => Mage::helper('sales')->__('Action'),
						'width'     => '50px',
						'type'      => 'action',
						'getter'     => 'getId',
						'actions'   => array(
								array(
										'caption' => Mage::helper('sales')->__('View'),
										'url'     => array('base'=>'*/*/view'),
										'field'   => 'invoice_id'
								)
						),
						'filter'    => false,
						'sortable'  => false,
						'is_system' => true
		));

		//return parent::_prepareColumns();
	}
}