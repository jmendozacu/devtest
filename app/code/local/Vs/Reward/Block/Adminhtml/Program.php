<?php
class Vs_Reward_Block_Adminhtml_Program
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. vs_reward/adminhtml_program
        $this->_blockGroup = 'vs_reward';
        $this->_controller = 'adminhtml_program';
        $this->_headerText = $this->__('Program');
         
        parent::__construct();
    }
}