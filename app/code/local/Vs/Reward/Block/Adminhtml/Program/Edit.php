<?php
class Vs_Reward_Block_Adminhtml_Program_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {  
        $this->_blockGroup = 'vs_reward';
        $this->_controller = 'adminhtml_program';
     
        parent::__construct();
     
        $this->_updateButton('save', 'label', $this->__('Save Program'));
        $this->_updateButton('delete', 'label', $this->__('Delete Program'));
    }  
     
    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {  
        if (Mage::registry('vs_reward')->getId()) {
            return $this->__('Edit Program');
        }  
        else {
            return $this->__('New Program');
        }  
    }  
}