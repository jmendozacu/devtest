<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

# Controllers are not autoloaded so we will have to do it manually:
require_once 'Mage/Checkout/controllers/OnepageController.php';

class MW_Ddate_Checkout_OnepageController extends Mage_Checkout_OnepageController
{	
	public function ddateAction()
    {        
        $this->loadLayout(false);
        $this->renderLayout();
    }
 	protected $_sectionUpdateFunctions = array(
        'payment-method'  => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review'          => '_getReviewHtml',
 		'ddate'			  => '_getDdateHtml',
    );
	public function savePaymentAction()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('payment', array());
            /*
            * first to check payment information entered is correct or not
            */

            try {
                $result = $this->getOnepage()->savePayment($data);
            }
            catch (Mage_Payment_Exception $e) {
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                $result['error'] = $e->getMessage();
            }
            catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
            	//$this->loadLayout('checkout_onepage_ddate');
            	
                $result['goto_section'] = 'ddate';
		     	/*$result['update_section'] = array(
                        'name' => 'ddate',
                        'html' => $this->_getDdateHtml(),
                    );*/
		 		// $result['review_html'] = $this->getLayout()->getBlock('root')->toHtml();
            }
			//$this->getResponse()->setBody(Zend_Json::encode($result));
			
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
            
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
		
	protected function _getReviewHtml()
    {
        return $this->getLayout()->getBlock('root')->toHtml();
    }
    
    protected function _getDdateHtml(){
    	$layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_ddate');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }
    
    public function saveDdateAction()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
        	$data = $this->getRequest()->getPost('ddate', '');
			$result = $this->getOnepage()->saveDdate($data);			
            if(!$result) {		
				$this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );				
			}
            $this->getResponse()->setBody(Zend_Json::encode($result));
			
        }

    }
		 public function findDtimeAction()
    {
        //$this->_expireAjax();
        if ($this->getRequest()->isPost()) {
			$post = $this->getRequest()->getPost('deliverydate','');
			$day= explode('>',$post);		
			 $temp1=array("sun","mon","tue","wed","thu","fri","sat");
			$slot = Mage::getModel('ddate/dtime')->getCollection()
			->addFieldToFilter($temp1[$day[1]],array('eq'=>1))
			;
			if(count($slot)){
				$html ="";		
				 $html=$html.'<select id="ddate:dtime" size="1" name="ddate[dtime]" >	'
				.'<option value="">Select Time</option>';
				foreach($slot as $sl){
					$html=$html.'<option value="'.$sl->getDtimeId().'">'.$sl->getDtime().'</option>';
				};
				$html=$html.'</select>';					
				echo $html; 
			return;	
			}else echo "There is no delivery time slot available for choosed day";
        }

    }
        /**
     * Create order action
     */
    public function saveOrderAction()
    { 

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }
       

        if (! $this->getOnepage()->getQuote()->validateMinimumAmount()) {
             $result = array();
        
                $result['success'] = false;
                $result['error'] = true;
                $result['minimum'] = true;
                $result['error_messages'] = $this->__('Please check minimum order amount');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;       
           
        }
     
    $data_new = $this->getRequest()->getPost();
        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
       // echo 'here';exit;
        $this->getOnepage()->getQuote()->save();

 $customer_id = 0;//$customer->getId();
       if (Mage::getSingleton('customer/session')->isLoggedIn()) { 
    // Get the customer object from customer session
        $customer = Mage::getSingleton('customer/session')->getCustomer();
         
        
            $custEmail = $customer->getEmail();//get customer email

         $customer_id = $customer->getId();
        $lastOrderId =Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $data_instruction['order_id']= $lastOrderId;
        $data_instruction['customer_id']=$custEmail;

        if(isset($data_new['mobile_num'])) {
            $data_instruction['mobile_no']= $data_new['mobile_num'];
            $data_instruction['reward_instruction']=$data_new['instruction'];
            $rewardPgm = Mage::getModel('vs_reward/program');
            $rewardPgm->rewardProgramInstructionInsert($data_instruction);

            // $connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            // $connectionWrite->beginTransaction();
            // $connectionWrite->insert('vs_reward_instruction', $data_instruction);
            // $connectionWrite->commit();
        }
    }
     $order_id =Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $store_id = Mage::app()->getStore()->getId();
        $foodcouponModel = Mage::getModel('foodcoupon/foodcoupon')->load();
        $amount = $data_new['food_coupen'];
        $foodcouponModel->updateFoodcouponTable($order_id,$store_id,$customer_id,$amount);
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}

?>