<?php
class Vs_Reward_Model_Program
    extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {  
        $this->_init('vs_reward/program');
    }  
    public function rewardProgramInsert($customerEmail,$loyaltyInfo) {
    	$data = array();
    	
    	$data['online_login'] = $customerEmail;
        $data['contact_number'] = $loyaltyInfo;
        $connectionWrite =   $this->getConnection();                                    
        $connectionWrite->insert('vs_reward_program', $data);
        $connectionWrite->commit();
        
    }
    public function rewardProgramInstructionInsert($data_instruction) {
    	$connectionWrite =  $this->getConnection();
    	$connectionWrite->insert('vs_reward_instruction', $data_instruction);
        $connectionWrite->commit();

    }
	public function checkRegistered($email) {
		$connection =  $this->getConnectionRead();
	    $selectloyaltycard = $connection->select()
	    ->from('vs_reward_program', array('*')) 
	    ->where('online_login=?',$email) ;
	    $result = $connection->fetchRow($selectloyaltycard); 
	    return $result;
	}
    public function getConnection() {
    	$connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connectionWrite->beginTransaction();
        return $connectionWrite;

    }

    public function getConnectionRead() {
    	$connection = Mage::getSingleton('core/resource')
                    ->getConnection('core_read');
        return $connection;
    }


}