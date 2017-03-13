<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/EditController.php';

   
    
class MW_Ddate_EditorderController extends Mage_Adminhtml_Sales_Order_EditController
{

	/**
     * Index page
     */
    public function indexAction()
    {   echo '11111111111';die;
        $this->_title($this->__('Sales'))->_title($this->__('Orders'))->_title($this->__('Edit Order'));
        $this->loadLayout();

        $this->_initSession()
            ->_setActiveMenu('sales/order')
            ->renderLayout();
    }


	 /**
     * Saving quote and create order
     */
    public function saveAction()
    {   		    
  echo '11111111111';die;
        try {
            $this->_processActionData('save');
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();
			/* Mage-world code*/
			$order_data=$this->getRequest()->getPost('order');
			$dtime = (isset($order_data['dtime'])) ? $order_data['dtime'] : '';
            $ddate = (isset($order_data['ddate'])) ? $order_data['ddate'] : '';
            $ddate_comment = (isset($order_data['ddate_comment'])) ? $order_data['ddate_comment'] : '';
            $ddates = Mage::getModel('ddate/ddate')->getCollection()
                    ->addFieldToFilter('ddate', array('like' => $ddate . '%'))
                    ->addFieldToFilter('dtime', $dtime);
            if ($ddates->count() > 0):
                foreach ($ddates as $ddate1) {
                    $ddate1->setOrdered($ddate1->getOrdered() + 1);
                    $ddate1->setIncrementId($order->getIncrementId());
                    $ddate1->setDdateComment($ddate_comment);
                    //Mage::log($ddate1->getData());
                    $ddate1->save();
                    break;
                }
            else:
                $_ddate = Mage::getModel('ddate/ddate');
                $_ddate->setDdate($ddate);
                $_ddate->setDtime($dtime);
                $_ddate->setOrdered(1);
                $_ddate->setIncrementId($order->getIncrementId());
                $_ddate->setDdateComment($ddate_comment);
                $_ddate->save();
            endif;
            /* $order->setDdate($ddate);
            $order->setDdateComment($ddate_comment);
            $order->setDtime(Mage::getModel('ddate/dtime')->load($dtime)->getDtime()); */ 
			
			
			/* //Mage-world code*/
				
				
				
				
				
				
            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
	
	
	
}
