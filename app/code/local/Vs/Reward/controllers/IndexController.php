<?php
class Vs_Reward_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		
		
		//$_collection = Mage::getResourceModel('reward/program_collection');
		//echo '<pre/>';print_r($collection->getData());exit;
		if($this->getRequest()->getParam('ajax') || $this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax'))
		{
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connectionWrite->beginTransaction();
			$customeremail = Mage::getSingleton('checkout/session')->getQuote()->getCustomerEmail();
			//$customerData->getEmail();
			$result=array();
            if(isset($_POST['mobile_number']) && $_POST['mobile_number']!="")
			{
			
				$collection = Mage::getModel('vs_reward/program')->getCollection()
						->addFieldToFilter('contact_number',$_POST['mobile_number']);
				$reward_collection=$collection->getData();
				
				
					if(isset($reward_collection[0]['id']))
					{
					
						if((isset($reward_collection[0]['customer_id']) && $reward_collection[0]['customer_id'] == 0) && ($customeremail == $reward_collection[0]['online_login'])){
							$result['msg']="You are already requested for Loyalty Card. A new Loyalty Card Account will be created for you and this Transaction will be added to your Loyalty Card Account.";
							$result['instruction']="Add this transaction to above contact number.";
							$result['mobile_number']=$_POST['mobile_number'];
							echo json_encode($result);
						}elseif((isset($reward_collection[0]['customer_id']) && $reward_collection[0]['customer_id'] == 0) && ($customeremail != $reward_collection[0]['online_login'])){
							$result['msg'] = "This contact number has been already associated with loyalty card. Please enter a different number.";
							$result['instruction'] = "Customer tried to create Loyalty Card account for existing contact number which is not associated with Loyalty Card yet.";
							$result['mobile_number']=$_POST['mobile_number'];
							echo json_encode($result);
						}else{
							if(isset($reward_collection[0]['online_login']) && $reward_collection[0]['online_login'] != ""){
								$result['msg'] = "This contact number has been already associated with loyalty card. Please enter a different number.";
								$result['instruction'] = "Customer tried to create loyalty card account for existing contact number.";
								$result['mobile_number']=$_POST['mobile_number'];
								echo json_encode($result);							
							}else{
								$data = array();
								$data['online_login'] = $customeremail;
								$where = $connectionWrite->quoteInto('contact_number =?', $_POST['mobile_number']);
								$connectionWrite->update('vs_reward_program', $data, $where);
								$connectionWrite->commit();
								$result['msg']="You are already registered for Loyalty Card. This Transaction will be added to your Loyalty Card Account.";
								$result['instruction']="Add this transaction to above Loyalty Card ID / Contact Number.";
								$result['mobile_number']=$_POST['mobile_number'];
								echo json_encode($result);
								//echo "Congratulations! Your loyal card has been associated with online account.";
							}
						}
		     				
					}
					else
					{						
						$data = array();
						$data['online_login']= $customeremail;
						$data['contact_number']= $_POST['mobile_number'];
						 $rewardPgm = Mage::getModel('vs_reward/program');
						//$data['bill_amount']='cba';											
						//$connectionWrite->insert('vs_reward_program', $data);
						$connectionWrite->closeConnection();
						 $rewardPgm->rewardProgramInsert($customeremail,$_POST['mobile_number']); 
						$result['msg']="A new Loyalty Card Account will be created for you.";
						$result['instruction']="Create a new Loyalty Card & Add this transaction to above contact number.";
						$result['mobile_number']=$_POST['mobile_number'];
						echo json_encode($result);
						//echo "We have created new loyal account for you. We will provide you a loyal card soon.";
						
					}
				
			}
			elseif(isset($_POST['check_redeem']) && $_POST['check_redeem'] == "on")
			{
				   //echo $customerData->getId(); exit;				   
				   $data = array();
				   $data['redemption_flag'] = 1;
				   $where = $connectionWrite->quoteInto('online_login =?', $customeremail);
				   $connectionWrite->update('vs_reward_program', $data, $where);
				   $connectionWrite->commit();
				   //echo "Congratulations! your redemption will happen on time of delivery.";
				   $result['msg']="Your redemption request is accepted.";
				   $result['mobile_number']=$_POST['mobilenumber'];
				   $result['instruction']="Add this transaction to above loyalty card ID/contact number. Customer would like to redeem points.";				   
				   echo json_encode($result);
					
			}
			elseif(!isset($_POST['check_redeem']))
			{			
				$data = array();
				//$data['online_login'] = $customerData->getId();
				$data['redemption_flag'] = 0;
				$where = $connectionWrite->quoteInto('online_login =?', $customeremail);
				$connectionWrite->update('vs_reward_program', $data, $where);
				$connectionWrite->commit();
			}
			exit;
	       	}
		else
		{
			$this->loadLayout();
			$this->renderLayout();
   		}  
    }
}
