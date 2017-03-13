<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';
class Magestore_ManualInvoiceNumber_Adminhtml_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
	public function saveAction() {
		$data = $this->getRequest()->getPost('invoice');
		try {
			if ($invoice = $this->_initInvoice()) {

					if (!empty($data['capture_case'])) {
							$invoice->setRequestedCaptureCase($data['capture_case']);
					}

					if (!empty($data['comment_text'])) {
							$invoice->addComment($data['comment_text'], isset($data['comment_customer_notify']));
					} else {
						$this->_getSession()->addError($this->__('Enter the Invoice amount'));
						$this->_redirect('adminhtml/sales_order_invoice/new', array('order_id' => $this->getRequest()->getParam('order_id')));
						return;
					}

					$invoice->register();

					if (!empty($data['send_email'])) {
							$invoice->setEmailSent(true);
					}
			
					//Manual Invoice Number
					if ($data['invoice_number'] != '') {
						$invoice->setIncrementId($data['invoice_number']);
					} else {
						$this->_getSession()->addError($this->__('Enter an Invoice number'));
						$this->_redirect('adminhtml/sales_order_invoice/new', array('order_id' => $this->getRequest()->getParam('order_id')));
						return;
					}
					
					$invoice->getOrder()->setIsInProcess(true);

					$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder());
					$shipment = false;
					if (!empty($data['do_shipment']) || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
							$shipment = $this->_prepareShipment($invoice);
							if ($shipment) {
									$shipment->setEmailSent($invoice->getEmailSent());
									$transactionSave->addObject($shipment);
							}
					}
					$transactionSave->save();

					/**
					 * Sending emails
					 */
					$comment = '';
					if (isset($data['comment_customer_notify'])) {
							$comment = $data['comment_text'];
					}
					$invoice->sendEmail(!empty($data['send_email']), $comment);
					if ($shipment) {
							$shipment->sendEmail(!empty($data['send_email']));
					}

					if (!empty($data['do_shipment'])) {
							$this->_getSession()->addSuccess($this->__('Invoice and shipment was successfully created.'));
					}
					else {
							$this->_getSession()->addSuccess($this->__('Invoice was successfully created.'));
					}

					$this->_redirect('adminhtml/sales_order/view', array('order_id' => $invoice->getOrderId()));
					return;
			}
			else {
					$this->_forward('noRoute');
					return;
			}
		}
		catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
		}
		catch (Exception $e) {
				$this->_getSession()->addError($this->__('Can not save invoice'));
		}

		$this->_redirect('adminhtml/sales_order_invoice/new', array('order_id' => $this->getRequest()->getParam('order_id')));
	}
}
